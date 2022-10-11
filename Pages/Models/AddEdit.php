<?php
namespace models;
use soffit\{Registry,HtmlHelper};

class AddEdit extends General
{
    public int $id = 0;
    public array $row = [];
	public array $workingCenters = [];

    protected HtmlHelper $html;

    /**
     * @var array - массив списков из service_data
     */
	public array $dataTables = [];

    /**
     * AddEdit constructor.
     * @throws \Exception
     */
    public function __construct( int $id = 0 )
    {
        parent::__construct();
        $this->html = new HtmlHelper();
        if ( $id ) 
        {
            if ( !$this->checkID( $id ) )
                //$this->redirect('/main/');
                throw new \Exception("Wrond ID in " . __METHOD__, 707);

            $this->id = $id;
        }

        $this->connectToDB();
        $this->getUsers();
        $this->workingCenters = $this->workingCentersDB;
    }

    public function handlerFilesScript(bool $full, array &$permittedFields=[]) : string
    {
        $js = "let handlerFiles;";
        if ( $full === false )
            return $js;

        $fileTypes = ["image/jpeg", "image/png", "image/gif"];
        if ( isset($permittedFields['rhino3dm']) ) 
            $fileTypes[] = ".3dm";

        if ( isset($permittedFields['stl']) )
        {
            $fileTypes[] = ".stl";
            $fileTypes[] = ".mgx";
        }
        if ( isset($permittedFields['ai']) ) 
        {
            $fileTypes[] = ".ai";
            $fileTypes[] = ".dxf";
        }
        $json = json_encode($fileTypes,JSON_UNESCAPED_UNICODE);
        $js = <<<JS
            $js
            window.addEventListener('load',function() {
              handlerFiles = new HandlerFiles( document.getElementById('drop-area'),document.getElementById('addImageFiles'),$json);
            },false);
JS;             
        return $js;
    }

    public function parseMaterialsData( array $dataTables ) : array
    {
        $res = [];
        $materials = [];
        foreach ( $dataTables['metal_color']??[] as $metalColor )
            $materials['colors'][] = $metalColor['name'];

        $materials['colors'][] = "Нет";

        foreach ( $dataTables['model_material']??[] as $modelMaterials )
        {
            $namesProbes = explode(';', $modelMaterials['name']);
            $name = $namesProbes[0]??'';
            $probe = $namesProbes[1]??'';

            $materials['names'][$name] = $name;
            if ( !empty( $probe ) )
            {
                $materials['probes'][$name][] = $probe;
            } else {
                $materials['probes'][$name] = [];
            }
        }
        $materials['probes']['none'][] = "Нет";
        $res['materials'] = $materials;

        $coverings = [];
        foreach ( $dataTables['model_covering']??[] as $modelCoverings )
        {
            $namesCovers = explode(';', $modelCoverings['name']);
            $name = $namesCovers[0];
            $area = $namesCovers[1];

            $coverings['names'][$name] = $name;

            if ( !empty( $area ) )
                $coverings['areas'][$area] = $area;
        }
        $res['coverings'] = $coverings;
        $res['handlings'] = $dataTables['handling']??[];

        return $res;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getDataTables()
    {
        if ( !empty($this->dataTables) && is_array($this->dataTables) ) 
            return $this->dataTables;
        
        $tables = $this->getServiceData();
        foreach ( $tables['gems_sizes']??[] as $size )
        {
            if ( is_numeric($size['name']) )
            {
                $tables['gems_sizes']['num'][] = $size['name'];
            } else {
                $tables['gems_sizes']['notnum'][] = $size['name'];
            }
        }
        $tA = $tables['gems_sizes']['num']??[];
        $tables['gems_sizes']['num'] = $tA;
        sort($tables['gems_sizes']['num']);

		return $this->dataTables = $tables;
	}

	public function setPrevPage()
    {
        
        $session = Registry::init()->sessions;
		$thisPage = 'http://'.($this->server['HTTP_HOST']??'').($this->server['REQUEST_URI']??'');
        //debug($this->server,'serv'); // HTTP_REFERER
        //debug($this->server["HTTP_REFERER"],'serv',1); // HTTP_REFERER
        $ref = $this->server["HTTP_REFERER"]??'';
		if ( !empty($ref) && $thisPage !== $ref ) {
			$session->prevPage = $ref;
		}
		return $session->prevPage;
	}

    /**
     * @param $component
     * @return array
     * @throws \Exception
     */
    public function getComplected($component) : array
    {
        $and = "";
        if ( $component === 2 ) $and = "AND st.id<>'{$this->id}'";
        $sql = " SELECT st.id, st.model_type, img.pos_id, img.img_name, img.main, img.sketch
				FROM stock st 
					LEFT JOIN images img ON (st.id = img.pos_id) 
				WHERE st.number_3d='{$this->row['number_3d']}' 
				$and ";                                           // AND img.main=1
        $complected = $this->findAsArray( $sql );
        if ( empty($complected) ) return [];

        $images = [];
        foreach ( $complected as $image )
        {
            $main = 'main';
            $sketch = 'sketch';
            if ( isset($image['main']) )
            {
                $images[$image['pos_id']]['img_names'][$main] = $image['img_name'];
            } elseif ( isset($image['sketch']) )
            {
                $images[$image['pos_id']]['img_names'][$sketch] = $image['img_name'];
            } else {
                $images[$image['pos_id']]['img_names'][] = $image['img_name'];
            }
            $images[$image['pos_id']]['pos_id'] = $image['pos_id'];
            $images[$image['pos_id']]['model_type'] = $image['model_type'];

            if ( isset($image['number_3d']) )
                $images[$image['pos_id']]['number_3d'] = $image['number_3d'];
        }

        $complected = $images;

        foreach ( $complected as &$complect )
        {
            $imgPath = $this->row['number_3d'].'/'.$complect['pos_id'].'/images/';
            $imgName = $complect['img_names'][ array_key_first($complect['img_names']) ]; // первая попавшаяся

            foreach ( $complect['img_names']??[] as $iStat => $iName )
            {
                if ( $iStat == 'main' )
                {
                    $imgName = $iName;
                    break;
                }
                if ( $iStat == 'sketch' )
                    $imgName = $iName;
            }

            // проверка файла
            if ( !file_exists(_stockDIR_ . $imgPath . $imgName) )
            {
                $complect['img_name'] = _stockDIR_HTTP_."default.jpg";
            } else {
                // Файл Есть
                $complect['img_name'] = '';
                if ( $prevImgName = $this->checkSetPreviewImg($imgPath, $imgName) )
                {
                    $complect['img_name'] = _stockDIR_HTTP_.$imgPath.$prevImgName;

                } elseif ( ImageConverter::makePrev( $imgPath, $imgName ) ) {
                    // Превью создана!
                    $complect['img_name'] = _stockDIR_HTTP_ . $imgPath . ImageConverter::getLastImgPrevName();
                } else {
                    // подставим ьольшую картинку если не удалось создать превью
                    $complect['img_name'] = _stockDIR_HTTP_.$imgPath.$imgName;
                }
            }
        }
        return $complected;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getDataLi()
    {
        $dataTables = $this->getDataTables();
		$data_Li = ['collections'=>'', 'author'=>'', 'modeller3d'=>'', 'model_type'=>'', 'jeweler'=>''];
        foreach ( $dataTables as $name => $data )
        {
            $coll = '';
            if ( !array_key_exists($name, $data_Li) ) continue;

            if ( $name == 'collections' )  $coll = 'coll';
            foreach ( $data as $arrayLi )
            {
                $a = $this->html->tag('a')->setAttr(['elemToAdd'=>'',$coll=>'','collId'=>$arrayLi['id']])->setTagText($arrayLi['name'])->create();
                $li = $this->html->tag('li')->setTagText($a)->create();
                $data_Li[$name] .= $li;
            }
        }
		return $data_Li;
	}

    public function getGemsLi()
    {
		$gems_Li = ['gems_sizes'=>'', 'gems_cut'=>'', 'gems_names'=>'', 'gems_color'=>''];
        $dataTables = $this->getDataTables();

        foreach ( $dataTables as $name => $data )
        {
            if ( !array_key_exists($name, $gems_Li) ) continue;

            if ( $name == 'gems_sizes' )
            {
                foreach ( $data['num']??[] as $value ) 
                {
                    $a = $this->html->tag('a')->setAttr(['elemToAdd'=>''])->setTagText($value)->create();
                    $gems_Li[$name] .= $this->html->tag('li')->setTagText($a)->create();
                }
                
                $gems_Li[$name] .= $this->html->tag('li')->setAttr(['role'=>'separator','class'=>'divider'])->create();
                
                foreach ( $data['notnum']??[] as $value ) 
                {
                    $a = $this->html->tag('a')->setAttr(['elemToAdd'=>''])->setTagText($value)->create();
                    $gems_Li[$name] .= $this->html->tag('li')->setTagText($a)->create();
                }

            } else {
                foreach ( $data??[] as $arrayLi )
                {
                    $div = $this->html->tag('div')->setAttr(['class'=>'addElemMore','addElemMore'=>''])->setTagText('+')->create();
                    $a = $this->html->tag('a')->setAttr(['elemToAdd'=>''])->setTagText($arrayLi['name'])->create();
                    $gems_Li[$name] .= $this->html->tag('li')->setAttr(['style'=>'position:relative;'])->setTagText($a.$div)->create();
                }
            }
        }
		return $gems_Li;
	}

	public function getNamesVCLi()
    {
		$vc_namesLI = '';
        foreach ( $this->dataTables as $name => $data )
        {
            if ( $name !== 'vc_names' ) continue;
            foreach ( $data as $arrayLi )
            {
                $a = $this->html->tag('a')->setAttr(['elemToAdd'=>'','VCTelem'=>''])->setTagText($arrayLi['name'])->create();
                $vc_namesLI .= $this->html->tag('li')->setTagText($a)->create();
            }
        }
		return $vc_namesLI;
	}

    /**
     * ПЕРЕПИСАТЬ с Одним запромом
     * @param $vcLinkRows
     * @return array
     * @throws \Exception
     */
	public function getNum3dVCLi( array $vcLinkRows ) : array
    {
		$num3DVC_LI = [];
		$mtIN = "";
		foreach ( $vcLinkRows as $vcLink )
		    $mtIN .= "'" . $vcLink['vc_names'] . "',";

		if ( empty(trim($mtIN, "', ")) ) return $num3DVC_LI;

        $mtIN = "(" . trim($mtIN,",") . ")";

        $sql = " SELECT st.id, st.number_3d, st.vendor_code, st.model_type, i.img_name
                  FROM stock as st
                  LEFT JOIN images as i ON i.pos_id = st.id AND i.main=1
                  WHERE collections LIKE '%Детали%' AND model_type IN $mtIN ";

        $result = $this->findAsArray($sql);

        $num3DVC_LI = range( 0, count($vcLinkRows)-1 );

        foreach ( $vcLinkRows??[] as $key => $vcLink )
        {
            $num3DVC_LI[$key] = '';
            foreach ( $result as $res )
            {

                if ( $vcLink['vc_names'] === $res['model_type'] )
                {
                    $file = $res['number_3d'].'/'.$res['id'].'/images/'.$res['img_name'];
                    $fileImg = _stockDIR_HTTP_.$file;
                    if ( !file_exists(_stockDIR_.$file) )
                        $fileImg = _stockDIR_HTTP_."default.jpg";

                    $nameVC = $res['vendor_code'] ?: $res['number_3d'];
                    $a = $this->html->tag('a')->setAttr(['class'=>'imgPrev','elemToAdd'=>'','imgtoshow'=>$fileImg])
                        ->setTagText($nameVC)->create();

                    $num3DVC_LI[$key] .= $this->html->tag('li')->setTagText($a)->create();
                }
            }
        }
		return $num3DVC_LI;
	}

    /**
     * @return array|bool
     * @throws \Exception
     */
    public function getGeneralData()
    {
    	$this->row = $this->findOne( " SELECT * FROM stock WHERE id='$this->id' ");
    	if ( empty($this->row) ) return [];
    	$this->row['collections'] = explode(';',$this->row['collections']);
        foreach ( $this->statuses??[] as $status )
        {
            if ( $status['id'] === (int)$this->row['status'] )
            {
                $this->row['status'] = $status;
                break;
            }
        }

		return $this->row;
	}

    /**
     * @return array|bool
     * @throws \Exception
     */
    public function getStl() : array
    {
        return $this->findOne( " SELECT * FROM stl_files WHERE pos_id='$this->id' ");
	}
    /**
     * @return array
     * @throws \Exception
     */
    public function get3dm() : array
    {
        return $this->findOne( " SELECT * FROM rhino_files WHERE pos_id='$this->id' ");
    }
    /**
     * @return array|bool
     * @throws \Exception
     */
    public function getAi() : array
    {
        return $this->findOne( " SELECT * FROM ai_files WHERE pos_id='$this->id' ");
	}

    /**
     * @return array
     * @throws \Exception
     */
    public function getModelPrices() : array
    {
        return $this->findAsArray( " SELECT * FROM model_prices WHERE pos_id='$this->id' ");
    }

    /**
     * @param bool $row
     * @param bool $complected
     * Флаг о том что создаём комплект, все ид строк надо удалить
     * @return array|bool
     * @throws \Exception
     */
	public function getMaterials($row=false, $complected=false) : array
	{
		$materials = $this->findAsArray(" SELECT * FROM metal_covering WHERE pos_id='$this->id' ");

		if (!empty($materials))
        {
            if ( $complected )
                foreach ( $materials as &$material )
                    $material['id'] ='';
            return $materials;
        }

		if ( $row ) $this->row = $row;

		 $materials = [
            [
                'part' => '',
                'type' => '',
                'probe' => '',
                'metalColor' => '',
                'covering' => '',
                'area' => '',
                'covColor' => '',
                'handling' => '',
            ],
        ];

        $privParts = stristr($this->row['model_covering'], 'Отдельные части');
        $hasDetail = false; // есть деталировка
        if ( $privParts )
        {
        	$str_mod_priv_arr = explode("-",$this->row['model_covering']);
        	$hasDetail = true;
        	$materials[1]['part'] = $str_mod_priv_arr[1]?:'';
        }

        $str_material_arr = explode(";",$this->row['model_material']);

        foreach ( $str_material_arr as $value )
        {
        	$i = 0;
            switch ( $value )
            {
                case "585":
                case "750":
                    $materials[0]['probe'] = $value;
                    if ( $hasDetail ) $materials[1]['probe'] = $value;
                    break;

                case "Золото":
                    $materials[0]['type'] = $value;
                    if ( $hasDetail ) $materials[1]['type'] = $value;
                    break;
                case "Серебро":
                    $materials[0]['type'] = $value;
                    break;
                case "Красное":
                	$materials[0]['metalColor'] = $value;
                    break;

                case "Белое":
                case "Желтое(евро)":
                	if ( $hasDetail ) $i = 1;
                    $materials[$i]['metalColor'] = $value;
                    break;
            }
        }

        $i = $hasDetail?1:0;
        $str_mod_cov_arr = explode(";",$this->row['model_covering']);
        foreach ( $str_mod_cov_arr as $value )
        {
            switch ( $value )
            {
                case "Родирование":
                case "Золочение":
                case "Чернение":
                    $materials[$i]['covering'] = $value;
                    break;

                case "Полное":
                case "Частичное":
                case "По крапанам":
                    $materials[$i]['area'] = $value;
                    break;
            }
        }
        return $materials;
	}

    /**
     * @param bool $sketch
     * @return array
     * @throws \Exception
     */
    public function getImages($sketch = false)
    {
		$respArr = array();

		if ( $sketch === true ) {
            $foundImages = $this->findAsArray(" SELECT * FROM images WHERE pos_id='$this->id' AND sketch='1' ");
		} else {
            $foundImages = $this->findAsArray(" SELECT * FROM images WHERE pos_id='$this->id' ");
		}
		
		if ( !count($foundImages) ) return $respArr;

        $this->getStatLabArr('image');

        $i = 0;
        foreach ( $foundImages as  $row_img )
        {
            $respArr[$i]['id'] = $row_img['id'];
            $respArr[$i]['imgName'] = $row_img['img_name'];
            if ( $row_img['main'] ) $respArr[$i]['main'] = $row_img['main'];

            $imgPath = $this->row['number_3d'].'/'.$this->id.'/images/';
            $imgName = $row_img['img_name'];

            if ( !file_exists(_stockDIR_.$imgPath.$imgName) )
            {
                $respArr[$i]['imgPath'] = _stockDIR_HTTP_."default.jpg";
            } else {
                // Файл есть!
                $respArr[$i]['imgPath'] = _stockDIR_HTTP_.$imgPath.$imgName;

                // проверим превьюшку
                $respArr[$i]['imgPrevPath'] = '';
                if ( $prevImgName = $this->checkSetPreviewImg($imgPath, $imgName) )
                {
                    $respArr[$i]['imgPrevPath'] = _stockDIR_HTTP_.$imgPath.$prevImgName;
                } elseif ( ImageConverter::makePrev( $imgPath, $imgName ) ) {
                    // Превью создана!
                    $respArr[$i]['imgPrevPath'] = _stockDIR_HTTP_ . $imgPath . ImageConverter::getLastImgPrevName();
                }
            }

            //debug($row_img,'$row_img');

            // проставляем флажки
            $img_arr = $this->imageStatuses;
            //debug($img_arr,'$img_arr',1);
            foreach ( $row_img as $key => $value )
            {
                // нижний ходит по статусам из табл и сверяет имена с ключом из картинок
                $flagToResetNo = false;
                foreach ( $img_arr as &$option )
                {
                    if ( $key === $option['name_en'] && (int)$value === 1 )
                    {
                        $option['selected'] = $value;
                        $flagToResetNo = true;
                    }
                    // уберем флажек с "НЕТ" если был выставлен на чем-то другом
                    if (  (int)$option['id'] === 27 && $flagToResetNo === true ) $option['selected'] = 0;
                }
            }
            $respArr[$i]['imgStat'] = $img_arr;
            $i++;
        }

		return $respArr;
	}

    /**
     * @param bool $complected
     * Флаг о том что создаём комплект, все ид строк надо удалить
     * @return array
     * @throws \Exception
     */
    public function getGems($complected = false)
	{
		$gems = $this->findAsArray( " SELECT * FROM gems WHERE pos_id='$this->id' ");

        if (!empty($gems))
        {
            if ( $complected )
                foreach ( $gems as &$gem )
                    $gem['id'] ='';
        }

        return $gems;
	}

    /**
     * @param bool $complected
     * @return array
     * @throws \Exception
     */
    public function getDopVC($complected = false)
	{
        $vc_links = $this->findAsArray( " SELECT * FROM vc_links WHERE pos_id='$this->id' ");

        if (!empty($vc_links))
        {
            if ( $complected )
                foreach ( $vc_links as &$vLink )
                    $vLink['id'] ='';
        }

        return $vc_links;
	}

    /**
     * @return array
     * @throws \Exception
     */
    public function getDescriptions()
    {
        $sql = "SELECT d.id, d.num, d.text, DATE_FORMAT(d.date, '%d.%m.%Y') as date, d.pos_id, u.fio as userName
                FROM description as d
                  LEFT JOIN users as u
                    ON (d.userID = u.id ) 
                WHERE d.pos_id = $this->id";
        return $this->findAsArray( $sql );
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getRepairs()
    {
        $repairs = $this->findAsArray( " SELECT * FROM repairs WHERE pos_id='$this->id' ");
        $sql = " SELECT * FROM model_prices as mp
                  WHERE mp.repair_id IN 
                  ( SELECT r.id FROM repairs as r WHERE r.pos_id='$this->id' ) ";
        $prices = $this->findAsArray( $sql );

        foreach ( $repairs as &$repair )
        {
            foreach ( $prices as $price )
            {
                if ( $repair['id'] == $price['repair_id'] )
                {
                    $repair['prices'][] = $price;
                    if ( (int)$price['paid'] === 1 )
                        $repair['notDell'] = 1;
                }
            }
        }

        //debug($repairs,'$repairs',1);
		return $repairs;
	}

	public function countRepairs( $repairs ) : array
    {
        $result = [
            '3d'=>0,
            'jew'=>0,
            'prod'=>0,
        ];
        foreach ( $repairs as &$repair )
        {
            switch ( (int)$repair['which'] )
            {
                case 0:
                    $result['3d']++;
                    break;
                case 1:
                    $result['jew']++;
                    break;
                case 2:
                    $result['prod']++;
                    break;
            }
        }
        return $result;
    }

    /**
     * вернет массив разрешенных статусов для текущего пользователя
     * @param array $uD
     * @return array
     */
    public function getPermittedStatuses( array $uD = [] ) : array
    {
        $userData = User::init();//$this->user;
        if ( $uD )
            $userData = $uD;

        $locations = explode(',',$userData['location']??''); // ID участки к которому относится юзер

        $statuses = $this->getStatLabArr('status'); // все возможные статусы
        $permittedStatuses = []; // разрешенные статусы на участок

        if ( (int)$userData['access'] > 1 )
        {
            foreach ( $statuses as $status )
            {
                foreach ( $locations as $location )
                {
                    // возьмем статусы которые подходят этому юзеру
                    if ( (int)$status['location'] === (int)$location ) $permittedStatuses[] = $status;
                }
            }
        } else {
            //иначе возьмем все статусы
            $permittedStatuses = $statuses;
        }

        return $permittedStatuses;
    }

	public function getStatus( $stockStatusID = '', string $selMode='') : array
    {

        $permittedStatuses = $this->getPermittedStatuses(); // разрешенные статусы на участок

		// Этот код ставит галочку на текущем статусе в соответствии со статусом в таблице Stock
		if ( !empty($stockStatusID) )
		{
			foreach ( $permittedStatuses as &$permittedStatus )
			{
				if ( $stockStatusID == $permittedStatus['id'] ) $permittedStatus['check'] = "checked";
			}

		} else {
            foreach ( $permittedStatuses as &$permittedStatus )
            {
                if ( $permittedStatus['id'] == 35 ) $permittedStatus['check'] = "checked";
            }
            // if ( $selMode !== 'selectionMode' ) 
            //     $permittedStatuses[0]['check'] = "checked";
		}

        $permittedStatuses = $this->sortStatusesByWorkingCenters($permittedStatuses);
        //debug($permittedStatuses,'123',1);
		return $permittedStatuses;
	}

	/**
     * отсортируем статусы по участкам, добавим описание, ответственных.
     * @param $statuses array
	 */
	private function sortStatusesByWorkingCenters($statuses)
    {
        //debug($this->workingCenters,'workingCenters');
        //debug($statuses,'$statuses',1);

        foreach ( $this->workingCenters as $key => &$workingCenters )
        {
            foreach ( $workingCenters as $wcKey => &$subUnit )
            {
                $subUnit['statuses'] = []; // массив доступных статусов
                $subUnit['user'] = ''; // ответственный из Users
                foreach ( $statuses as $status )
                {
                    if ( $status['location'] == $subUnit['id'] ) $subUnit['statuses'][] = $status;

                    // проверим есть ли Ответственный
                    foreach ( $this->users as $user )
                    {
                        if ($user['id'] == $subUnit['user_id'])
                        {
                            $subUnit['user'] = $user['fio'];
                            break;
                        }
                    }
                    if (empty($subUnit['user']) ) $subUnit['user'] = 'Нет';

                }

                // удалим подУчастки с пустыми статусами
                if ( empty($subUnit['statuses']) ) unset($workingCenters[$wcKey]);
            }

            // удалим пустые Участки
            if ( empty($workingCenters) ) unset($this->workingCenters[$key]);
        }

        //debug($this->workingCenters,'workingCenters');
        return $this->workingCenters;
    }

	public function getLabels($str='') : array
	{
		$labels = $this->getStatLabArr('labels');
		if ( isset($str) && !empty($str) ) {
			$arr_labels = explode(";",$str);
			for ( $i = 0; $i < count($arr_labels); $i++ )
			{
				for ( $j = 0; $j < count($labels); $j++ ) {
					if ( $arr_labels[$i] == $labels[$j]['name'] ) $labels[$j]['check'] = "checked";
				}
			}
		}
		return $labels;
	}


    /**
    * Возьмем систему оценок
    */
    public $gsArray = [];
    /**
     * @param int $gradeType
     * @return array
     * @throws \Exception
     */
    public function gradingSystem(int $gradeType = 0 ) : array
    {
        if ( empty($this->gsArray) )
            $this->gsArray = $this->findAsArray("SELECT * FROM grading_system ORDER BY work_name");

        if ( !$gradeType ) return $this->gsArray;
    
        $res = [];
        foreach ($this->gsArray as $gsRow)
            if ( $gsRow['grade_type'] == $gradeType ) $res[] = $gsRow;

        return $res;
    }

    /**
     * @param string $modelType
     * @throws \Exception
     * @return array
     */
    public function getModelsByType( string $modelType ) : array
    {
        $sql = "SELECT s.number_3d,s.vendor_code,s.model_type,s.collections,  i.pos_id,i.img_name,i.main,i.sketch
                FROM stock as s 
                LEFT JOIN images as i ON (i.pos_id=s.id)
                WHERE s.collections='Детали' AND s.model_type='$modelType'";

        $query = $this->findAsArray($sql);
        $sorted = $this->sortComplectedData($query,['number_3d','vendor_code','model_type','collections']);

        $resp = [];
        foreach ( $sorted as $detail )
        {
            $nameVC = ($detail['vendor_code']??'') ?: ($detail['number_3d']??'');
            $a = $this->html->tag('a')
                    ->setAttr(['class'=>'imgPrev','elemToAdd'=>'','imgtoshow'=>$detail['img_name']])
                    ->setTagText($nameVC)
                    ->create();
            $resp[] = $this->html->tag('li')->setTagText($a)->create();
        }

        return $resp;
    }

}
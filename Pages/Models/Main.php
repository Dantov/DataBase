<?php
namespace models;

use soffit\{ActiveQuery,HtmlHelper};
use libs\classes\AppCodes;

class Main extends General 
{

    public $assist;
    public array $row = [];
    public $wholePos;
    public $today;

    // Pagination start page / positions per page
    public int $start = 0;
    public int $perPage = 0; 

    protected string $searchQuery = '';

    /**
     * Main constructor.
     * @param bool $assist
     * @param bool $user
     * @param array $searchQuery
     * @throws \Exception
     */
    public function __construct($assist=false, $user=false, string $searchQuery='' )
    {
        parent::__construct(); //General

        $this->assist = $this->session->assist;

        if (!empty($searchQuery))
             $this->searchQuery = $searchQuery;

        $this->today = time();
        
        $this->connectToDB();
    }



    /**
     * Pack some data variables for tools panel under Header Bar 
     * to use in Main views (tiles, kits etc.)
     */
    public function getToolsPanelVars() : array
    {
        $sd = $this->getServiceData(['model_type','gems_names']);
        $result['modelTypes'] = $sd['model_type'];
        $result['modelGemTypes'] = $sd['gems_names'];
        $result['modelMaterials'] = $this->getModelMaterialsSelect();

        switch ($this->assist['reg'])
        {
            case   "number_3d": $result['showsort'] = "№3D"; break;
            case "vendor_code": $result['showsort'] = "Арт."; break;
            case        "date": $result['showsort'] = "Дате"; break;
            case      "status": $result['showsort'] = "Статусу"; break;
            case  "model_type": $result['showsort'] = "Типу"; break;
            default: $result['showsort'] = "Дате";
        }
        switch ($this->assist['sortDirect'])
        {
            case "ASC": 
                $result['chevron_'] = "triangle-top";
                $result['chevTitle'] = "По возростанию"; 
            break;
            case "DESC":
                $result['chevron_']  = "triangle-bottom";
                $result['chevTitle'] = "По убыванию";
            break;
            default: {$result['chevron_']="triangle-bottom";$result['chevTitle'] = "По убыванию";}
        }
        switch ($this->session->selectionMode['activeClass'])
        {
            case "btnDefActive": 
                $result['toggleSelectedGroup'] = '';
                $result['activeSelect'] = "btnDefActive";
            break;
            default: 
                $result['activeSelect'] = "";
                $result['toggleSelectedGroup'] = "hidden";
            break;
        }
        
        return $result;
    }
    /**
     * Will fully sort statuses by his working centers.
     * Need for Tool panel in Main and etc.
     * @return array
     * @throws \Exception
     */
    public function getStatusesToolsPanel()
	{
        $this->getUsers();
        // все возможные статусы
        $statuses = $this->statuses;
        $workingCentersDB = $this->workingCentersDB;

        foreach ( $workingCentersDB as $key => &$workingCenters )
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
            if ( empty($workingCenters) ) unset($workingCentersDB[$key]);
        }

        return $workingCentersDB;
    }


    

    public function totalModelsCount() : int
    {
        $sql = "SELECT COUNT(1) as c " . $this->prepareStockQuery(false);
        //debug($sql,'totalModelsCount',1);
        return $this->findOne($sql)['c'];
    }
    protected static string $stockStringQuery = "";
    protected function prepareStockQuery(bool $showall = false) : string
    {
        if ( self::$stockStringQuery ) return self::$stockStringQuery;

        $where = "";
        $and = false;

        $modelType = $this->assist['modelType'];
        if ( $modelType !== "Все" ) // Если выбран тип модели, коллекцию "детали" тоже включаем
            $showall = true;
        
        if ( !$showall )
        {
            $where = "WHERE collections<>'Детали'";
            $and = true;
        }
        
        if ( $this->assist['collection_id'] > -1 ) 
        {
            $where = "WHERE collections like '%{$this->assist['collectionName']}%'";
            $and = true;
        }

        if ( $modelType !== "Все" )
        {
            $where .= ($and ? " AND " : " WHERE ") . " model_type='$modelType'";
            $and = true;
        }

        /** Location Centers sort by  */
        if ( !empty($this->assist['drawBy_'] === 4) )
        {
            if ( !empty($this->assist['wcSort']['ids']) )
            {
                $this->assist['regStat'] = 'Нет';
                $this->session->setKey('assist',$this->assist);
                if ( $in = $this->dopSortByWC() ) 
                    $where .= ($and ? " AND " : " WHERE ") . " status " . $in;
            }
        }

        $modelMat = $this->assist['modelMaterial'];
        if ( $modelMat !== "Все" )
        {
            $this->registerTable(['metal_covering']);
            $posIDs_m = SelectBy::modelMaterial($modelMat, $this->METAL_COVERING);
            if ( !empty($posIDs_m) ) 
                $posIDs_m = "OR (id IN ($posIDs_m))";

            $where .= ($and ? " AND " : " WHERE ") . " (model_material LIKE '%$modelMat%' $posIDs_m)";
        }

        $gemType = $this->assist['gemType']??"Все";
        if ( $gemType !== "Все" )
        {
            $this->registerTable(['gems']);
            $posIDs_g = SelectBy::gemType($gemType, $this->GEMS);
            $posIDs_g = !empty($posIDs_g) ? " (id IN ($posIDs_g))" : "(id=-1)";

            $where .= ($and ? " AND " : " WHERE ") . " $posIDs_g";
        }


        /** History is OFF! Just last status added. Query from stock*/
        $regStat = $this->assist['regStatID'];
        if ( $this->assist['regStatID'] && ($this->assist['byStatHistory'] !== 1) )
            $where .= ($and ? " AND " : " WHERE ") . " status='$regStat'";

        /** History is ON! Adding IDs from Statuses table*/
        if ( $this->assist['byStatHistory'] === 1 )
        {
            $dates = [];
            if ( !empty($this->session->assist['byStatHistoryFrom'])) 
                $dates['from'] = $this->session->assist['byStatHistoryFrom'];
            if ( !empty($this->session->assist['byStatHistoryTo'])) 
                $dates['to'] = $this->session->assist['byStatHistoryTo'];

            $posIDs_sh = SelectBy::byStatusesHistory($regStat, $dates, $this->STATUSES);
            if ( !empty($posIDs_sh) ) 
            {
                $posIDs_sh = " (id IN ($posIDs_sh))";
                $where .= ($and ? " AND " : " WHERE ") . " $posIDs_sh";
            }
        }

        /** Search query at the end */
        if ( !empty($this->searchQuery) )
            $where .= ($and ? " AND " : " WHERE ") . "($this->searchQuery)";
        
        self::$stockStringQuery = "FROM stock " . $where . " ORDER BY " .$this->assist['reg']." ".$this->assist['sortDirect'];
        return self::$stockStringQuery;
    }

    

    /**
     * @param bool $showall - Флаг исключающий коллекцию детали из выборки 
     * (используется для выбора эсскизов со всех колл. для Богдана)
     * 
     * @return array|bool
     * @throws \Exception
     */
    public function getModelsFormStock( bool $showall = false, string $searchInput='' )
    {
        $limit = " LIMIT $this->start, $this->perPage";
        $sql = "SELECT * " . $this->prepareStockQuery($showall,$searchInput) . $limit;
        //debug($sql,'sql',1);
        //debugAjax($sql,'sql',END_AB);

        $this->row = $this->findAsArray($sql);

        return count($this->row);
	}




    /**
     * For Excel/PDF exports
     * @param $iter - элемент массива
     * @return array
     */
    /*
    public function getRow($iter=false)
    {
        if ( is_int($iter) ) return $this->row[$iter];
        return $this->row;
    }
    */
    /**
    * Sort all statuses for usefull using below:
    * Need for drawing tables (in working-centers and localion-centers) drawTableRow and drawTable2Row
    * @param $id - модели pos_id
    * @return array
    */
    public function getStatusesTable( int $stockID ) : array
    {
        $res = [];
        $statsQuery = $this->findAsArray(" SELECT id,status,name,date,pos_id FROM statuses WHERE pos_id='$stockID' ");
        foreach( $statsQuery as $stats_row )
        {
            $stats_row['status_id'] = $stats_row['status'];

            foreach ( $this->statuses as $status )
            {
                if ( $status['id'] == $stats_row['status_id'] ) 
                    $stats_row['status'] = $status;
            }
            $res[] = $stats_row;
        }
        return $res;
    }







    /** DRAWING SINGLE MODEL */
    protected function selectMainImg(string $which, array &$images) : string
    {
        foreach ( $images as &$image )
        {
            if ( !isset($image[$which]) ) continue;

            if ($image[$which] == 1 ) 
            {
                return $image['img_name'];
                //$mainIsset = true;
                //break;
            }
        }
       return '';
    }
    /**
     * плиткой
     * @param array $rowImages
     * @param array $row - each model data
     * @param $comlectIdent true - drawModel вызвана в отрисовке комплекта
     * @throws \Exception
     */
	protected function drawModel(&$row, &$rowImages, &$rowStls, bool $comlectIdent = false, array $newKitCounters = [])
	{
        $newKitStarts = $newKitCounters['newKitStarts']??false;
        $newKitEnds = $newKitCounters['newKitEnds']??false;

		//по дефолту
		$vc_show = "";
		if ( !empty($row['vendor_code']) ) $vc_show = " | ".$row['vendor_code'];
		$columns = $comlectIdent === true ? 3 : 2;

        $images = [];
        foreach ($rowImages as $thisImage)
            if ( $thisImage['pos_id'] == $row['id'] )
                $images[] = $thisImage;

        //-------------- Выбираем главную картинку ------
        $showimg = '';

        if ( !$showimg ) $showimg = $this->selectMainImg('main', $images);
        if ( !$showimg ) $showimg = $this->selectMainImg('sketch', $images);
        // если не нашли
        if ( !$showimg && $images )
            $showimg = $images[array_key_first($images)]['img_name'];

        //-------------------

        $path = $row['number_3d'].'/'.$row['id'].'/images/';

        if ( !$showimg )
            $showimg = _stockDIR_HTTP_ . "default.jpg";

		if ( !file_exists(_stockDIR_. $path . $showimg) ) // file_exists работает только с настоящим путём!! не с HTTP
		{
		    $showimg = _stockDIR_HTTP_ . "default.jpg";
		} else {
		    //Файл Есть!

            //Проверим на наличие превью
            if ( $prevImgName = $this->checkSetPreviewImg($path, $showimg) )
            {
                $showimg = _stockDIR_HTTP_.$path.$prevImgName;
            } elseif ( ImageConverter::makePrev( $path, $showimg ) ) {
                // Превью создана!
                $showimg = _stockDIR_HTTP_ . $path . ImageConverter::getLastImgPrevName();
            } else {
                // оригин. картинка, если ничего не получилось
                $showimg = _stockDIR_HTTP_ . $path . $showimg;
            }
        }
		$btn3D = false;
		if ( array_key_exists($row['id'], $rowStls) ) $btn3D = true;

		// смотрим отрисовывать ли нам кнопку едит
		$editBtn = false;
        if ( User::permission('editModel') )
        {
            $editBtn = true;
        } elseif ( User::permission('editOwnModels') ) {
            $userRowFIO = explode(' ', $this->user['fio'])[0];
            $authorFIO = $row['author'];
            $modellerFIO = $row['modeller3D'];
            $jewelerName = $row['jewelerName'];
            if ( mb_stristr($authorFIO, $userRowFIO) !== FALSE || mb_stristr($modellerFIO, $userRowFIO) !== FALSE || mb_stristr($jewelerName, $userRowFIO) !== FALSE )
                $editBtn = true;
        }
		
		$status = $this->getStatus($row);
		$labels = $this->getLabels($row['labels']);	
		$checkedSM = self::selectionMode($row['id'], $this->session->selectionMode);
		
        // Укорочение длинны типа модели
        $modTypeCount = mb_strlen($row['model_type']);
        if ( $modTypeCount > 14 )
        {
            $modTypeStr = mb_substr($row['model_type'], 0, 11);
            $modTypeStr.= "...";
        } else {
            $modTypeStr = $row['model_type'];
        }

        // проверка на всю ширину
        $columnsLG = 2;
        if ( $_SESSION['assist']['containerFullWidth'] == 1 )
            $columnsLG = 1;
        
		require _WEB_VIEWS_ . "main/drawModel.php";
	}
	private static function selectionMode($id, array $selectionMode) : array
	{
		$defRes = [
            'inptAttr'=>'',
            'class'=>'glyphicon-unchecked',
            'active'=>'hidden'
        ];
		if ( $selectionMode['activeClass'] == "btnDefActive" ) 
        {	
			$defRes['active'] = "";
			
			$selectedModels = $selectionMode['models'];
			if ( !empty($selectedModels) ) 
            {
				if ( array_key_exists($id, $selectedModels) ) {
					$defRes['inptAttr'] = "checked";
					$defRes['class'] = "glyphicon-check";
					
					return $defRes;
				}
			}
		}
		return $defRes;
	}





    public function statusBar( string $type, array $vars ) : string
    {
        extract($vars);
        static $html = new HtmlHelper();
        $modelsFrom = $modelsFrom === 0 ? 1 : $modelsFrom;

        $f5 = $html->tag('i')->setTagText('Показано (изделий): ')->create() . $modelsFrom . " - " . $modelsTo;
        
        switch ($type)
        {
            case 'tiles':
                $f2 = $html->tag('i')->setTagText('Найдено изделий:')->create() . $wholePos;
                return   $f2 . " || " . $f5;
            break;
            case 'kits':
                $f2 = $html->tag('i')->setTagText('Всего:')->create() . $totalM;
                $f3 = $html->tag('i')->setTagText('(Комплектов):')->create() . $ComplShown;
                $f4 = $html->tag('i')->setTagText('(комплектов):')->create() . $wholePos;
                return   $f2." | ". $f3 . " | ". $f5 . " ". $f4;
            break;
        }
    }

    /*
	public function drawPagination()
    {
		$pagination = '';
		
		$max_shown_pagin = 10; // максимальное число отображаемых квадратиков пагинации
		//округлили вверх - это общее кол-во страниц(квадратиков)
		$paginLength = ceil( $this->wholePos / $this->assist['maxPos'] );
	
		$pagination .= '<nav aria-label="Page navigation">';
        $pagination .= '<ul class="pagination">';
		
		// если был переход на след. часть квадратиков то рисуем кнопку назад
		if ( isset($this->assist['st_prevPage']) && $this->assist['startFromPage'] != 0 ) {
			$startI = $this->assist['startFromPage'] - $max_shown_pagin; // флаг - с какой стр. начинать рисовать квадратики
			$pagination .= "
				<li>
					<a href=\"/main/?page=$startI&start_FromPage=$startI\" aria-label=\"Next\" title=\"Назад на пред. 10\">
						<span aria-hidden=\"true\">&laquo;</span>
					</a>
				</li>
			";
		}
		
		// цикл по отрисовке квадратиков
                $nn = null;
		for ( $i = $this->assist['startFromPage']??0; $i < $paginLength; $i++ ) {
			
			$classAct = '';
			if ( $this->assist['page'] == $i ) $classAct = 'class="active"';
			$pagination .= "<li $classAct>";
			$pagination .= '<a href="/main/?page='.$i.'">'.($i+1).'</a>';
			$pagination .= '</li>';
			
			// если это не первая стр. то начинаем проверять на кратность макс. разрешенных квадратиков т.е 10
			if ( $i != 0 )	$nn = ($i+1) / $max_shown_pagin; 
			
			// если остаток от деления целый, знач завершаем цикл и рисуем кнопку вперед
			// для след. страниц пагинации.
			if ( is_int($nn) ) {
				$nextI = $i + 1; // определяем след. страницу на которую перейдем после клика
				$pagination .= "
				<li>
					<a href=\"/main/?page=$nextI&start_FromPage=$nextI&st_prevPage=$nextI\" aria-label=\"Next\" title=\"Вперед на след. 10\">
						<span aria-hidden=\"true\">&raquo;</span>
					</a>
				</li>
				";
				break;
			}
		}
		
		$pagination .= '</ul>';
        $pagination .= '</nav>';
		return $pagination;
	}
    */


}
<?php
namespace Views\_ModelView\Models;
use Views\_Globals\Models\General;
use Views\_Globals\Models\User;
use Views\_SaveModel\Models\ImageConverter;
use Views\vendor\core\ActiveQuery;
use Views\vendor\core\HtmlHelper;


class ModelView extends General {

	private $id;
	public $number_3d;

	public  $row;
	public  $coll_id;
	
	private $img;
	private $gems_Query;
	private $dopVc_Query;
	private $coll_Query;
    public  $rep_Query;
	private $statuses_Query;
	private $complected;

    /**
     * ModelView constructor.
     * @param bool $id
     * @throws \Exception
     */
    public function __construct($id = false )
    {
        parent::__construct();

        $this->id = (int)$id;

        $this->connectToDB();
        $this->dataQuery();
    }


    /**
     * @throws \Exception
     */
    public function dataQuery()
    {
        $aq = new ActiveQuery();
        $aq->registerTable(['stock', 'images', 'rhino_files', 'stl_files', 'ai_files',
            'service_data','vc_links', 'gems', 'repairs', 'statuses']);

        $stock      = $aq->stock??null;
        $images     = $aq->images??null;
        $rhinoFiles = $aq->rhino_files??null;
        $stlFiles   = $aq->stl_files??null;
        $aiFiles    = $aq->ai_files??null;
        $gems       = $aq->gems??null;
        $vcLinks    = $aq->vc_links??null;
        $repairs    = $aq->repairs??null;
        $service_data = $aq->service_data??null;
        $statuses     = $aq->statuses??null;

        $aq->link(['id'=>$stock],'=',['pos_id'=>$images]);
        $aq->link(['id'=>$stock],'=',['pos_id'=>$stlFiles]);
        $aq->link(['id'=>$stock],'=',['pos_id'=>$aiFiles]);
        $aq->link(['id'=>$stock],'=',['pos_id'=>$rhinoFiles]);

        $stockRes = $stock
            ->select(['*'])
            ->join($images,['*'])
            ->join($stlFiles,['stl_name','pos_id'])
            ->join($aiFiles,['ai_name'=>'name','pos_id'])
            ->join($rhinoFiles,['3dm_name'=>'name','pos_id'])
            ->where('id','=',$this->id)
            ->exe();
        $this->row = $stockRes[0]??null;
        $this->img = $stockRes;



        $this->rep_Query = $repairs->select(['*'])->where('pos_id','=',$this->id)->exe();
        $this->dopVc_Query = $vcLinks->select(['*'])->where('pos_id','=',$this->id)->exe();
        $this->gems_Query = $gems->select(['*'])->where('pos_id','=',$this->id)->exe();
        $this->statuses_Query = $statuses->select(['status','name','date'])->where('pos_id','=',$this->id)->exe();

        $this->number_3d = $this->row['number_3d'];
        $this->complected = $stock
            ->select(['id','model_type','number_3d'])
            ->join($images,['pos_id','img_name','main','sketch'])
            ->where('number_3d','=',$this->number_3d)
            ->and(['id','<>',$this->id])
            ->asArray()
            ->exe();

        $this->coll_Query = $service_data
            ->select(['id','name'])
            ->where('name','IN',$this->getCollections(true))
            ->and(['tab','=','collections'])
            ->exe();

	}

    /**
     * @param bool $getINStr
     * @return array|string
     * @throws \Exception
     */
    public function getCollections( $getINStr = false )
    {
        $IN = '';
        if ( $getINStr )
        {
            $collections = explode(';',$this->row['collections']);
            foreach ( $collections as $collection ) $IN .= "'".$collection."',";
            $IN =  trim($IN,",");
            return $IN;
        }

        return $this->coll_Query;
    }

    /**
     * @throws \Exception
     */
    public function getStl()
    {
        return $this->row['stl_name']??null;
	}

    /**
     * @throws \Exception
     */
    public function getAi()
    {
        return $this->row['ai_name']??null;
	}

    /**
     * @throws \Exception
     */
    public function get3dm()
    {
        return $this->row['3dm_name']??null;
    }

    /**
     * @return array
     */
    public function getRepairs()
    {
        return $this->rep_Query;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function usedInModels()
    {
        $aq = new ActiveQuery();
        $stock = $aq->registerTable('stock');
        $vc_links = $aq->registerTable(['vc_links']);
        $images = $aq->registerTable(['images']);

        $vclBuild = $vc_links->select(['pos_id'])->where('vc_3dnum','LIKE',"%{$this->number_3d}%");
        if ( !empty( $this->row['vendor_code'] ) )
            $vclBuild->or('vc_3dnum','LIKE',"%{$this->row['vendor_code']}%");

        $vclBuild = $vclBuild->build();

        $aq->link(['id'=>$stock],'=',['pos_id'=>$images]);
        $res = $stock
            ->select(['id','model_type','number_3d','vendor_code'])
            ->join($images,['img_name'])
            ->joinAnd($images,'main', '=', 1)
            ->where('id','IN',$vclBuild)->and('id','<>',$this->row['id'])
            ->asArray()
            ->exe();

        return $res;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getDescriptions()
    {
        $aq = new ActiveQuery();
        $description = $aq->registerTable(['users','description']);
        $users = $aq->users??null;
        $aq->link(['userID'=>$description],'=',['id'=>$users]);

        $df = function()
        {
          return ['fieldNames'=>['dt'=>'date'], 'function'=>"DATE_FORMAT(%dt%, '%d.%m.%Y')" ];
        };
        $res = $description
            ->select(['num','text','date'=>$df,'pos_id'])
            ->join($users,['userName'=>'fio'])
            ->where('pos_id','=',$this->id)
            ->asArray()
            ->exe();

        return $res;
    }

    /**
     * @param bool $forPdf
     * @return array|string
     * @throws \Exception
     */
	public function getComplectes($forPdf=false)
    {
    	if ( empty($this->complected) ) return [];
        if ($forPdf) return $this->complected;

        return $this->sortComplectedData($this->complected,['id','number_3d','model_type']);
	}

    /**
     * @return array
     * @throws \Exception
     */
    public function getImages()
    {
		$images = [];

        foreach ( $this->img as &$img ) $images[$img['id']] = $img; // чтоб работали клики по мал. картинкам

        foreach ( $images as &$image )
        {
            $path = $this->number_3d.'/'.$this->id.'/images/';
            $fileImg = $image['img_name'];

            if ( !file_exists(_stockDIR_.$path.$fileImg) )
            {
                $image['imgPath'] = _stockDIR_HTTP_."default.jpg";
            } else {
                // Файл Есть!
                $image['imgPath'] = _stockDIR_HTTP_.$path.$fileImg;

                // Проверим превьюшку
                $image['imgPrevPath'] = '';
                if ( $prevImgName = $this->checkSetPreviewImg($path, $fileImg) )
                {
                    $image['imgPrevPath'] = _stockDIR_HTTP_.$path.$prevImgName;
                } elseif ( ImageConverter::makePrev( $path, $fileImg ) ) {
                    // Превью создана!
                    $image['imgPrevPath'] = _stockDIR_HTTP_ . $path . ImageConverter::getLastImgPrevName();
                }
            }

        }

		return $images;
	}

    /**
     * проверим наличие статусов в картинках
     * что бы выбрать, какую отобразить главной
     * @param array $images
     * @return array
     */
	public function choseMainImage( array &$images ) : array
    {
        $mainImgID = '';
        $setMainImg = function(&$image)
        {
            $mainImg['src'] = $image['imgPath'];
            $mainImg['id'] = $image['id'];
            $image['active'] = 1;

            return $mainImg;
        };

        foreach ( $images as &$image )
        {
            if ( trueIsset($image['main']) )
            {
                $mainImgID = $image['id'];
                break;
            }
            if ( trueIsset($image['sketch']) )
            {
                $mainImgID = $image['id'];
            }
        }

        //везьмем первую, если ничего не выбрали
        if ( !$mainImgID )
        {
            $mainImg = $setMainImg($images[array_key_first($images)]);
        } else {
            $mainImg = $setMainImg($images[$mainImgID]);
        }

        return $mainImg;
    }

    /**
     * @return array|bool
     * @throws \Exception
     */
    public function getModelMaterials()
	{
		$addEdit = new \Views\_AddEdit\Models\AddEdit($this->id);

        $addEdit->connectDBLite();
        $mats = $addEdit->getMaterials($this->row);
        return $mats;
	}


	public function getGems()
    {
		$result = array();

		$c = 0;
		foreach ( $this->gems_Query as $row_gems )
        {
            if ( !empty($row_gems['gems_sizes']) )
                $sizeGem = is_numeric($row_gems['gems_sizes']) ? "Ø".$row_gems['gems_sizes']." мм" : $row_gems['gems_sizes']." мм";

            if ( !empty($row_gems['value']) )
                $valueGem = $row_gems['value']." шт";

            $result[$c]['gem_num'] = $c+1;
            $result[$c]['gem_size'] = $sizeGem??'';
            $result[$c]['gem_value'] = $valueGem??'';
            $result[$c]['gem_cut'] = $row_gems['gems_cut'];
            $result[$c]['gem_name'] = $row_gems['gems_names'];
            $result[$c]['gem_color'] = $row_gems['gems_color'];
            $c++;
        }
		return $result;
	}

    /**
     * @param $id
     * @param $vc_3dNum
     * @return string
     * @throws \Exception
     */
    protected function links($id, $vc_3dNum)
    {
        $sql = "SELECT st.id, st.number_3d, img.pos_id, img.img_name, img.main, img.sketch
				FROM stock st 
					LEFT JOIN images img ON ( img.pos_id = $id )
				WHERE st.id='$id' ";
        $linkQuery = $this->findAsArray( $sql );

        $fileImg = $this->sortComplectedData($linkQuery,['id','number_3d'])[$id]['img_name'];

        $html = new HtmlHelper();
        return $html->tag("a")
                    ->setAttr(['imgtoshow'=>$fileImg, 'href'=>HtmlHelper::URL('/',['id'=>$id])]) //_rootDIR_HTTP_ .'model-view/?id='.$id
                    ->setTagText($vc_3dNum)
                    ->create();
    }

    /**
     * @param $vc_3dnum
     * @param $vc_name
     * @return null|string
     * @throws \Exception
     */
    protected function vc_3dnumExpl($vc_3dnum, $vc_name)
    {
        $arr = explode('/',$vc_3dnum);
        $quer = mysqli_query($this->connection, " SELECT id,number_3d,vendor_code FROM stock WHERE model_type='$vc_name' ");

        $link  = null;

        if ( $quer->num_rows > 0 ) {


            while( $row_vc = mysqli_fetch_assoc($quer) ) {

                if ( !empty($row_vc['vendor_code']) )
                {
                    if ( trim($arr[0]) == $row_vc['vendor_code'] ) {
                        $link = $this->links($row_vc['id'], $vc_3dnum);
                        break;
                    }
                }

                if ( trim($arr[0]) == $row_vc['number_3d'] )
                {
                    $link = $this->links($row_vc['id'], $vc_3dnum);
                    break;
                }

                if ( isset($arr[1]) )
                {
                    if ( !empty($row_vc['vendor_code']) ) {
                        if ( trim($arr[1]) == $row_vc['vendor_code'] ) {
                            $link = $this->links($row_vc['id'], $vc_3dnum);
                            break;
                        }
                    }
                    if ( trim($arr[1]) == $row_vc['number_3d'] ) {
                        $link = $this->links( $row_vc['id'], $vc_3dnum);
                        break;
                    }
                }
            }
        }
        return $link;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getDopVC()
    {
		$result = array();
		$c = 0;
		foreach ( $this->dopVc_Query as $row_dop_vc )
        {
            $linkVCnum = $this->vc_3dnumExpl($row_dop_vc['vc_3dnum'], $row_dop_vc['vc_names'] );
            $linkVCnum = $linkVCnum ? $linkVCnum : $row_dop_vc['vc_3dnum'];

            $result[$c]['vc_num'] = $c+1;
            $result[$c]['vc_names'] = $row_dop_vc['vc_names'];
            $result[$c]['vc_link'] = $linkVCnum;
            $result[$c]['vc_descript'] = $row_dop_vc['descript'];
            $c++;
        }
		return $result;
	}

	public function getLabels($labelsStr=false)
    {
        return parent::getLabels($this->row['labels']);
    }

    /**
     * @param bool $id
     * @param string $status_name
     * @param string $status_date
     * @return array
     * @throws \Exception
     */
    public function getStatuses($id = false, $status_name = '', $status_date = '' )
    {
        $statuses = $this->getStatLabArr('status');
        $result = [];
        //$stats_quer = mysqli_query($this->connection, " SELECT status,name,date FROM statuses WHERE pos_id='{$this->id}' ");

        //if ( !mysqli_num_rows($stats_quer) )
        if ( !$this->statuses_Query )
        {
            $statusT = [];
            $statusT['pos_id'] = $this->id;
            $statusT['status'] = $this->row['status'];
            $statusT['creator_name'] = "";
            $statusT['UPdate'] = $this->row['status_date'];
            $this->addStatusesTable($statusT);
            foreach ( $statuses?:[] as $status )
            {
                if ( $statusT['status'] === $status['name_ru'] )
                {
                    $result[0]['class'] = $status['class'];
                    $result[0]['classMain'] = $status['name_en'];
                    $result[0]['glyphi'] = $status['glyphi'];
                    $result[0]['title'] = $status['title'];
                    $result[0]['status'] = $status['name_ru'];
                    $result[0]['name'] = $statusT['name'];
                    $result[0]['date'] = ($statusT['date'] == "0000-00-00") ? "" : date_create( $statusT['UPdate'] )->Format('d.m.Y')."&#160;";
                    break;
                }
            }

            //debug($result,'$result',1);
            return $result;
        }

        $c = 0;
        /*
        while( $statuses_row = mysqli_fetch_assoc($stats_quer) )
        {
            foreach ( $statuses as $status )
            {
                if ( $statuses_row['status'] === $status['id'] )
                {
                    $result[$c]['ststus_id'] = $status['id'];
                    $result[$c]['class'] = $status['class'];
                    $result[$c]['classMain'] = $status['name_en'];
                    $result[$c]['glyphi'] = $status['glyphi'];
                    $result[$c]['title'] = $status['title'];
                    $result[$c]['status'] = $status['name_ru'];
                    $result[$c]['name'] = $statuses_row['name'];
                    $result[$c]['date'] = ($statuses_row['date'] == "0000-00-00") ? "" : date_create( $statuses_row['date'] )->Format('d.m.Y')."&#160;";
                    $c++;
                    break;
                }
            }

        }
        */
        foreach ( $this->statuses_Query as $statuses_row )
        {
            foreach ( $statuses as $status )
            {
                if ( $statuses_row['status'] === $status['id'] )
                {
                    $result[$c]['ststus_id'] = $status['id'];
                    $result[$c]['class'] = $status['class'];
                    $result[$c]['classMain'] = $status['name_en'];
                    $result[$c]['glyphi'] = $status['glyphi'];
                    $result[$c]['title'] = $status['title'];
                    $result[$c]['status'] = $status['name_ru'];
                    $result[$c]['name'] = $statuses_row['name'];
                    $result[$c]['date'] = ($statuses_row['date'] == "0000-00-00") ? "" : date_create( $statuses_row['date'] )->Format('d.m.Y')."&#160;";
                    $c++;
                    break;
                }
            }
        }
        return $result;
    }


    /**
     * смотрим отрисовывать ли нам кнопку едит
     * @throws \Exception
     */
    public function editBtnShow() : bool
    {
        if ( User::permission('editModel') )
        {
            return true;
        } elseif ( User::permission('editOwnModels') ) {

            $userRowFIO = User::getSurname();
            $authorFIO = $this->row['author'];
            $modellerFIO = $this->row['modeller3D'];
            $jewelerName = $this->row['jewelerName'];

            if (   mb_stristr($authorFIO, $userRowFIO)   !== FALSE
                || mb_stristr($modellerFIO, $userRowFIO) !== FALSE
                || mb_stristr($jewelerName, $userRowFIO) !== FALSE
               )
                return true;
        }

        return false;
    }

    /**
     * пока не нужно
     */
    private function setPrevPage()
    {
        $thisPage = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        if ( $thisPage !== $_SERVER["HTTP_REFERER"] ) {
            $_SESSION['prevPage'] = $_SERVER["HTTP_REFERER"];
        }
    }

}
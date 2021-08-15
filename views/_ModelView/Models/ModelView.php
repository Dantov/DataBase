<?php
namespace Views\_ModelView\Models;
use Views\_Globals\Models\General;
use Views\_SaveModel\Models\ImageConverter;


class ModelView extends General {

	
	private $id;
	public $number_3d;

	public  $row;
	public  $coll_id;
	public  $rep_Query;
	
	private $img;
	//private $img_Query;
	private $gems_Query;
	private $dopVc_Query;
	private $stl_Query;
	private $complected;
	private $ai_Query;

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
		$this->row  = $this->findOne( " SELECT * FROM stock    WHERE     id='$this->id' ");
		$this->img  = $this->findAsArray( " SELECT * FROM images   WHERE pos_id='$this->id' ");
		//$this->img_Query   = mysqli_query($this->connection, " SELECT * FROM images    WHERE pos_id='$this->id' ");
        $this->gems_Query  = mysqli_query($this->connection, " SELECT * FROM gems      WHERE pos_id='$this->id' ");
        $this->dopVc_Query = mysqli_query($this->connection, " SELECT * FROM vc_links  WHERE pos_id='$this->id' ");
        $this->stl_Query   = mysqli_query($this->connection, " SELECT * FROM stl_files WHERE pos_id='$this->id' ");
        $this->ai_Query    = mysqli_query($this->connection, " SELECT * FROM ai_files  WHERE pos_id='$this->id' ");
        $this->rep_Query   = mysqli_query($this->connection, " SELECT * FROM repairs   WHERE pos_id='$this->id' ");
		
		//$this->row = mysqli_fetch_assoc($stock_Query);
		$this->number_3d = $this->row['number_3d'];

		$sql = " SELECT st.id, st.model_type, img.pos_id, img.img_name 
				FROM stock st 
					LEFT JOIN images img 
					ON (st.id = img.pos_id AND img.main=1) 
				WHERE st.number_3d='{$this->number_3d}' 
				AND st.id<>'{$this->id}' ";
		//debug($sql,'$sql');
		$this->complected = $this->findAsArray( $sql );
		//debug($this->complected,'complected',1);


	}

    public function getCollections()
    {
        $collections = explode(';',$this->row['collections']);
        $collectionStr = '';
        foreach ( $collections as $collection ) $collectionStr .= "'".$collection."',";
        $collectionStr = "(". trim($collectionStr,",") . ")";

        $collId_Query = mysqli_query($this->connection, " SELECT id,name FROM service_data WHERE name IN $collectionStr AND tab='collections' ");
        $res = [];
        while( $coll = mysqli_fetch_assoc($collId_Query) ) $res[] = $coll;
        return $res;
    }
	
	public function getStl()
    {
		if ( $this->stl_Query->num_rows > 0 ) {
			$stl_file = mysqli_fetch_assoc($this->stl_Query);
            return $stl_file;
		}
        return false;
	}

	public function getAi()
    {
		if ( $this->ai_Query->num_rows > 0 )
		{
			$ai_file = mysqli_fetch_assoc($this->ai_Query);
			return $ai_file;
		}
		return false;
	}

    /**
     * @return array|bool
     * @throws \Exception
     */
    public function get3dm()
    {
        return $this->findOne( " SELECT * FROM rhino_files WHERE pos_id='$this->id' ");
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function usedInModels()
    {
        $vc = "";
        if ( !empty( $this->row['vendor_code'] ) ) $vc = "OR vc_3dnum LIKE '%{$this->row['vendor_code']}%'";

        $sql = " SELECT s.id, s.number_3d, s.vendor_code, s.model_type FROM stock as s WHERE s.id IN
                  ( SELECT pos_id FROM vc_links WHERE vc_3dnum LIKE '%{$this->number_3d}%' $vc ) AND s.id <> {$this->row['id']}";
        return $this->findAsArray( $sql );
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getDescriptions()
    {
        $sql = "SELECT d.num, d.text, DATE_FORMAT(d.date, '%d.%m.%Y') as date, d.pos_id, u.fio as userName
                FROM description as d
                  LEFT JOIN users as u
                    ON (d.userID = u.id ) 
                WHERE d.pos_id = $this->id";
        return $this->findAsArray( $sql );
    }

    /**
     * @param bool $forPdf
     * @return array|string
     */
	public function getComplectes($forPdf=false)
    {
    	if ( empty($this->complected) ) return [];
        if ($forPdf) return $this->complected;

		foreach ($this->complected as &$complect) 
		{
			$imagePath = $this->number_3d.'/'.$complect['id'].'/images/'.$complect['img_name'];
			$complect['img_name'] = _stockDIR_HTTP_ . $imagePath;
            if ( !file_exists(_stockDIR_ . $imagePath) ) $complect['img_name'] = _stockDIR_HTTP_."default.jpg";
		}
		return $this->complected;
	}
	
	public function getImages()
    {
		$images = [];
        //debug($this->img,'img');

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
                $prevImgName = $this->checkSetPreviewImg($path, $fileImg);
                $image['imgPrevPath'] =  $prevImgName ? _stockDIR_HTTP_ . $path . $prevImgName : '';
            }

        }
        //debug($images,'$images',1);

		return $images;
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
        //$addEdit->closeDB();
        return $mats;
	}


	public function getGems() {
		$result = array();
		$c = 0;
		while( $row_gems = mysqli_fetch_assoc($this->gems_Query) ) {
			if ( !empty($row_gems['gems_sizes']) ) {
				$sizeGem = is_numeric($row_gems['gems_sizes']) ? "Ø".$row_gems['gems_sizes']." мм" : $row_gems['gems_sizes']." мм";	
			}
			if ( !empty($row_gems['value']) ) $valueGem = $row_gems['value']." шт";
			$result[$c]['gem_num'] = $c+1;
			$result[$c]['gem_size'] = $sizeGem;
			$result[$c]['gem_value'] = $valueGem;
			$result[$c]['gem_cut'] = $row_gems['gems_cut'];
			$result[$c]['gem_name'] = $row_gems['gems_names'];
			$result[$c]['gem_color'] = $row_gems['gems_color'];
			$c++;
		}
		return $result;
	}
	public function getDopVC() {
		
		function links($connection, $id, $vc_3dnum, $stockDir) {
			$compl_quer = mysqli_query($connection, " SELECT img_name FROM images WHERE pos_id='$id' AND main='1' ");
			$querN3D = mysqli_query($connection, " SELECT number_3d FROM stock WHERE id='$id' ");
			$n3d_row = mysqli_fetch_assoc($querN3D);
			$compl_row = mysqli_fetch_assoc($compl_quer);

            $file = $n3d_row['number_3d'].'/'.$id.'/images/'.$compl_row['img_name'];
            $fileImg = _stockDIR_HTTP_.$n3d_row['number_3d'].'/'.$id.'/images/'.$compl_row['img_name'];
            if ( !file_exists(_stockDIR_.$file) ) $fileImg = _stockDIR_HTTP_."default.jpg";

			return '<a imgtoshow="'.$fileImg.'" href="index.php?id='.$id.'">'.$vc_3dnum.'</a>';
		}
		function vc_3dnumExpl($connection, $vc_3dnum, $vc_name, $stockDir) {
			$arr = explode('/',$vc_3dnum);
			$quer = mysqli_query($connection, " SELECT id,number_3d,vendor_code FROM stock WHERE model_type='$vc_name' ");
			
			$link  = null;
			
			if ( $quer->num_rows > 0 ) {
				while( $row_vc = mysqli_fetch_assoc($quer) ) {
					
					if ( !empty($row_vc['vendor_code']) ) {
						if ( trim($arr[0]) == $row_vc['vendor_code'] ) {
							$link = links($connection, $row_vc['id'], $vc_3dnum, $stockDir);
							break;
						}
					}
					if ( trim($arr[0]) == $row_vc['number_3d'] ) {
						$link = links($connection, $row_vc['id'], $vc_3dnum, $stockDir);
						break;
					}
					
					if ( isset($arr[1]) ) {
						if ( !empty($row_vc['vendor_code']) ) {
							if ( trim($arr[1]) == $row_vc['vendor_code'] ) {
								$link = links($connection, $row_vc['id'], $vc_3dnum, $stockDir);
								break;
							}
						}
						if ( trim($arr[1]) == $row_vc['number_3d'] ) {
							$link = links($connection, $row_vc['id'], $vc_3dnum, $stockDir);
							break;
						}
					}
				}
			}
			return $link;
		}
		
		$result = array();
		$c = 0;
		while( $row_dop_vc = mysqli_fetch_assoc($this->dopVc_Query) ) {
			
			$linkVCnum = vc_3dnumExpl($this->connection, $row_dop_vc['vc_3dnum'], $row_dop_vc['vc_names'], $this->stockDir );
			$linkVCnum = $linkVCnum ? $linkVCnum : $row_dop_vc['vc_3dnum'];
			
			$result[$c]['vc_num'] = $c+1;
			$result[$c]['vc_names'] = $row_dop_vc['vc_names'];
			$result[$c]['vc_link'] = $linkVCnum;
			$result[$c]['vc_descript'] = $row_dop_vc['descript'];
			$c++;
		}
		return $result;
	}

	public function getRepairs()
    {
        $repairs = [];

        if ( $this->rep_Query )
            while($repRow = mysqli_fetch_assoc($this->rep_Query)) $repairs[] = $repRow;

        return $repairs;
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
        $stats_quer = mysqli_query($this->connection, " SELECT status,name,date FROM statuses WHERE pos_id='{$this->id}' ");

        if ( !mysqli_num_rows($stats_quer) )
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
        return $result;
    }

}
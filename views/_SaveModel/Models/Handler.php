<?php
namespace Views\_SaveModel\Models;

use Views\_Globals\Models\{
    General, PushNotice, User
};
use Views\vendor\core\Files;

/**
 * общий класс, для манипуляций с базой данных MYSQL, и файлами на сервере
 */
class Handler extends General
{
	
	public $id;
    public $number_3d;
	private $vendor_code;
	private $model_type;
	private $model_typeEn;
	private $isEdit;

    public  $date;
	public  $forbiddenSymbols;

	function __construct( $id = false )
	{
		parent::__construct();
		if ( $id ) $this->id = $id;

        $this->date = date("Y-m-d");

		$this->forbiddenSymbols = ['/','\\',"'",'"','?',':','*','|','>','<',',','.'];
	}
	
	public function tities($str='')
    {
        if ( empty($str) ) return '';
        $titi =  htmlentities(strip_tags($str), ENT_QUOTES | ENT_IGNORE);
        return $titi;
    }

	public function setId($id) {
		if ( isset($id) ) $this->id = $id;
	}


    /**
     * @param string $number_3d
     * @return null|string
     * @throws \Exception
     */
    public function setNumber_3d($number_3d='')
    {
		if ( !empty($number_3d) )
		{
			$this->number_3d = $this->add000( $this->checkCyrillic( str_replace($this->forbiddenSymbols,'_',$number_3d) ) );
			return $this->number_3d;
		}

		$query = $this->baseSql(' SELECT max(number_3d) largestNum FROM stock ');
		//if ( isset($query['error']) ) throw new Error("Error finding last 3d number" . $query['error'], 1);
		
		$row = mysqli_fetch_assoc($query);
		$newNum = intval($row['largestNum']);

		return $this->number_3d = "000" . ++$newNum;
	}


	public function setVendor_code($vendor_code) {
		if ( isset($vendor_code) ) $this->vendor_code = $vendor_code;
	}
	public function setModel_type($model_type) {
		if ( isset($model_type) ) $this->model_type = $model_type;
	}
	public function setModel_typeEn($model_type) {
		if ( isset($model_type) ) $this->model_typeEn = $this->translit($model_type);
	}
	public function setDate($date) {
		if ( isset($date) ) $this->date = $date;
	}
	public function setIsEdit($isEdit) {
		if ( isset($isEdit) ) $this->isEdit = $isEdit;
	}
    public function setCollections( array $collections )
    {
        //if ( !is_array($collections) || empty($collections) ) return null;

        $temp = [];
        foreach ( $collections as $key => $array )
        {
            foreach ( $array as $collName )
            {
                $temp[] = htmlentities(trim($collName),ENT_QUOTES);
            }
        }

        return implode(';',$temp);
    }
	
	protected function add000($str)
    {
		$arrChars = preg_split('//u',$str,-1,PREG_SPLIT_NO_EMPTY);
		foreach ( $arrChars as $key=>$value ) {
			if ( $value > 0 ) {
				$output = array_slice($arrChars, $key); // "c", "d", "e"
				
				$str = implode( "", $output );
				$str = "000".$str;
				return $str; 
			}
		}
		return null;
	}
	
	protected function checkCyrillic($number_3d) { // проверка на кирилические символы в номере3д
		$result = $this->translit($number_3d);
		$result = mb_convert_case($result, MB_CASE_UPPER, "UTF-8");
		
		return $result;
	}


    /**
     * новый вариант, проверяет поменялся ли номер 3д
     * @throws \Exception
     */
    public function checkModel3DNum() : string
    {
        $oldN3d = $this->findOne(" SELECT number_3d FROM stock WHERE id='$this->id' ", 'number_3d');

        if ( $oldN3d == $this->number_3d )
        {
            return "";
        } else {
            return $oldN3d;
        }
    }

    /**
     * переносит файлы модели в новую папку, если поменялся номер 3д
     * @param string $oldN3d
     * @return bool
     */
	public function moveModelFiles( string $oldN3d ) : bool
    {
		//$query_oldN3d = mysqli_query($this->connection, " SELECT number_3d FROM stock WHERE id='$this->id' " );
		//$oldN3d = $this->findOne(" SELECT number_3d FROM stock WHERE id='$this->id' ", 'number_3d');
		//$row = mysqli_fetch_assoc($query_oldN3d);

		//if ( $row['number_3d'] != $this->number_3d ) {
		//if ( $oldN3d == $this->number_3d ) return;

        $oldPath = $oldN3d.'/'.$this->id;
		if ( empty($oldN3d) || !file_exists($oldPath) )
		    return false;

        $newN3d = $this->number_3d;

        if ( !file_exists($newN3d) ) mkdir($newN3d, 0777, true);
        if ( !file_exists($newN3d.'/'.$this->id) ) mkdir($newN3d.'/'.$this->id, 0777, true);

        $newPath = $newN3d.'/'.$this->id;

        //chdir(_stockDIR_);
		//debugAjax( getcwd(),' getcwd()');

        $folders = scandir($oldPath);


        for ( $i = 0; $i < count($folders); $i++ ) { // взяли папки Images и Stl если они есть

            if ( $folders[$i] == '.' || $folders[$i] == '..' ) continue;

            $filesToMove = scandir($oldPath.'/'.$folders[$i]); // сканируем каждую папку на предмет картинок или стл в ней

            for ( $j=0; $j < count($filesToMove); $j++ ) {

                if ( $filesToMove[$j] == '.' || $filesToMove[$j] == '..' ) continue;

                $oldCopyPath = $oldPath.'/'.$folders[$i].'/'.$filesToMove[$j];
                $newCopyPath = $newPath.'/'.$folders[$i].'/'.$filesToMove[$j];

                // если в новом пути нет папки Images или Stl, то создадим их
                if ( !file_exists($newPath.'/'.$folders[$i]) ) mkdir($newPath.'/'.$folders[$i], 0777, true);

                // копируем файлы из старого места в новую дир.
                copy( $oldCopyPath, $newCopyPath );
            }
        }
        $this -> rrmdir( $oldPath ); // удаляем все на старом месте
        $oldDirs = scandir($oldN3d);
        $emptyDir = true;
        // если папка, после удаления, осталась пустая - то удаляем и ее
        for ( $i = 0; $i < count($oldDirs); $i++ ) {
            if ( $oldDirs[$i] == '.' || $oldDirs[$i] == '..' ) continue;
            if ( isset($oldDirs[$i]) && !empty($oldDirs[$i]) ) $emptyDir = false;
        }
        if ( $emptyDir ) rmdir($oldN3d);

		return true;
	}

    /**
     * @param string $vendor_code
     * @return bool
     * @throws \Exception
     */
    public function addVCtoComplects( string $vendor_code )
    {
        if ( empty($vendor_code) )
            return false;


        $sql = " SELECT id,vendor_code FROM stock WHERE number_3d='$this->number_3d' AND vendor_code=' ' ";
        if ( $this->id ) $sql .= " AND id<>'$this->id' ";

        $includedModels = $this->findAsArray( $sql );

        if ( $includedModels ) {
            $ids = '';
            foreach ( $includedModels as $model )
            {
                if ( empty($model['vendor_code']) )
                    $ids .= "'" . $model['id'] . "',";
            }
            if ( $ids )
            {
                try {
                    $ids = '(' . trim($ids,',') . ')';
                    $this->baseSql( " UPDATE stock SET vendor_code='$vendor_code' WHERE id IN $ids " );
                } catch (\Exception $e)
                {
                    if ( _DEV_MODE_ ) {
                        $errArrCodes = [
                            'code' => $e->getCode(),
                            'message' => $e->getMessage(),
                        ];
                        exit(json_encode(['error' => $errArrCodes]));
                    } else {
                        exit(json_encode(['error' => ['message'=>'Error in adding vendor code..', 'code'=>500]]));
                    }
                }
            }
        }
        return true;
	}

    /**
     * @param array $labels
     * @return string
     * @throws \Exception
     */
    public function makeLabels($labels=[])
    {
        if (empty($labels)) return '';

        $str_labels = '';
        $labelsOrigin = $this->getStatLabArr('labels');

        foreach ( $labels as $key => $names )
        {
            foreach ( $names as $label )
                foreach ($labelsOrigin ?: [] as $labelOrigin)
                    if ($label === $labelOrigin['name'])
                        $str_labels .= $labelOrigin['name'] . ';';
        }
        return trim($str_labels,';');
    }

    /**
     * @param $statusNew
     * @param string $creator_name
     * @throws \Exception
     */
    public function updateStatus( int $statusNew, $creator_name="" )
	{
		if ( !$statusNew || $statusNew < 0 || $statusNew > 200 )
		    throw new \Exception(SaveModelCodes::message(SaveModelCodes::WRONG_STATUS,true),
                SaveModelCodes::WRONG_STATUS);

        $statusOld = (int)$this->findOne(" SELECT status as s FROM stock WHERE id='$this->id' ", 's' );

		if ( $statusOld === $statusNew ) return;

		$this->baseSql(" UPDATE stock SET status='$statusNew', status_date='$this->date' WHERE id='$this->id' ");

        //04,07,19 - вносим новый статус в таблицу statuses
        if ( empty($creator_name) )
            $creator_name = User::getFIO();

        $statusTemplate = [
            'pos_id' => $this->id,
            'status' => $statusNew,
            'creator_name' => $creator_name,
            'UPdate'       => date("Y-m-d H:i:s"),//$this->date
        ];
        $this->addStatusesTable($statusTemplate);
	}

    /**
     * @param array $statusT
     * @return bool|int
     * @throws \Exception
     */
    public function addStatusesTable( $statusT = [] )
    {
        //04,07,19 - вносим новый статус в таблицу statuses
        if ( empty($statusT) ) return false;

        $pos_id = $statusT['pos_id'];
        $status = $statusT['status'];
        $name = $statusT['creator_name'];
        $date = date("Y-m-d H:i:s");//$statusT['UPdate'];
        $querStr = "INSERT INTO statuses (pos_id,status,name,date) VALUES('$pos_id','$status','$name','$date')";

        //$quer_status =  mysqli_query($this->connection, $querStr );
        return $this->sql($querStr);
	}

    /**
     * добавляет только номер 3д и тип, чтобы получить айди для дальнейших манипуляций
     * @param $number_3d
     * @param $model_type
     * @return bool
     * @throws \Exception
     */
	public function addNewModel( string $number_3d = '', string $model_type = '' )
    {
        if ( empty($number_3d) )
            $number_3d = $this->number_3d;
        if ( empty($model_type) )
            $model_type = $this->model_type;
        if ( empty($number_3d) || empty($model_type) )
            throw new \Exception("Тип и номер 3Д модели должны быть заполнены", 199);

		$addNew = mysqli_query($this->connection, "INSERT INTO stock (number_3d,model_type) VALUES('$number_3d','$model_type') ");
		if ( !$addNew ) {
			printf( "Error_AddModel: %s\n", mysqli_error($this->connection) );
			return false;
		}
		$id = mysqli_insert_id($this->connection);
		$this->setId($id);
		return $this->id;
	}

    /**
     * @param $data
     * @param bool $id
     * @return bool
     * @throws \Exception
     */
    public function updateDataModel($data, $id = false )
    {
		if (!$id) $id = $this->id;

        // в некоторых случаях в стоке обновлять нечего, только статус
		if ( !trim( $data ) )
		    return true;

		$where = " WHERE id='$id' ";
		$queryStr = " UPDATE stock SET ".$data.$where;
		$addEdit = $this->baseSql($queryStr);
		if ( !$addEdit )
		    throw new \Exception("Error updateDataModel() in class ".get_class($this) . " " . mysqli_error($this->connection),197);

		return true;
	}

    /**
     * @param $creator_name
     * @return bool
     * @throws \Exception
     */
    public function updateCreater( string $creator_name ) : bool
    {
        if ( empty($creator_name) ) return false;
        $creator =  $this->findOne(" SELECT creator_name as n FROM stock WHERE id='$this->id'", 'n' );

		if ( empty($creator) )
			return $this->baseSql(" UPDATE stock SET creator_name='$creator_name' WHERE id='$this->id' ");

		return false;
	}

    /**
     * OLD VERSION
     * @param $files
     * @param $imgRows
     * @return mixed
     */
	public function addImageFiles($files, $imgRows)
    {
        $imgCount = count($files['name']?:[]);

        /* для добавления эскиза */
        $c = 0;
        if ( !empty($imgRows[$c]['img_name']) && $imgRows[$c]['sketch'] == 1 )
        {
            $sketchNames = explode('#',$imgRows[$c]['img_name']);
            $num3D = $sketchNames[0];
            $modelID = $sketchNames[1];
            $imgName = $sketchNames[2];

            $pathFrom = _stockDIR_ . $num3D . "/" . $modelID . "/images/" . $imgName;
            if ( file_exists($pathFrom) )
            {
                $pathTo = $this->number_3d.'/'.$this->id.'/images/'.$imgName;
                if ( copy($pathFrom, $pathTo) )
                {
                    $imgRows[$c]['img_name'] = $imgName;
                }
            }
            $c++;
        }
        /* енд для добавления эскиза */


        for ( $i = 0; $i < $imgCount; $i++ )
        {
            $randomString = randomStringChars(8,'en','symbols');
            //если имя есть, это значит что добавили вручную
            if ( !empty( basename($files['name'][$i]) ) )
            {
                $info = new \SplFileInfo($files['name'][$i]);
                $extension = pathinfo($info->getFilename(), PATHINFO_EXTENSION);
                $uploading_img_name = $this->number_3d."_".$randomString.mt_rand(0,98764321).".".$extension;
                $destination = $this->number_3d.'/'.$this->id.'/images/'.$uploading_img_name;
                $tmpName = $files['tmp_name'][$i];

                if ( move_uploaded_file($tmpName, $destination) ) {
                    $imgRows[$c]['img_name'] = $uploading_img_name;
                    $c++;
                } else {
                    exit('Error moving image file '. $uploading_img_name);
                }
            }
        }

		return $imgRows;
	}

    /**
     * для добавления эскиза в комплект
     * @param $newImages
     */
    public function addIncludedSketch( array &$newImages )
    {
        $sketchNames = explode('#',$newImages[0]['img_name']);
        $num3D = $sketchNames[0];
        $modelID = $sketchNames[1];
        $imgName = $sketchNames[2];

        $pathFrom = _stockDIR_ . $num3D . "/" . $modelID . "/images/" . $imgName;
        if ( file_exists($pathFrom) )
        {
            $pathTo = $this->number_3d.'/'.$this->id.'/images/'.$imgName;

            if ( Files::instance()->copy($pathFrom, $pathTo) )
                $newImages[0]['img_name'] = $imgName;
        }
    }

    /**
     * этот метод расчитывает на то что chdir() установлена в Stock :)
     * @param array $fileData
     * @param bool $preview - Флаг для создания превьюшки
     * @return bool|string
     * @throws \Exception
     */
    public function uploadImageFile( array $fileData, bool $preview = false )
    {
        $randomString = randomStringChars(8,'en','symbols');

        if ( $fileData['error'] !== 0 )
            return false;
            //throw new \Exception($fileData['error'],SaveModelCodes::ERROR_UPLOAD_FILE);

        $info = new \SplFileInfo($fileData['name']);
        $extension = pathinfo($info->getFilename(), PATHINFO_EXTENSION);
        $uploading_img_name = $this->number_3d."_".$randomString.mt_rand(0,98764321).".".$extension;
        $d = $this->number_3d.'/'.$this->id.'/images/';
        $tmpName = $fileData['tmp_name'];

        $files = Files::instance();
        if ( $files->upload( $tmpName, $d.$uploading_img_name, ['png','gif','jpg','jpeg'] ) )
        {

            /** оптимизация размера файла */
            ImageConverter::optimizeUpload($d.$uploading_img_name);

            /** Сднлаем превью загруженного файла */
            if ($preview)
                ImageConverter::makePrev($d, $uploading_img_name);

            return $uploading_img_name;
        }

        return false;
    }



	public function updateImageFlags($imgFlags)
	{
	    if ( empty($imgFlags) ) return true;
		$querFlags = mysqli_query($this->connection, " SELECT id FROM images WHERE pos_id='$this->id' ");
		
		if ( $querFlags->num_rows > 0 ) {
			$i = 0;
			while ($img = mysqli_fetch_assoc($querFlags)) {
				$id = $img['id']; // id картинки в которой нужно проапдейтить флажки
				// индексы картинок выставляются от 0 до их кол-ва в базе
				
				$mainImg_bool   = ( $imgFlags[$i] == 1 ) ? 1 : "";
				$onBodyImg_bool = ( $imgFlags[$i] == 2 ) ? 1 : "";
				$sketchImg_bool = ( $imgFlags[$i] == 3 ) ? 1 : "";
				$detailImg_bool = ( $imgFlags[$i] == 4 ) ? 1 : "";
				$schemeImg_bool = ( $imgFlags[$i] == 5 ) ? 1 : "";
				
				// обновляем в базе флажки для старых картинок
				$quer_upd = mysqli_query($this->connection, " UPDATE images SET main='$mainImg_bool',
																				onbody='$onBodyImg_bool',
																				sketch='$sketchImg_bool',
																				detail='$detailImg_bool',
																				scheme='$schemeImg_bool'
																				WHERE id='$id' 
				");
				if ( !$quer_upd ) {
					printf( "Error add imgFlags: %s\n", mysqli_error($this->connection) );
					return false;
				}
				$i++;
			}
		}
		return true;
	}

	public function openZip($path)
    {
        $zip = new \ZipArchive();
        $zip_name = $this->number_3d."-".$this->model_typeEn.".zip";
        $zip->open($path.$zip_name, \ZIPARCHIVE::CREATE);

        return ['zip'=>$zip, 'zipName' => $zip_name];
    }
    public function closeZip( \ZipArchive $zip ) : bool
    {
        if ( method_exists($zip,'close') )
        {
            $zip->close();
            return true;
        }
        return false;
    }

    /**
     * OLD VERSION
     * @param $filesSTL
     * @return bool
     */
	public function addSTL( &$filesSTL )
    {
		$folder = $this->number_3d.'/'.$this->id.'/stl/';
		
		$zip = new \ZipArchive();
		$zip_name = $this->number_3d."-".$this->model_typeEn.".zip";
		$zip->open($folder.$zip_name, \ZIPARCHIVE::CREATE);

		$countSTls = count($filesSTL['name']);
		for ( $i = 0; $i < $countSTls; $i++ ) {
			
			$fileSTL_name = basename($filesSTL['name'][$i]);
			
			if ( !empty($fileSTL_name) ) {
				
				$info = new \SplFileInfo($filesSTL['name'][$i]);
				$extension = pathinfo($info->getFilename(), PATHINFO_EXTENSION);
				
				$uploading_fileSTL_name[$i] = $this->number_3d."-".$this->model_typeEn."-".$i.".".$extension;
				move_uploaded_file($filesSTL['tmp_name'][$i], $folder.$uploading_fileSTL_name[$i]);
				
				$zip->addFile( $folder.$uploading_fileSTL_name[$i], $uploading_fileSTL_name[$i] );
			}

		}
		$zip->close();
		
		for ( $i = 0; $i < count($filesSTL['name']); $i++ ) {
			unlink($folder.$uploading_fileSTL_name[$i]);
		}
		if ( $countSTls ) {
			$quer = mysqli_query($this->connection, " INSERT INTO stl_files (stl_name, pos_id) VALUES ('$zip_name', '$this->id') ");
			if ( !$quer ) {
				printf( "Error Add STL: %s\n", mysqli_error($this->connection) );
				return false;
			}
		}
		return true;
	}

    /**
     * @param array $zipData
     * @param string $path
     * @return bool
     * @throws \Exception
     */
    public function uploadStlFile(array &$zipData, string $path )
    {
        $fileSTL_name = basename($zipData['stl']['name']);

        if ( !empty($fileSTL_name) )
        {
            $randomString = randomStringChars(8,'en','symbols');

            $info = new \SplFileInfo($fileSTL_name);
            $extension = pathinfo($info->getFilename(), PATHINFO_EXTENSION);

            $uploading_fileSTL_name = $this->number_3d."-".$this->model_typeEn."-".$randomString.mt_rand(0,98764321).".".$extension;

            $destination = $path.$uploading_fileSTL_name;
            if ( Files::instance()->upload( $zipData['stl']['tmp_name'], $destination, ['stl','mgx']) )
            {
                if ( $zipData['zip']->addFile( $destination, $uploading_fileSTL_name ) )
                    return $destination;
            }
        }

        return false;
    }

    /**
     * Завершает загрузку Stl файлов
     * @param array $stlFileNames
     * @param array $zipData
     * @throws \Exception
     */
    public function insertStlData( array $stlFileNames, array $zipData )
    {
        $this->closeZip( $zipData['zip'] );

        foreach ( $stlFileNames as $stlFN )
            Files::instance()->delete($stlFN);

        if ( count($stlFileNames) )
        {
            $sql = "INSERT INTO stl_files (stl_name, pos_id) 
                      VALUES ('{$zipData['zipName']}', '$this->id') ";
            if ( $this->sql($sql) === -1 )
                throw new \Exception('Error adding STL files',2);
        }
    }


    /**
     * @param $files3DM
     * @return bool
     * @throws \Exception
     */
    public function add3dm($files3DM)
    {
        $path = $this->number_3d.'/'.$this->id.'/3dm/';
        $zipArch = $this->openZip($path);
        $zip = $zipArch['zip'];

        $fileNames = [];

        for ( $i = 0; $i < count($files3DM['name']); $i++ )
        {
            $fileSTL_name = basename($files3DM['name'][$i]);
            if ( !empty($fileSTL_name) )
            {
                $info = new \SplFileInfo($files3DM['name'][$i]);
                $extension = pathinfo($info->getFilename(), PATHINFO_EXTENSION);

                $fileNames[$i] = $this->number_3d."-".$this->model_typeEn."-".$i.".".$extension;
                move_uploaded_file($files3DM['tmp_name'][$i], $path.$fileNames[$i]);

                $zip->addFile( $path.$fileNames[$i], $fileNames[$i] );
            }
        }
        $zip->close();

        $zipFile = $path.$zipArch['zipName'];

        if ( file_exists( $zipFile ) )
        {
            $zipArchSize = filesize( $zipFile );
            $query = $this->baseSql(" INSERT INTO rhino_files (name, size, pos_id) VALUES ('{$zipArch['zipName']}', '$zipArchSize','$this->id') ");
            if ( !$query ) throw new \Exception(__METHOD__ . 'Error :' . mysqli_error($this->connection),412);
        }

        // иногда выбивает ошибку: unlink(...) Resource temporarily unavailable
        // поставим задержку что бы успел обработать файл
        sleep(1);
        foreach ( $fileNames as $fileName )
            if ( file_exists( $path.$fileName ) ) unlink($path.$fileName);

        return true;
    }



	public function addAi( &$filesAi ) {
		$folder = $this->number_3d.'/'.$this->id.'/ai/';
		
		$zip = new \ZipArchive();
		$zip_name = $this->number_3d."-".$this->model_typeEn.".zip";
		$zip->open($folder.$zip_name, \ZIPARCHIVE::CREATE);
		$countAis = count($filesAi['name']);
		for ( $i = 0; $i < $countAis; $i++ ) {
			
			$fileAi_name = basename($filesAi['name'][$i]);
			
			if ( !empty($fileAi_name) ) {
				
				$info = new \SplFileInfo($filesAi['name'][$i]);
				$extension = pathinfo($info->getFilename(), PATHINFO_EXTENSION);
				
				$uploading_fileAi_name[$i] = $this->number_3d."-".$this->model_typeEn."-".$i.".".$extension;
				
				$size = filesize($filesAi['tmp_name'][$i]);
				
				move_uploaded_file($filesAi['tmp_name'][$i], $folder.$uploading_fileAi_name[$i]);
				
				$zip->addFile( $folder.$uploading_fileAi_name[$i], $uploading_fileAi_name[$i] );
			}

		}
		$zip->close();
		
		for ( $i = 0; $i < count($filesAi['name']); $i++ ) {
			unlink($folder.$uploading_fileAi_name[$i]);
		}
		
		if ( $countAis ) {
			$quer = mysqli_query($this->connection, " INSERT INTO ai_files (name,size,pos_id) VALUES ('$zip_name', '$size', '$this->id') ");
			if ( !$quer ) {
				printf( "Error Add Ai: %s\n", mysqli_error($this->connection) );
				return false;
			}
		}
		return true;
	}
	
	public function addGems( &$gems ) {
		if ( $this->isEdit === true ) mysqli_query($this->connection, " DELETE FROM gems WHERE pos_id='$this->id' ");
		
		for ( $i = 0; $i < count($gems['name']?:[]); $i++ ) {
			
			$gemsName  = trim($gems['name'][$i]);
			$gemsCut   = trim($gems['cut'][$i]);
			$gemsVal   = trim($gems['val'][$i]);
			$gemsDiam  = trim($gems['diam'][$i]);
			$gemsColor = trim($gems['color'][$i]);

			if ( $gemsName == "" ) continue;
			
			$quer_gem = mysqli_query($this->connection, " INSERT INTO gems (gems_names, 
																	 gems_cut,
																	 value,
																	 gems_sizes,
																	 gems_color,
																	 pos_id
																	) 
															 VALUES ('$gemsName',
																	 '$gemsCut',
																	 '$gemsVal',
																	 '$gemsDiam',
																	 '$gemsColor',
																	 '$this->id'
																	)
			");
			if ( !$quer_gem ) {
				printf( "Error Add Gems: %s\n", mysqli_error($this->connection) );
				return false;
			}
		}
		return true;
	}
	
	public function addDopVC( &$vc ) {
		if ( $this->isEdit === true ) mysqli_query($this->connection, " DELETE FROM vc_links WHERE pos_id='$this->id' ");
		
		for ( $i = 0; $i < count($_POST['dop_vc_name_']?:[]); $i++ ) {
		
			$dop_vc_name = trim($vc['dop_vc_name'][$i]);
			$num3d_vc =  trim($vc['num3d_vc'][$i]);
			$descr_dopvc =  trim($vc['descr_dopvc'][$i]);

			if ( $dop_vc_name == "" ) continue;
			
			$quer_dop_vc = mysqli_query($this->connection, " INSERT INTO vc_links (vc_names, 
                                                                vc_3dnum,
                                                                descript,
                                                                pos_id
                                                               ) 
                                                VALUES ('$dop_vc_name',
                                                                '$num3d_vc',
                                                                '$descr_dopvc',
                                                                '$this->id'
                                                               ) 
			");
			if ( !$quer_dop_vc ) {
				printf( "Error Add dop_vc: %s\n", mysqli_error($this->connection) );
				return false;
			}
		}
		return true;
	}


	public function addNotes( $notes )
    {
        if ( !is_array($notes) || empty($notes) ) return [];
        $notes = $this->parseRecords($notes);

        $deletions = [];
        $updates = [];
        $insertions = [];

        foreach ( $notes as $note )
        {
            $noteID = (int)$note['id'];
            $noteText = trim($note['text']);

            if ( $noteID > 0 )
            {
                $repQuery = mysqli_query($this->connection, " SELECT COUNT(1) FROM description WHERE id='$noteID' ");

                if ( $repQuery->num_rows && ( empty($noteText) || $noteText == -1) )
                {
                    // кандидат на удаление
                    $deletions[] = $note;
                } elseif ($repQuery->num_rows)
                {
                    $updates[] = $note;
                }
            }
            if ( $noteID === 0 )
            {
                $insertions[] = $note;
                continue;
            }
        }

        //        debug($deletions,'$deletions');
        //        debug($updates,'$updates');
        //        debug($insertions,'$insertions');

        $result = [];
        if ( !empty($deletions) )
        {
            $dellIds = '(';
            foreach ( $deletions as $deletion ) $dellIds .= $deletion['id'] . ',';
            $dellIds = trim($dellIds,',') . ')';

            $dellQuery = mysqli_query($this->connection, " DELETE FROM description WHERE id in $dellIds ");
            if ($dellQuery) {
                $result['deletions'] = $dellIds . ' - deleted.';
            } else {
                printf( "Error Delete notes: %s\n", mysqli_error($this->connection) );
                $result['deletions'] = 'error';
            }
        }
        if ( !empty($updates) )
        {
            foreach ( $updates as $update )
            {
                $id = $update['id'];
                $text = $update['text'];

                $queryStr = " UPDATE description SET text='$text' WHERE id='$id' ";

                $updQuery = mysqli_query($this->connection, $queryStr);
                if ($updQuery) {
                    $result['updates'][] = $id . ' - success.';
                } else {
                    printf( "Error Update notes: %s\n", mysqli_error($this->connection) );
                    $result['updates'][] = $id . ' - update error!';
                }
            }
        }
        if ( !empty($insertions) )
        {
            foreach ( $insertions as $insertion )
            {
                $num = $insertion['num'];
                $text = $insertion['text'];
                $userID = $_SESSION['user']['id'];
                $insertQuery = mysqli_query($this->connection, " INSERT INTO description (num, text, userID, date, pos_id) 
		                                                                 VALUES ('$num','$text','$userID','$this->date','$this->id') ");
                if ($insertQuery) {
                    $result['insertions'][] =  $this->connection->insert_id . ' - success.';
                } else {
                    printf( "Error Insert repairs: %s\n", mysqli_error($this->connection) );
                    $result['insertions'][] = ' Insert error!';
                }
            }
        }

        return $result;
    }

	public function parseRecords($records)
    {
        if ( !is_array($records) ) return [];
        $parsedRecords = [];

        foreach ( $records as $field => $record )
        {
            foreach ( $record as $key => $value )
            {
                $parsedRecords[$key][$field] = $value;
            }
        }
        return $parsedRecords;
    }
	public function addRepairs( $repairs )
    {
        if ( !is_array($repairs) || empty($repairs) ) return [];
        $repairs = $this->parseRecords($repairs);
        //$repairsJew = $this->parseRepairs(isset($repairs['jew'])?$repairs['jew']:[]);
        //$repairs = array_merge($repairs3D, $repairsJew);

        //debug($repairs,'$repairs');

        $deletions = [];
        $updates = [];
        $insertions = [];

        foreach ( $repairs as $repair )
        {
            $repID = (int)$repair['id'];
            $repDescr = trim($repair['description']);

            if ( $repID > 0 )
            {
                $repQuery = mysqli_query($this->connection, " SELECT COUNT(1) FROM repairs WHERE id='$repID' ");
                if ( $repQuery->num_rows && (empty($repDescr) || $repDescr == -1) ) { // кандидат на удаление
                    $deletions[] = $repair;
                } elseif ($repQuery->num_rows)
                {
                    $updates[] = $repair;
                }
            }
            if ( $repID === 0 )
            {
                $insertions[] = $repair;
                continue;
            }
        }

	//        debug($deletions,'$deletions');
	//        debug($updates,'$updates');
	//        debug($insertions,'$insertions');

        $result = [];
        if ( !empty($deletions) )
        {
            $dellIds = '(';
            foreach ( $deletions as $deletion ) $dellIds .= $deletion['id'] . ',';
            $dellIds = trim($dellIds,',') . ')';

            $dellQuery = mysqli_query($this->connection, " DELETE FROM repairs WHERE id in $dellIds ");
            if ($dellQuery) {
                $result['deletions'] = $dellIds . ' - deleted.';
            } else {
                printf( "Error Delete repairs: %s\n", mysqli_error($this->connection) );
                $result['deletions'] = 'error';
            }
        }
        if ( !empty($updates) )
        {
            foreach ( $updates as $update )
            {
                $id = $update['id'];
                $description = $update['description'];
                $which = $update['which'];
                $cost = '';
                if ( isset($update['cost']) ) $cost = ", cost='{$update['cost']}'";

                $queryStr = " UPDATE repairs SET repair_descr='$description', which='$which' $cost WHERE id='$id' ";

                $updQuery = mysqli_query($this->connection, $queryStr);
                if ($updQuery) {
                    $result['updates'][] = $id . ' - success.';
                } else {
                    printf( "Error Update repairs: %s\n", mysqli_error($this->connection) );
                    $result['updates'][] = $id . ' - update error!';
                }
            }
        }
        if ( !empty($insertions) )
        {
            foreach ( $insertions as $insertion )
            {
                $num = $insertion['num'];
                $description = $insertion['description'];
                $cost = $insertion['cost'];
                $which = $insertion['which'];
                $insertQuery = mysqli_query($this->connection, " INSERT INTO repairs (rep_num, repair_descr, cost, which, date, pos_id) 
		                                                                 VALUES ('$num','$description','$cost','$which','$this->date','$this->id') ");
                if ($insertQuery) {
                    $result['insertions'][] = $id . ' - success.';
                } else {
                    printf( "Error Insert repairs: %s\n", mysqli_error($this->connection) );
                    $result['insertions'][] = ' Insert error!';
                }
            }
        }

        return $result;
	}




    /**
     * @return array
     * @throws \Exception
     */
    public function deleteModel()
    {
        chdir(_stockDIR_);

        $row = $this->findOne(" SELECT number_3d,vendor_code,model_type,status FROM stock WHERE id='$this->id' ");

		$result = [
		    'success' => 0,
            'error'=>'',
            'errorNo'=> 0,
		    'number_3d'   => $row['number_3d'],
		    'vendor_code' => $row['vendor_code'],
		    'model_type'  => $row['model_type'],
		    'status'  => $row['status'],
            'dell' => $row['number_3d']." / ".$row['vendor_code']." - ".$row['model_type'],
        ];

        try {
            if ( $this->deleteFromTable('stock','id',$this->id) )
            {
                $this->deleteFromTable('metal_covering','id',$this->id);
                mysqli_query($this->connection, " DELETE FROM images         WHERE pos_id='$this->id' ");
                mysqli_query($this->connection, " DELETE FROM gems           WHERE pos_id='$this->id' ");
                mysqli_query($this->connection, " DELETE FROM vc_links       WHERE pos_id='$this->id' ");
                mysqli_query($this->connection, " DELETE FROM statuses       WHERE pos_id='$this->id' ");
                mysqli_query($this->connection, " DELETE FROM ai_files       WHERE pos_id='$this->id' ");
                mysqli_query($this->connection, " DELETE FROM stl_files      WHERE pos_id='$this->id' ");
                mysqli_query($this->connection, " DELETE FROM rhino_files    WHERE pos_id='$this->id' ");
                mysqli_query($this->connection, " DELETE FROM repairs        WHERE pos_id='$this->id' ");
                mysqli_query($this->connection, " DELETE FROM pushnotice     WHERE pos_id='$this->id' ");
                mysqli_query($this->connection, " DELETE FROM description    WHERE pos_id='$this->id' ");
                mysqli_query($this->connection, " DELETE FROM model_prices   WHERE pos_id='$this->id' AND paid='0'"); // удалим только не оплаченные
                $result['success'] = 1;
            } else {
                $result['error'] = "something wrong";
                return $result;
            }
        } catch (\Exception | \Error $e) {
            $result['error'] = $e->getMessage();
            $result['errorNo'] = $e->getCode();
            return $result;
        }

		$path = $row['number_3d'].'/'.$this->id;
        if (file_exists($path))
        {
            try {
                $this->rrmdir($path);

                $files = [];
                if ( file_exists($row['number_3d']) ) $files = scandir( $row['number_3d'] );
                $is_empty = true;

                for ( $i = 0; $i < count($files); $i++ ) {
                    if ( $files[$i] == '.' || $files[$i] == '..' ) continue;

                    if ( isset($files[$i]) && !empty($files[$i]) ) $is_empty = false;
                }

                if ( $is_empty && file_exists($row['number_3d']) )
                    rmdir( $row['number_3d'] );

            } catch (\Exception | \Error $e) {
                $result['error'] = $e->getMessage();
                $result['errorNo'] = $e->getCode();
                return $result;
            }
        }

		return $result;
	}

    /**
     * @param $fileName string
     * @param $fileType string
     * @return bool
     * @throws \Exception
     */
    public function deleteFile( string $fileName, string $fileType )
    {
        if ( empty($fileName) || empty($fileType) )
            throw new \Exception('Имя и тип файла должен быть не пусты.',444);

        if ( !User::permission('files') ) return false;
        switch ($fileType)
        {
            case "stl":   if ( !User::permission('stl') )      return false; break;
            case "image": if ( !User::permission('images') )   return false; break;
            case "ai":    if ( !User::permission('ai') )       return false; break;
            case "3dm":   if ( !User::permission('rhino3dm') ) return false; break;
        }

        $configs = [
            'stl' => [
                'table' => 'stl_files',
                'field' => 'stl_name',
                'folder' => 'stl',
                'text' => 'Stl файлы ',
            ],
            'image' => [
                'table' => 'images',
                'field' => 'img_name',
                'folder' => 'images',
                'text' => 'Картинка ',
            ],
            'ai' => [
                'table' => 'ai_files',
                'field' => 'name',
                'folder' => 'ai',
                'text' => 'Файлы накладки ',
            ],
            '3dm' => [
                'table' => 'rhino_files',
                'field' => 'name',
                'folder' => '3dm',
                'text' => '3dm файлы ',
            ],
        ];
        if ( !array_key_exists($fileType, $configs) ) throw new \Exception('Передан не известный тип файла.',444);
        $config = $configs[$fileType];

        $modelData = $this->findOne(" SELECT number_3d FROM stock WHERE id='$this->id' ");

        $dellQuery = mysqli_query($this->connection, " DELETE FROM {$config['table']} WHERE {$config['field']}='$fileName' ");
        if ( !$dellQuery ) throw new \Exception(__METHOD__.' Error '. mysqli_error($this->connection));

        $basePath = _stockDIR_ . $modelData['number_3d']."/".$this->id."/{$config['folder']}/";
        $filePath = $basePath.$fileName;

        $f = Files::instance();
        $deletedOrigin = $f->delete($filePath);

        /** Если это картинка, топроверим наличие превью */
        $deletedPrev = false;
        if ( $deletedOrigin && $fileType === 'image' )
        {
            $ext = pathinfo($fileName, PATHINFO_EXTENSION);
            $imgBaseName = pathinfo($fileName, PATHINFO_FILENAME); // вернет имя файла без расширения
            $imgName = $imgBaseName . ImageConverter::getImgPrevPostfix() . "." . $ext;

            $deletedPrev = $f->delete($basePath.$imgName);
        }

        if ( $deletedOrigin && $deletedPrev )
            return $config['text'] . " и превью - ";

        if ( $deletedOrigin )
            return $config['text'];

        return false;
    }

	public function deletePDF($pdfName) {
		
		$name = _rootDIR_."Pdfs/".$pdfName;
		if ( file_exists($name) ) 
		{
			unlink($name);
			return true;
		}
		
		return false;
	}
	
	
	public function setRepairPaid($repairID, $repairCost)
    {
        $query = mysqli_query($this->connection, " UPDATE repairs SET paid=1, cost='$repairCost' WHERE id='$repairID' ");
        if ($query) return true;

        return 'Error in setRepairPaid() ' . mysqli_error($this->connection);
    }

    /**
     * СТАРЫЙ
     * Формируте массив строк для пакетной вставки/обновления строк
     * в дополнительные таблицы
     * @param $data
     * @param $stockID
     * @param $tableName
     * @return array|bool
     * @throws \Exception
     */
    public function makeBatchInsertRowOLD($data, $stockID, $tableName)
    {
        if ( !is_array($data) || empty($data) ) return false;
        $materials = [];

        $tableSchema = $this->getTableSchema($tableName);

        foreach ( $data as $mats )
        {
            for( $i = 0; $i < count($mats); $i++ )
            {
                $materials[$i][] = $mats[$i];
            }
        }

        $removeRows = [];
        foreach ( $materials as $key => &$material )
        {
            // проверка на пустые строки
            $toDell = true;
            $toRemove = false;
            $materialAssoc = [];
            //$material - массив строк с полями
            foreach ( $material as $iter => &$mat )
            {
                // когда хоть одно поле заполнено - оставим для внесения в табл.
                if ( !empty($mat) ) {
                    $toDell = false;
                }
                // кандидат на удаление из Таблицы
                if ( (int)$mat === -1 ) {
                    $toRemove = true;
                    break;
                }
                $materialAssoc[$tableSchema[$iter]] = $this->tities($mat);
            }
            $materialAssoc[$tableSchema[++$iter]] = $stockID; // в конец добавим pos_id

            if ( $toDell )
            {
                unset($materials[$key]);
                continue;
            }
            if ( $toRemove )
            {
                $removeRows[] = $material[0];
                //$removeRows['table'] = $name;
                unset($materials[$key]);
                continue;
            }

            $materials[$key] = $materialAssoc;
            
        }
        debug(['insertUpdate'=>$materials, 'remove'=>$removeRows],'old',1,1);
        return ['insertUpdate'=>$materials, 'remove'=>$removeRows];
    }

    /**
     * @param $data
     * @param $stockID
     * @param $tableName
     * @return bool | array
     * @throws \Exception
     */
    public function makeBatchInsertRow($data, $stockID, $tableName)
    {
        if ( !is_array($data) || empty($data) ) return false;
        $dataRows = [];
        $insertRows = [];
        $removeRows = [];

        $data = $this->parseRecords($data);
		
		//debugAjax($stockID,'stockID');
		//debugAjax($data,'parseRecords',END_AB);

        $tSOrigin = $this->getTableSchema($tableName);
        $tableSchema = [];
        foreach ( $tSOrigin as $cn ) $tableSchema[$cn] = '';

        $i = 0;
        foreach ( $data as $dR )
        {
            foreach ( $tableSchema as $columnName => $val )
            {
                $dataRows[$i][$columnName] = '';
                if ( array_key_exists($columnName,$dR) )
                    $dataRows[$i][$columnName] = $this->tities($dR[$columnName]);

            }
            $i++;
        }

        foreach ( $dataRows as $key => $dataRow )
        {
            $emptyFields = true;
            $toRemove = false;
            foreach ( $dataRow as $value )
            {
                // когда хоть одно поле заполнено - оставим для внесения в табл.
                if ( !empty($value) ) {
                    $emptyFields = false;
                }
                // кандидат на удаление из Таблицы
                if ( (int)$value === -1 ) {
                    $toRemove = true;
                    break;
                }
            }

            if ( $toRemove )
            {
				$dataRow[end($tSOrigin)] = $stockID; // в конец добавим pos_id
				$removeRows[] = $dataRow;
                continue;
            }
            if ( $emptyFields )
            {
                unset($dataRows[$key]);
                continue;
            }

            $dataRow[end($tSOrigin)] = $stockID; // в конец добавим pos_id
            $insertRows[] = $dataRow;
        }

        return ['insertUpdate'=>$insertRows, 'remove'=>$removeRows];
    }

    /**
     * Формируте массив строк для пакетной вставки картинок
     * @param $data
     * @return array|bool
     * @throws \Exception
     */
    public function makeBatchImgInsertRow($data)
    {
        $newImgRows = [];
        $imgRows = [];
        if ( !is_array($data) || empty($data) ) return false;

        /* для добавления эскиза */
        $sketchImgName = '';
        if ( isset($data['img_name']['sketch']) ) $sketchImgName = $data['img_name']['sketch'];


        foreach ( $data as $mats )
        {
            for( $i = 0; $i < count($mats); $i++ )
            {
                $imgRows[$i][] = $mats[$i];
            }
        }
        //debug($imgRows,'$imgRow');
        $images = $this->findAsArray("SELECT * FROM images WHERE pos_id='$this->id' ");
        //debug($images,'Images');

        foreach( $imgRows as $imgRowKey => &$imgRow )
        {
            $imgId = (int)$imgRow[0];
            $imgFor = (int)$imgRow[1];
            $isNEWImage = true;
            $modifiedImgRow = [];
            foreach( $images as $image )
            {
                if ( $imgId === (int)$image['id'] )
                {
                    $modifiedImgRow = $image;
                    $isNEWImage = false;
                    break;
                }
            }
            //
            if ( $isNEWImage )
            {
                $modifiedImgRow = ['id'=>'','img_name'=>$sketchImgName,'main'=>'','onbody'=>'','sketch'=>'','detail'=>'','scheme'=>'','pos_id'=>$this->id];
            }
            // уже новая $imgRow
            // сформируем массив флажков
            foreach( $modifiedImgRow as $keyCol => &$column )
            {
                switch ($keyCol)
                {
                    case 'main':
                        $column = ($imgFor == 22)  ? 1 : "";
                        break;
                    case 'onbody':
                        $column = ($imgFor == 23)  ? 1 : "";
                        break;
                    case 'sketch':
                        $column = ($imgFor == 24)  ? 1 : "";
                        break;
                    case 'detail':
                        $column = ($imgFor == 25)  ? 1 : "";
                        break;
                    case 'scheme':
                        $column = ($imgFor == 26)  ? 1 : "";
                        break;
                }
            }
            if ( $isNEWImage )
            {
                $newImgRows[] = $modifiedImgRow;
                unset($imgRows[$imgRowKey]);
            } else {
                $imgRow = $modifiedImgRow;
            }
        }

        return ['newImages'=>$newImgRows,'updateImages'=>$imgRows];
    }

    /**
     * @param string $surname
     * @return int
     * @throws \Exception
     */
    public function getUserIDFromSurname( string $surname )
    {
        $userID = null;
        foreach ( $this->getUsers() as $user )
        {
            if ( mb_stripos( $user['fio'], $surname ) !== false )
            {
                $userID = $user['id'];
                break;
            }
        }
        return $userID;
    }

}
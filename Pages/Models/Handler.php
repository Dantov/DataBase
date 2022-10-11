<?php
namespace models;

use soffit\{Files, db\Table};

/**
 * общий класс, для сохранения моделей в базу данных MYSQL, и операциями с файлами на сервере
 */
class Handler extends General
{
	private string $vendor_code = '';
	private string $model_type = '';
	private string $model_typeEn = '';
	private $isEdit;

    /** Tables */
    //protected Table $stock;
    //protected Table $images;

    public int $id = 0;
    public string $number_3d = '';
    public string $date = '';
	public array $forbiddenSymbols = [];

	function __construct( int $id = 0 )
	{
		parent::__construct();
		if ( $id > 0 && $id < PHP_INT_MAX ) 
            $this->id = $id;

        $this->getUser();

        $this->date = date("Y-m-d");
		$this->forbiddenSymbols = ['/','\\',"'",'"','?',':','>','<',',','.'];

        $this->registerTable(['stock','images']);
	}
	
	public function tities( string $str ) : string
    {
        $titi = htmlentities(strip_tags($str), ENT_QUOTES | ENT_IGNORE);
        return $titi;
    }

	public function setId( int $id ) : int 
    {
        if ( $id <= 0 || $id > PHP_INT_MAX )
         throw new \Exception("Wrong id comes in " . __METHOD__ );
        
        return $this->id = $id;
	}


    /**
     * @param string $number_3d
     * @return null|string
     * @throws \Exception
     */
    public function setNumber_3d( string $number_3d='' ) : string
    {
		if ( !empty($number_3d) )
		{
			$this->number_3d = $this->add000( $this->checkCyrillic( str_replace($this->forbiddenSymbols,'_',$number_3d) ) );
			return $this->number_3d;
		}
        $max = function() {
            return ['fieldNames'=>['a'=>'number_3d'], 'function'=>"max(%a%)"];
        };
		$lastNum = $this->STOCK->select(['largestNum'=>$max])->asOne('largestNum')->exe();
		$newNum = intval($lastNum);

		return $this->number_3d = "000" . ++$newNum;
	}

	public function setVendor_code( string $vendor_code ) : void {
		if ( !empty($vendor_code) ) 
            $this->vendor_code = htmlentities(strip_tags($vendor_code), ENT_QUOTES | ENT_IGNORE);
	}
	public function setModel_type( string $model_type ) : void {
		if ( !empty($model_type) ) 
            $this->model_type = htmlentities(strip_tags($model_type), ENT_QUOTES | ENT_IGNORE);
	}
	public function setModel_typeEn( string $model_type) : void {
		if ( !empty($model_type) ) 
        {
            $model_type= htmlentities(strip_tags($model_type), ENT_QUOTES | ENT_IGNORE);
            $this->model_typeEn = $this->translit($model_type);
        }
	}
	public function setDate( string $date ) : void {
		if ( !empty($date) ) 
            $this->date = htmlentities(strip_tags($date), ENT_QUOTES | ENT_IGNORE);
	}
	public function setIsEdit( $isEdit ) {
		if ( !empty($isEdit) ) $this->isEdit = $isEdit;
	}
    public function setCollections( array $collections ) : string
    {
        $temp = [];
        foreach ( $collections as $key => $array )
        {
            foreach ( $array as $collName )
                $temp[] = htmlentities(trim($collName),ENT_QUOTES);
        }

        return implode(';',$temp);
    }
	
	protected function add000( string $str ) : string
    {
        if ( empty($str) ) 
            throw new \Exception( "Input str is empty in " . __METHOD__, 222 );
        try {
            $arrChars = preg_split('//u',$str,-1,PREG_SPLIT_NO_EMPTY);
            foreach ( $arrChars as $key=>$value ) 
            {
                if ( $value > 0 ) 
                {
                    $output = array_slice($arrChars, $key); // "c", "d", "e"
                    
                    $str = implode( "", $output );
                    $str = "000".$str;
                    return $str; 
                }
            }
        } catch ( Exception | Error $e) {
            throw new \Exception( $e->getMessage(), $e->getCode() );
        }
		
		return '';
	}
	
    /**
     * проверка на кирилические символы в номере3д
     */
	protected function checkCyrillic( string $number_3d ) : string 
    {  
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
        $oldN3d = $this->STOCK->select(['number_3d'])->where('id','=',$this->id)->asOne('number_3d')->exe();

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
        $oldPath = $oldN3d.'/'.$this->id;
		if ( empty($oldN3d) || !file_exists($oldPath) )
		    return false;

        $newN3d = $this->number_3d;

        if ( !file_exists($newN3d) ) mkdir($newN3d, 0777, true);
        if ( !file_exists($newN3d.'/'.$this->id) ) mkdir($newN3d.'/'.$this->id, 0777, true);

        $newPath = $newN3d.'/'.$this->id;

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
    public function addVCtoComplects( string $vendor_code ) : bool
    {
        if ( empty($vendor_code) )
            return false;

        $sql = " SELECT id,vendor_code FROM stock WHERE number_3d='$this->number_3d' AND vendor_code=' ' ";
        if ( $this->id ) $sql .= " AND id<>'$this->id' ";

        $includedModels = $this->findAsArray( $sql );

        if ( $includedModels ) {
            $ids = '';
            $f = false;
            foreach ( $includedModels as $model )
            {
                if ( empty($model['vendor_code']) ) {
                    $ids .= "'" . $model['id'] . "',";
                    $f = true;
                }
            }
            $ids = '(' . trim($ids,',') . ')';
            if ( $f )
            {
                try { 
                    $this->update("stock", ['vendor_code'=>$vendor_code], ['id', 'IN', $ids]);
                    if ( $this->affectedRows ) return true;
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
        return false;
	}

    /**
     * @param array $labels
     * @return string
     * @throws \Exception
     */
    public function makeLabels( array $labels ) : string
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
    public function updateStatus( int $statusNew, string $creator_name="" ) : bool
	{
		if ( !$statusNew || $statusNew < 0 || $statusNew > 200 )
		    throw new \Exception(SaveModelCodes::message(SaveModelCodes::WRONG_STATUS,true),
                SaveModelCodes::WRONG_STATUS);

        $statusOld = (int)$this->findOne(" SELECT status as s FROM stock WHERE id='$this->id' ", 's' );

		if ( $statusOld === $statusNew ) 
            return false;

		//$this->baseSql(" UPDATE stock SET status='$statusNew', status_date='$this->date' WHERE id='$this->id' ");
        $this->update("stock", ['status'=>$statusNew, 'status_date'=>$this->date], ['id','=',$this->id]);
        if ( $this->affectedRows )
        {
            //04,07,19 - вносим новый статус в таблицу statuses
            if ( empty($creator_name) )
                $creator_name = User::getFIO();

            $statusTemplate = [
                'pos_id' => $this->id,
                'status' => $statusNew,
                'creator_name' => $creator_name,
                'UPdate'       => date("Y-m-d H:i:s"),//$this->date
            ];
            return $this->addStatusesTable($statusTemplate);    
        }
        return false;
	}

    /**
     * добавляет только номер 3д и тип, чтобы получить stock ID для дальнейших манипуляций
     * @param $number_3d
     * @param $model_type
     * @return bool
     * @throws \Exception
     */
	public function addNewModel( string $number_3d, string $model_type ) : int
    {
        if ( empty($number_3d) )
            $number_3d = $this->number_3d;
        if ( empty($model_type) )
            $model_type = $this->model_type;
        if ( empty($number_3d) || empty($model_type) )
            throw new \Exception("Тип и номер 3Д модели должны быть заполнены", 199);

		//$addNew = mysqli_query($this->connection, "INSERT INTO stock (number_3d,model_type) VALUES('$number_3d','$model_type') ");
        $this->insert("stock", ['number_3d'=>$number_3d,'model_type'=>$model_type]);
		if ( !$this->affectedRows ) {
            throw new \Exception("Error adding new model in " . __METHOD__ , 199);
		}
		return $this->setId( $this->lastInsertID );
	}

    /**
     * @param $data
     * @param bool $id
     * @return bool
     * @throws \Exception
     */
    public function updateDataModel( string $data, int $id = 0 ) : bool
    {
		if ( $id <= 0 || $id > PHP_INT_MAX ) 
            $id = $this->id;

        // в некоторых случаях в стоке обновлять нечего, только статус
		if (!trim( $data ))
		    return false;

		$where = " WHERE id='$id' ";
		$queryStr = " UPDATE stock SET ".$data.$where;
		//$addEdit = $this->baseSql($queryStr);
        $addEdit = $this->sql($queryStr);
		if ( $this->affectedRows ) {
		    //throw new \Exception("Error update Data Model in: " . __METHOD__ , 197);
        //} else {
            return true;
        }
		return false;
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

		if ( empty($creator) ) {
            //return $this->baseSql(" UPDATE stock SET creator_name='$creator_name' WHERE id='$this->id' ");
            $upd = $this->update("stock", ['creator_name'=>$creator_name], ['id'=>$this->id]);
            if ( $this->affectedRows ) {
                return true;
            } else {
                throw new \Exception("Error update Data Model in: " . __METHOD__ , 198);
            }   
        }
		return false;
	}

    /**
     * OLD VERSION
     * @param $files
     * @param $imgRows
     * @return mixed
     */
	public function addImageFiles(array $files, array $imgRows)
    {
        $imgCount = count($files['name']??[]);

        /* для добавления эскиза */
        $c = 0;
        if ( !empty($imgRows[$c]['img_name']) && $imgRows[$c]['sketch'] == 1 )
        {
            $sketchNames = explode('#',$imgRows[$c]['img_name']);
            $num3D   = $sketchNames[0];
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
    public function addIncludedSketch( array &$newImages ) : bool
    {
        $sketchNames = explode('#',$newImages[0]['img_name']);
        $num3D   = $sketchNames[0];
        $modelID = $sketchNames[1];
        $imgName = $sketchNames[2];

        $pathFrom = _stockDIR_ . $num3D . "/" . $modelID . "/images/" . $imgName;
        if ( file_exists($pathFrom) )
        {
            $pathTo = $this->number_3d.'/'.$this->id.'/images/'.$imgName;

            if ( Files::instance()->copy($pathFrom, $pathTo) ) {
                $newImages[0]['img_name'] = $imgName;
                return true;
            } else {
                throw new \Exception("Error to copy file in " . __METHOD__,222);
            }
        }
        return false;
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



	public function updateImageFlags( array $imgFlags ) : bool
	{
	    if ( empty($imgFlags) ) return false;

        $querFlags = $this->IMAGES->select(['id','img_name'])->where('pos_id','=',$this->id)->exe();
		
		if ( $this->IMAGES->numRows > 0 ) 
        {
			$i = 0;
            $upd = [];
            foreach ( $querFlags as $img ) 
            {
                if ( !isset($imgFlags[$i]) )
                    continue;

				$id = $img['id']; // id картинки в которой нужно проапдейтить флажки

				// индексы картинок выставляются от 0 до их кол-ва в базе
				$mainImg_bool   = ( $imgFlags[$i] == 1 ) ? 1 : "";
				$onBodyImg_bool = ( $imgFlags[$i] == 2 ) ? 1 : "";
				$sketchImg_bool = ( $imgFlags[$i] == 3 ) ? 1 : "";
				$detailImg_bool = ( $imgFlags[$i] == 4 ) ? 1 : "";
				$schemeImg_bool = ( $imgFlags[$i] == 5 ) ? 1 : "";
				
				// обновляем в базе флажки для старых картинок
                $upd[] = [
                    'id' => $id,
                    'img_name' => $img['img_name'],
                    'main'  =>$mainImg_bool,
                    'onbody'=>$onBodyImg_bool,
                    'sketch'=>$sketchImg_bool,
                    'detail'=>$detailImg_bool,
                    'scheme'=>$schemeImg_bool,
                    'pos_id' => $this->id
                ];
				$i++;
			}
            $this->IMAGES->insertUpdateRows($upd,"images");
            if ( $this->IMAGES->affectedRows ) {
                    return true;
            }
		}

		return false;
	}

	public function openZip( string $path ) : bool
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
     * @return bool|string
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

        if ( count($stlFileNames) )
        {
            $this->insert("stl_files", ['stl_name'=>$zipData['zipName'], 'pos_id'=>$this->id]);
            if ( !$this->lastInsertID )
                throw new \Exception('Error adding STL files',2);
        }

        // иногда выбивает ошибку: unlink(...) Resource temporarily unavailable
        // поставим задержку что бы успел обработать файл
        sleep(1);
        foreach ( $stlFileNames as $stlFN ) {
            Files::instance()->delete($stlFN);
        }
    }


    /**
     * @param $files3DM
     * @return bool
     * @throws \Exception
     */
    public function add3dm( array $files3DM )
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
            $this->insert("rhino_files",['name'=>$zipArch['zipName'], 'size'=>$zipArchSize, 'pos_id'=>$this->id]);
            if ( !$this->affectedRows ) 
                throw new \Exception('Error adding rhino files in: ' . __METHOD__, 412);
        }

        // иногда выбивает ошибку: unlink(...) Resource temporarily unavailable
        // поставим задержку что бы успел обработать файл
        sleep(1);
        foreach ( $fileNames as $fileName )
            if ( file_exists( $path.$fileName ) ) unlink($path.$fileName);

        return true;
    }



	public function addAi( array &$filesAi ) 
    {
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
		
		if ( $countAis ) 
        {
            $this->insert("ai_files",['name'=>$zip_name,'size'=>$size,'pos_id'=>$this->id]);
            if ( !$this->lastInsertID ) {
                throw new \Exception('Error adding ai files in: ' . __METHOD__, 413);
                return false;
            }
		}
		return true;
	}
	
	public function addGems( array &$gems ) : bool
    {
        $gRows=[];
		for ( $i = 0; $i < count($gems['name']??[]); $i++ ) 
        {
			$gemsName  = trim($gems['name'][$i]);
			$gemsCut   = trim($gems['cut'][$i]);
			$gemsVal   = trim($gems['val'][$i]);
			$gemsDiam  = trim($gems['diam'][$i]);
			$gemsColor = trim($gems['color'][$i]);

            // Kind of gem name is necessary rest is not, lol 
			if ( $gemsName == "" ) continue;
            $gRows[] = [
                'gems_names' => $gemsName, 
                'gems_cut'   => $gemsCut,
                'value'      => $gemsVal,
                'gems_sizes' => $gemsDiam,
                'gems_color' => $gemsColor,
                'pos_id'     => $this->id
            ];
		}
        if ( count($gRows) )
        {
            if ( $this->isEdit === true ) 
            {
                $this->deleteFromTable('gems','pos_id',$this->id,'=');
                if ( !$this->affectedRows ) {
                    throw new \Exception( "Error deleting Gems in: " .__METHOD__, 431);
                }
            }

            $this->insertUpdateRows($gRows,"gems");
            if ( !$this->affectedRows ) {
                printf( "Error Add Gems in: " .__METHOD__);
                return false;
            }
            return true; 
        }
		return false;
	}
	
	public function addDopVC( array &$vc ) : bool
    {	
        if ( !filter_has_var(INPUT_POST, 'dop_vc_name_') )
            return false;
        $cnt = $this->request->post('dop_vc_name_')??[];

        $vcRows = [];
		for ( $i = 0; $i < count($cnt); $i++ ) 
        {
			$dop_vc_name = trim($vc['dop_vc_name'][$i]);
			$num3d_vc =  trim($vc['num3d_vc'][$i]);
			$descr_dopvc =  trim($vc['descr_dopvc'][$i]);

			if ( $dop_vc_name == "" ) continue;
            $vcRows[] = [
                'vc_names'=>$dop_vc_name, 
                'vc_3dnum'=>$num3d_vc,
                'descript'=>$descr_dopvc,
                'pos_id'=>$this->id
            ];
		}
        if ( count($vcRows) )
        {
            if ( $this->isEdit === true ) 
            {
                $this->deleteFromTable('vc_links','pos_id',$this->id,'=');
                if ( !$this->affectedRows ) {
                    throw new \Exception( "Error deleting VC_Links in: " .__METHOD__, 432);
                }
            }
            $this->insertUpdateRows($vcRows,"vc_links");
            if ( !$this->affectedRows ) {
                printf( "Error Add VC_Links: " .__METHOD__);
                return false;
            }
            return true; 
        }
        return false;
	}


	public function addNotes( array $notes = [] ) : array
    {
        if ( empty($notes) ) 
            return [];

        $notes = $this->parseRecords($notes);

        $deletions = [];
        $updates = [];
        $insertions = [];

        $this->aq->registerTable('description');
        foreach ( $notes as $note )
        {
            $noteID = (int)$note['id']??'';
            if ( $noteID < 0 || $noteID > PHP_INT_MAX ) continue;

            $noteText = trim($note['text']??'');

            if ( $noteID > 0 && $noteID < PHP_INT_MAX )
            {
                $repQuery = (int)$this->aq->description->count(field: "id")->asOne('id')->where('id','=',$noteID)->exe();

                if ( ($repQuery > 0) && ( empty($noteText) || $noteText == -1) )
                {
                    // кандидат на удаление
                    $deletions[] = $note;
                } elseif ( $repQuery > 0 ) {
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
        $ids = [];
        if ( !empty($deletions) )
        {
            foreach ( $deletions as $deletion ) 
                $ids[] = $deletion['id'];

            //debugAjax($ids,'notes deletions ids',END_AB);
            $dellQuery = $this->removeRows( ids: $ids, tableName: "description");
            if ( $this->affectedRows ) {
                $result['deletions'] = implode(',',$ids) . ' - deleted.';
            } else {
                $result['deletions'] = 'error';
            }
        }
        
        /** UPDATING */
        if ( !empty($updates) )
        {
            $updDescr = [];
            $updIds = [];
            foreach ( $updates as $update )
            {
                $updDescr[] = [
                    'id'=>$update['id'],
                    'text'=>$update['text'],
                ];
                $updIds[] = $update['id'];
            }
            $this->insertUpdateRows(rows: $updDescr, table: "description");
            if ( $this->affectedRows ) {
                $result['updates'] = implode(',',$updIds) . $this->affectedRows. ' - success updated.';
            } else {
                $result['updates'] = implode(',',$updIds) . ' - nothing to update.';
            }
        }

        /** INSERTING */
        if ( !empty($insertions) )
        {
            $insDescr = [];
            $insIds = [];
            foreach ( $insertions as $insertion )
            {
                $insDescr[] = [
                    'num'=>$insertion['num'], 
                    'text'=>$insertion['text'], 
                    'userID'=> User::getID(), 
                    'date'=>$this->date, 
                    'pos_id'=>$this->id
                ];
                $insIds[] = $insertion['text'];
            }
            $this->insertUpdateRows(rows: $insDescr, table: "description");
            if ($this->affectedRows) {
                $result['insertions'] = implode(', ',$insIds) . ' - success.';
            } else {
                printf( "Error Update notes: " . __METHOD__ );
                $result['insertions'] = implode(', ',$insIds) . ' - Insert error!';
            }
        }

        return $result;
    }

	public function addRepairs( array $repairs ) : array
    {
        if ( empty($repairs) ) return [];

        $repairs = $this->parseRecords($repairs);
        //debug($repairs,'$repairs');

        $deletions = [];
        $updates = [];
        $insertions = [];

        $this->aq->registerTable('repairs');
        foreach ( $repairs as $repair )
        {
            $repID = (int)$repair['id']??'';
            if ( $repID < 0 || $repID > PHP_INT_MAX ) continue;

            $repDescr = trim($repair['description']??'');

            if ( $repID > 0 )
            {
                $repQuery = (int)$this->aq->repairs->count(alias: "id",field: "id")->asOne('id')->where('id','=',$repID)->exe();
                if ( ($repQuery > 0) && (empty($repDescr) || $repDescr == -1) ) { // кандидат на удаление
                    $deletions[] = $repair;
                } elseif ( $repQuery > 0 ) {
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
            $dellIds = [];
            foreach ( $deletions as $deletion ) 
                $dellIds[] = $deletion['id'];
            //$dellIds = trim($dellIds,',') . ')';

            $dellQuery = $this->removeRows( ids: $dellIds, tableName: "repairs", primaryKey: 'id');
            if ( $dellQuery ) {
                $result['deletions'] = implode(',',$dellIds) . ' - deleted.';
            } else {
                printf( "Error Delete repairs in: " . __METHOD__ );
                $result['deletions'] = 'error';
            }
        }
        if ( !empty($updates) )
        {
            $updIds = [];
            $updRep = [];
            foreach ( $updates as $update )
            {
                $updIds[] = $update['id'];
                $updRep[] = [
                    'id'=> $update['id'],
                    'repair_descr'=> $update['description'], 
                    'which'=> $update['which'] ,
                ];
            }
            $this->insertUpdateRows(rows: $updRep, table: "repairs");
            if ($this->affectedRows) {
                $result['updates'] = implode(', ',$updIds) . ' - success.';
            } else {
                printf( "Error Update repairs in: " . __METHOD__ );
                $result['updates'] = implode(', ',$updIds) . ' - Insert error!';
            }
        }
        if ( !empty($insertions) )
        {
            $insIds = [];
            $insRep = [];
            foreach ( $insertions as $insertion )
            {
                $updIds[] = $insertion['description'];
                $updRep[] = [
                    'rep_num'=> $insertion['num'],
                    'repair_descr'=> $insertion['description'], 
                    'which'=> $insertion['which'] ,
                    'date'=> $this->date ,
                    'pos_id'=> $this->id ,
                ];
            }
            $this->insertUpdateRows(rows: $insRep, table: "repairs");
            if ($this->affectedRows) {
                $result['insertions'] = implode(', ',$insIds) . ' - success.';
            } else {
                printf( "Error Insert repairs in : " . __METHOD__ );
                $result['insertions'] = implode(', ',$insIds) . ' - Insert error!';
            }
        }
        return $result;
	}


    /**
     * 
     * @return array
     * @throws \Exception
     */
    public function deleteModel( int $stockID = 0 ) : array
    {
        chdir(_stockDIR_);
        $row = $this->STOCK->select(['number_3d','vendor_code','model_type','status'])->where('id','=',$this->id)->asOne()->exe();
		$result = [
		    'success' => 0,
            'error'=>'',
            'errorNo'=> 0,
		    'number_3d'  =>$row['number_3d'],
		    'vendor_code'=>$row['vendor_code'],
		    'model_type' =>$row['model_type'],
		    'status'     =>$row['status'],
            'dell'       =>$row['number_3d']." / ".$row['vendor_code']." - ".$row['model_type'],
        ];
        try {
            $result['tables'] = [
                    'stock'=>false,'metal_covering'=>false,'images'=>false,'gems'=>false,'vc_links'=>false,'statuses'=>false,
                    'ai_files'=>false, 'model_prices'=>false,'stl_files'=>false,'rhino_files'=>false,'repairs'=>false,
                    'pushnotice'=>false,'description'=>false
                ];
            if ( $tables['stock'] = $this->deleteFromTable('stock','id',$this->id) )
            {
                foreach( $result['tables'] as $table => &$res) {
                    if ( $table === 'stock' || $table === 'model_prices' ) continue;
                    $res = $this->deleteFromTable($table,'pos_id',$this->id);
                }

                // удалим только не оплаченные
                $this->sql("DELETE FROM model_prices WHERE pos_id='$this->id' AND paid='0'"); 
                if ( $this->affectedRows )
                    $result['tables']['model_prices'] = true;

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

        //debugAjax($result,'result',END_AB);

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
    public function deleteFile( string $fileName, string $fileType ) : string
    {
        if ( empty($fileName) || empty($fileType) )
            throw new \Exception('Имя и тип файла должен быть не пусты.',444);

        if ( !User::permission('files') ) return '';
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

        //$modelData = $this->findOne(" SELECT number_3d FROM stock WHERE id='$this->id' ");
        $modelNum3D = $this->STOCK->select(['number_3d'])->where('id','=',$this->id)->asOne('number_3d')->exe();

        //$dellQuery = mysqli_query($this->connection, " DELETE FROM {$config['table']} WHERE {$config['field']}='$fileName' ");
        $dellQuery = $this->deleteFromTable( table: $config['table'], primaryKey: $config['field'], value: $fileName);
        if ( !$dellQuery ) 
            throw new \Exception(__METHOD__.' Error '. mysqli_error($this->connection));

        $basePath = _stockDIR_ . $modelNum3D."/".$this->id."/{$config['folder']}/";
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

        return '';
    }

	public function deletePDF( string $pdfName) : bool
    {	
		$name = _rootDIR_."Pdfs/".$pdfName;
		if ( file_exists($name) ) 
			return unlink($name);
		
		return false;
	}


    /**
     * Формирует массив строк для пакетной вставки/обновления строк
     * в дополнительные таблицы
     * @param $data
     * @param $stockID
     * @param $tableName
     * @return array
     * @throws \Exception
     */
    public function makeBatchInsertRow(array $data, int $stockID, string $tableName) : array
    {
        if ( empty($data) ) return [];
        $dataRows = [];
        $insertRows = [];
        $removeRows = [];

        $data = $this->parseRecords($data);
		
		//debugAjax($stockID,'stockID');
		//debugAjax($data,'parsedRecords',END_AB);

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
        //debugAjax($dataRows,'dataRows');

        foreach ( $dataRows as $key => $dataRow )
        {
            $emptyFields = true;
            $toRemove = false;
            foreach ( $dataRow as $field=>&$value )
            {
                // когда хоть одно поле заполнено - оставим для внесения в табл.
                if ( !empty($value) ) {
                    $emptyFields = false;
                }
                //For Strict SQL mode
                if ( $field === 'id' )
                    if ( $value === '' ) $value = 0;

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

        //debugAjax($insertRows,'insertRows',END_AB);

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
        $images = $this->IMAGES->select(['*'])->where('pos_id','=',$this->id)->exe();
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
    public function getUserIDFromSurname( string $surname ) : int
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
<?php
namespace models;

use soffit\{
    ActiveQuery, Sessions, Request, Registry
};

class General extends ActiveQuery
{
    public static int $count = 0;

	protected array $alphabet = [];
	protected array $server = [];

    public static array $serviceArr = [];

	public array $user = [];
	public array $users = [];
	public array $statuses = [];
	public array $labels = [];
	public array $imageStatuses = [];
	public array $workingCentersDB = [];
	public array $workingCentersSorted = [];
    
    public string $IP_visiter = '';
    public string $localSocket = '';
    
    public Sessions $session;
    public Request $request;
    //public ActiveQuery $aq;

    public function __construct( $server=false )
    {
        parent::__construct(); //ActiveQuery Constructor
        self::$count++;

        $this->session = Registry::init()->sessions;// new Sessions();
        $this->request = Registry::init()->request; //new Request();

        $assist = $this->session->getKey('assist');
        $assist['GeneralsCount'] = self::$count;
        $this->session->setKey('assist',$assist);

        //$this->aq = new ActiveQuery(['users','working_centers','statuses','service_arr','service_data']);
        $this->registerTable(['users','working_centers','statuses','service_arr','service_data']);

        $this->server = $this->request->server;

        $this->IP_visiter = User::getIp();
        $this->localSocket = _WORK_PLACE_ ? 'tcp://192.168.0.245:1234' : 'tcp://127.0.0.1:1234';

        $this->alphabet = alphabet();
    }

    /**
     * Old name, need for compatibility
     * @return \mysqli
     * @throws \Exception
     */
    public function connectToDB() : void
    {
        $this->getUser();
        $this->getWorkingCentersDB();
        $this->getServiceArr();

        $this->statuses = $this->getStatLabArr('status');
        $this->labels = $this->getStatLabArr('labels');
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getUser() : array
    {
        if ( !empty($this->user) ) return $this->user;
        
        $this->user = User::init();
        
        if ( empty($this->user) )
            $this->request->redirect('auth/',['a'=>'exit']);

        $this->user['IP'] = User::getIp();
        return $this->user;
    }


    /**
     * @param bool $full
     * @return mixed
     * @throws \Exception
     */
    public function getUsers( bool $full=false ) : array
    {
        if ( !empty($this->users) ) return $this->users;
        $logPass = [];
        if ( $full ) $logPass = ['login', 'pass'];

        return $this->users = $this->USERS->select(['id', ...$logPass, 'fio','fullFio','location','access'])->exe();
    }


    /**
     * сформируем массив разрешений для текущего пользователя
     * @return array
     * @throws \Exception
     */
    public function permittedFields() : array
    {
        return User::permissions();
    }


    /**
     * рабочие центры из БД
     * @return mixed
     * @throws \Exception
     */
    public function getWorkingCentersDB() : array
    {
        if (!empty($this->workingCentersDB)) {
            return $this->workingCentersDB;
        }
        $wc = $this->WORKING_CENTERS->select(['id','name','descr','user_id'])->exe();
        if ( !$this->WORKING_CENTERS->numRows ) 
            throw new \Exception('Working Centers not found at all!',500);

        foreach ( $wc as  $centerRow ) {
           $this->workingCentersDB[$centerRow['name']][$centerRow['id']] = $centerRow;
        }
        return $this->workingCentersDB;
    }

    /**
     *
     * Выберем все участки, отсортируем их
     * и подставим им статусы start end
     * @return array
     * @throws \Exception
     */
    public function getWorkingCentersSorted() : array
    {
        if ( !empty($this->workingCentersSorted) ) 
            return $this->workingCentersSorted;

        $this->getStatLabArr('status');

        $centerRows = $this->WORKING_CENTERS->select(['*'])->orderBy('sort_id', 'ASC')->exe();
        if ( !count($centerRows) ) 
            throw new \Exception('Working Centers not found at all!',505);

        foreach( $centerRows as $centerRow )
        {
            $wcID = (int)$centerRow['id'];

            foreach ( $this->statuses as $status )
            {
                $location = (int)$status['location'];
                $type = $status['type'];
                if ( $location === $wcID )
                {
                    if ( $type === 'start' ) $centerRow['statuses']['start'] = $status;
                    if ( $type === 'end'  ) $centerRow['statuses']['end'] = $status;
                }
            }

            $this->workingCentersSorted[ $centerRow['sort_id'] ] = $centerRow;
        }
        return $this->workingCentersSorted;
    }
	
    /**
     * Need for ModelView,Main and their Controllers
     *
     */
	public function getStatus( array $row=[], string $selMode='') : array
    {
		$result = [];
		if ( empty($row['status']) )
            return $result;
		
        // при добавлении новых моделей в stock status заходит ID, а нам нужно имя
        // КОСТЫЛЬ возьмёт это имя из списка статусов по ID
        $result['stat_name'] = $row['status'];
        if ( $rowStatus = $this->getStatusByID($row['status']) ) 
            $result['stat_name'] = $rowStatus;

        $row['status_date'] = $row['status_date']??'';
		$result['stat_date'] = ($row['status_date'] == "0000-00-00") ? "" : date_create( $row['status_date'] )->Format('d.m.Y')."&#160;";

        foreach ( $this->statuses as $status )
        {
            if ( $result['stat_name'] === $status['name_ru'] )
            {
                $result['id'] = $status['id'];
                $result['class'] = $status['class'];
                $result['classMain'] = $status['name_en'];
                $result['glyphi'] = $status['glyphi'];
                $result['title'] = $status['title'];
                break;
            }
        }
		
		return $result;
	}

    /**
        при добавлении новых моделей в stock status заходит ID
        возьмём этот Id из статусов
     *
     * @param $stockStatus
     * ID статуса который хотим поставить по умолчанию
     * @param bool $index
     * вернем масств со всеми данными статуса если true. иначе только имя на руском
     * @return bool
     */
    public function getStatusByID( int $stockStatus, bool $index=false) : array|string
    {
        if ( $stockStatus )
        {
            if ( empty($this->statuses) ) 
                throw new \Exception("Not statuses found in: " . __METHOD__, 5);

            foreach ( $this->statuses as $status )
            {
                if ( (int)$status['id'] === $stockStatus )
                {
                    if ($index) return $status;
                    return $status['name_ru'];
                }
            }
        }
        return '';
    }
        
    public function addStatusesTable(array $statusT) : bool
    {
        //04,07,19 - вносим новый статус в таблицу statuses
        if ( empty($statusT) ) return false;

        $pos_id = $statusT['pos_id'];
        $status = $statusT['status'];
        $statuses = $this->statuses;
        foreach ( $statuses as $statusArr )
        {
            if ( $status === $statusArr['name_ru'] )
            {
                $status = $statusArr['id'];
                break;
            }
        }

        $name = isset($statusT['creator_name']) ? $statusT['creator_name'] : "";
        $date = isset($statusT['UPdate']) ? $statusT['UPdate'] : date("Y-m-d H:i:s");
        
        $this->insert('statuses', ['pos_id'=>$pos_id,'status'=>$status,'name'=>$name,'date'=>$date]);
        if ( $this->affectedRows ) return true;
        return false;
	}

    /**
     * @param $lables_str
     * @return array
     * @throws \Exception
     */
    public function getLabels( string $lables_str ) : array
    {
		$result = [];
		if ( empty($lables_str) ) return $result;
		
		$labels = $this->getStatLabArr('labels');
		$arr_labels = explode(";",$lables_str);
		$c = 0;
		for ( $i = 0; $i < count($arr_labels); $i++ )
		{
			for ( $j = 0; $j < count($labels); $j++ )
			{
				if ( $arr_labels[$i] == $labels[$j]['name'] )
				{
                    $result[$c] = $labels[$j];
					$result[$c]['check'] = "checked";
					$c++;
				}
			}
		}
		return $result;
	}

    /**
     * @throws \Exception
     */
    public function getServiceArr() : array
    {
        if (!empty(self::$serviceArr)) 
            return self::$serviceArr;

        //return self::$serviceArr = (new ActiveQuery('service_arr'))->service_arr->select(['*'])->exe();
        return self::$serviceArr = $this->SERVICE_ARR->select(['*'])->exe();
    }

    /**
     * @param $query
     * @param string $location - ID участка
     * @return bool | mixed
     * @throws \Exception
     */
    public function getStatLabArr( string $query, $location = '') : array
    {
        //статусы
		if ( $query == 'status' )
		{
		    if ( !empty($location) && trueIsset($this->statuses) )
            {
                if ( !is_int($location) ) 
                    throw new \Exception('location must be integer',500);
                $arrStatuses = [];
                foreach ( $this->statuses as $status )
                    if ( $status['location'] == $location && $status['tab'] == 'status' ) $arrStatuses[] = $status;

                return $arrStatuses;
            }

            if( !empty($this->statuses)  ) return $this->statuses;

            foreach ( $this->getServiceArr() as $status )
                if ( $status['tab'] == 'status' ) $this->statuses[] = $status;

            return $this->statuses;
		}

		//метки
		if ( $query == 'labels' )
		{
            if ( !empty($this->labels) ) return $this->labels;

            $c = 0;
            foreach ( $this->getServiceArr() as $status )
            {
                if ( $status['tab'] !== 'label' ) continue;

                $this->labels[$c]['id'] = $status['name_en'];
                $this->labels[$c]['name'] = $status['name_ru'];
                $this->labels[$c]['class'] = $status['class'];
                $this->labels[$c]['info'] = $status['title'];
                $this->labels[$c]['check'] = '';
                $c++;
            }
            return $this->labels;
		}

		// статусы картинок
		if ( $query == 'image' )
		{
            if ( !empty($this->imageStatuses) ) 
                return $this->imageStatuses;

            $c = 0;
            foreach ( $this->getServiceArr() as $imageStatus )
            {
                if ( $imageStatus['tab'] !== 'status_image' ) continue;

                $this->imageStatuses[$c]['id'] = $imageStatus['id'];
                $this->imageStatuses[$c]['name'] = $imageStatus['name_ru'];
                $this->imageStatuses[$c]['name_en'] = $imageStatus['name_en'];
                $this->imageStatuses[$c]['selected'] = '';

                if ( (int)$imageStatus['id'] === 27 ) 
                    $this->imageStatuses[$c]['selected'] = 1;

                $c++;
            }
            return $this->imageStatuses;
		}

		throw new \Exception("Wrong table query in " . __METHOD__, 701);
	}

    /**
     * Вернет массив с данными этого статуса, по его ID
     * @param int $statusID - id искомого статуса
     * @return array
     * @throws \Exception
     */
	public function getStatusInfo( int $statusID ) : array
    {

        if( !trueIsset($this->statuses)  )
             $this->getStatLabArr('status');

        foreach ( $this->statuses as $statusArr )
        {
            if ( $statusArr['id'] == $statusID )
                return $statusArr;
        }

        return [];
    }


    /**
     * @param int $maxAllowedFiles
     * @throws \Exception
     */
    public function backup(int $maxAllowedFiles = 10)
	{
		$localtime = localtime(time(), true);
		// бэкапимся только с 4х до 6
		if ( ($localtime['tm_hour']+1) < 16 || ($localtime['tm_hour']+1) >= 18 ) return;
		
		$today = date('Y-m-d');
		
		//$row = mysqli_fetch_assoc( mysqli_query($this->connection, " SELECT lastdate FROM backup " ) );
		$row = ( new ActiveQuery('backup'))->backup->select(['lastdate'])->asOne()->exe();
		$lastDate = explode(' ', $row['lastdate'])[0];
		
		if ( strtotime($lastDate) < strtotime($today)  )
		{
            $dbConfig = $this->dbConfig;
			$backupDatabase = new Backup_Database($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['dbname']);
			
			$this->checkBackupFiles( (int)$maxAllowedFiles, BACKUP_DIR);
			
			$result = $backupDatabase->backupTables(TABLES) ? 'OK' : 'KO';
			$backupDatabase->obfPrint('Backup result: ' . $result, 1);

			if ( $backupDatabase->done === true )
			{
				$ddddate = new \DateTime('+1 hour');
				$ddmmii = $ddddate->format('Y-m-d H:i:s');

                $this->update('backup', ['lastdate'=>$ddmmii]);
			}

		}
	}
	protected function checkBackupFiles(int $maxAllowedFiles, string $backupDir)
	{
		$dir = opendir( $backupDir );
		$count = 0;
		// массив с последними датами изменения файлов
		$filesMTime = [];
		while($file = readdir($dir))
		{
			if( $file == '.' || $file == '..' || is_dir($backupDir . "/" . $file) )
			{
				continue;
			}
			$filesMTime[$count]["time"] = filectime($backupDir . "/" . $file);
			$filesMTime[$count]["name"] = $file;
			$count++;
		}
		
		if ( $count >= $maxAllowedFiles  )
		{
			$min = $filesMTime[0]["time"];
			$name = $filesMTime[0]["name"];
			foreach ( $filesMTime as $val  )
			{
				if ( $val["time"] < $min ) 
				{
					$min = $val["time"];
					$name = $val["name"];
				}
			}
			unlink($backupDir . "/" . $name);
		}
		
		//debug( $name . " " . date("F d Y H:i:s.", $min) , "ggggggg");
	}

    
    /**
     * @param string $modelDate
     * @param int $component
     * @return bool
     * @throws \Exception
     */
    public function statusesChangePermission( string $modelDate, int $component) : bool
    {
        if ( empty($modelDate) ) return false;

        // Путь прохождения модели.
        // user access => status id
        //Пример: 2 => 89 - 3д моделлер может менять статус только после Утверждения дизайна
        /*$changeStatusesAccess = [
            //10 => 35, // утверждать эскиз, когда стоит статус эскиз
            2  => 89, // 3д после утв. эскиза
            11 => 89, // 3д после утв. эскиза
            //2 => 106,
            7 => 1,   // Валик может подписать только если модель на проверке
            9 => 89,  // подпись 3д техн. эскиза ( после подписи Валика)
            3 => 2,   // рост/поддержки после проверено
            5 => 5,   // доработка после выращено
            8 => 5,   // УчастокПДО после выращено
            4 => 5,   // Все остальные тоже после выращено
        ];*/

        // statusPresent => notPresent
        $currentStatusesAccess = [
            35=>[ // Эскиз
                'notPresent' => 89, // отсутствует
                'preset' => [10], // пресеты user Access
            ],
            11=>[ // Отложено
                'notPresent' => 89,
                'preset' => [10],
            ],
            89=>[ // Диз. утв.
                'notPresent' => 47,
                'preset' => [2,9,11],
            ],
            10=>[ // В ремонте 3D
                'notPresent' => 2, // Проверено
                'preset' => [2,7,9,11,8],
            ],
            47=>[ // Готово 3д
                'notPresent' => 1, //101, 106
                'preset' => [2,9,11],
            ],
            /*
            106=>[ // 3D дизайн утвержден
                'notPresent' => 1,
                'preset' => [2,9,11],
            ],
            */
            1=>[ // На проверке
                'notPresent' => 2,
                'preset' => [9,7],
            ],
            101=>[// Подпись технолога
                'notPresent' => 2, // Проверено
                'preset' => [9,11],
            ],
            2=>[ // Проверено
                'notPresent' => 5,
                'preset' => [3,9,11,8],
            ],
            5=>[ // Выращено
                'notPresent' => 2000,
                'preset' => [1,2,3,4,5,6,7,8,9,10,11,12],
            ],
        ];

        // Сделал привязку к конкретной дате, старые модели сделаны до неё не будут проверены на наличие нужных статусов
        // это сделано что бы участки могли принять старые модели в ремонт, в случае чего
        $comp_date =  new \DateTime($modelDate) < new \DateTime("2021-06-01") ? false : true;

        $toShowStatuses = true;
        if ( $comp_date && ($component !== 1) && ($component !== 3) )
        {

            $toShowStatuses = false;
            // Что бы предотвратить изменения статусов у модели, если не поставлен статус сдачи пред. участка
            // при постановке нужных статусов, некоторым людям начислятся деньги в кошельке работника

            if ( User::getAccess() !== 1 )
            {
                // Для этого способа нужна временная метка
                foreach ($currentStatusesAccess as $status => $access)
                {
                    // доступ для ред. статусов только пользователям с пресетам как в массивах
                    if ( $status1LastDate = $this->statusPresentLastDate($status) )
                    {
                        if ( $status2LastDate = $this->statusPresentLastDate($access['notPresent']) )
                        {

                            $status1LastDate = strtotime($status1LastDate);
                            $status2LastDate = strtotime($status2LastDate);
//                            debug($status1LastDate,'$status1LastDate');
//                            debug($status2LastDate,'$status2LastDate');

                            if ( ($status1LastDate > $status2LastDate) ) //|| ($status1LastDate == $status2LastDate)
                            {
//                                debug($status,'$status принятия: ');
//                                debug($access['notPresent'], 'статус сдачи просрочен: ');
//                                debug(User::getAccess(),'User getAccess');

                                $toShowStatuses = in_array(User::getAccess(), $access['preset']);
                                break;
                            }

                            /** Exit */
                        } else {
//                            debug($status,'$status принятия');
//                            debug($access['notPresent'],'Нет статуса сдачи: ');
//                            debug(User::getAccess(),'User getAccess');

                            $toShowStatuses = in_array(User::getAccess(), $access['preset']);
                            break;
                        }

                        /** Exit */
                    }
                }

            }
        }

        return $toShowStatuses;
    }

    /**
     * @param int $statusID
     * @param int $stock_id
     * @return bool | mixed
     * @throws \Exception
     */
    public function isStatusPresent(int $statusID = 0, int $stock_id = 0 ) : bool
    {
        if ( $stock_id <= 0 || $stock_id > PHP_INT_MAX )
        {
            if ( !$this->id )
                throw new \Exception('Model ID is wrong! Check stock table for this id: ' . $this->id, 0);

            $stock_id = $this->id;
        }
        //$query = $this->baseSql( "SELECT 1 FROM statuses WHERE pos_id='$this->id' AND status='$statusID'" );
        $q = $this->STATUSES->count('c','pos_id')->where('pos_id','=',$stock_id)->and('status','=',$statusID)->asOne('c')->exe();
        //if ( $query->fetchColumn() ) return true;
        if ( $q ) return true;

        return false;
    }

    /**
     *
     * @param int $statusID
     * @param int $stock_id
     * @return string
     * @throws \Exception
     */
    public function statusPresentLastDate(int $statusID = 0, int $stock_id = 0 ) : string
    {
        if ( $stock_id <= 0 || $stock_id > PHP_INT_MAX )
        {
            if ( !$this->id )
                throw new \Exception('Model ID is wrong! Check stock table for this id: ' . $this->id, 0);

            $stock_id = $this->id;
        }
        $result = $this->STATUSES
                ->select(['date'])
                ->where(['pos_id','=',$stock_id])
                ->and(['status','=',$statusID])
                ->asOne()
                ->exe();
        
        return isset($result['date']) ? $result['date'] : '';
    }

    /**
     * // Проверим существование превьюшки
     * @param string $imgPath - не полный! Начинается с №3Д
     * @param string $imgName
     * @return string
     */
    public function checkSetPreviewImg( string $imgPath, string $imgName ) : string
    {
        if ( empty($imgPath) || empty($imgName) )
            throw new \Error("Path or img name can't be empty in checkPreviewImg", 500);
        if ( !file_exists(_stockDIR_.$imgPath.$imgName) )
            throw new \Error("Original img not found in checkPreviewImg", 500);

        $name = pathinfo($imgName, PATHINFO_FILENAME);
        $ext = pathinfo($imgName, PATHINFO_EXTENSION);
        $prevImgName = $name . ImageConverter::getImgPrevPostfix() . '.' . $ext;

        if ( file_exists(_stockDIR_.$imgPath.$prevImgName) )
            return $prevImgName;

        return '';
    }

    /**
     * Из массива, выберет превью картинку или оригин. если превью нет
     * используется в видах modelView и addEdit
     * @param array $image
     * @return mixed|string
     */
    public function origin_preview_ImgSelect( array $image ) : string
    {
        if  ( !isset($image['imgPath']) )
            return '';

        if ( !isset($image['imgPrevPath']) || empty($image['imgPrevPath']) )
            return $image['imgPath'];

        return $image['imgPrevPath'];
    }

    /**
     * Из массива, выберет превью картинку или оригин. если превью нет
     * используется в видах modelView и addEdit
     * @param array $complected
     * @param array $dopFields
     * @return mixed|string
     * @throws \Exception
     */
    public function sortComplectedData( array $complected=[], array $dopFields = [] )
    {
        if ( empty($complected) ) return [];

        $images = [];
        $dop_iter = 0;
        foreach ( $complected as $image )
        {
            $main = 'main';
            $sketch = 'sketch';
            if ( trueIsset($image['main']) )
            {
                $images[$image['pos_id']]['img_names'][$main] = $image['img_name'];
            } elseif ( trueIsset($image['sketch']) )
            {
                $images[$image['pos_id']]['img_names'][$sketch] = $image['img_name'];
            } else {
                $dopImg = 'dopimg_'.$dop_iter++;
                $images[$image['pos_id']]['img_names'][$dopImg] = $image['img_name'];
            }

            $images[$image['pos_id']]['pos_id'] = $image['pos_id'];

            // доп данные
            foreach ( $dopFields as $dopField )
            {
                if ( trueIsset($dopField) && is_string($dopField) )
                {
                    if ( trueIsset($image[$dopField]) )
                        $images[$image['pos_id']][$dopField] = $image[$dopField];
                }
            }
        }

        foreach ($images as &$complect )
        {
            $imgPath = $complect['number_3d'].'/'.$complect['pos_id'].'/images/';
            $imgName = $complect['img_names'][ array_key_first($complect['img_names']) ]; // первая попавшаяся

            foreach ( $complect['img_names'] as $iStat => $iName )
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

        return $images;
    }


    /**
     * @throws \Exception
     */
    public function getDesignApproveModels()
    {
        return $this->findOne("SELECT COUNT(status) as c FROM stock WHERE status=35 ",'c');
        //$this->registerTable('stock');
        //return $this->STOCK->count('status')->asOne('status')->where('status','=',35)->exe();
    }

    /**
     * @throws \Exception
     */
    public function countRepairsToWork()
    {
        $userFIO = explode(' ', User::getFIO())[0];
        return $this->findOne("SELECT COUNT(toWhom) as c FROM repairs WHERE toWhom LIKE '%$userFIO%' AND status<>4 ",'c');
    }
	
	/**
     * @throws \Exception
     */
    public function countRepairsToShow()
    {
        return $this->findOne("SELECT COUNT(toWhom) as c FROM repairs WHERE status_date > '0000-00-00' AND status<>4 ",'c');
    }

    /**
     * @param bool $all
     * @return array|mixed
     * @throws \Exception
     */
    public function countModels3DToWork( bool $all = false )
    {
        if ( $all )
            return $this->findOne("SELECT COUNT(*) as c FROM stock WHERE `status`=89",'c');

        $userFIO = explode(' ', User::getFIO())[0];
        return $this->findOne("SELECT COUNT(modeller3D) as c FROM stock WHERE modeller3D LIKE '%$userFIO%' AND status=89 ",'c');
    }

    /**
     * @param bool $all
     * @return array|mixed
     * @throws \Exception
     */
    public function countModels3DInWork( bool $all = false )
    {
        if ( $all )
            return $this->findOne("SELECT COUNT(*) as c FROM stock WHERE `status`=8",'c');

        $userFIO = explode(' ', User::getFIO())[0];
        return $this->findOne("SELECT COUNT(modeller3D) as c FROM stock WHERE modeller3D LIKE '%$userFIO%' AND status=8  ",'c');
    }

    /**
     * @param array $tabs
     * @return array
     * @throws \Exception
     */
    protected static $tabsOrigin = [];
    public function getServiceData( array $tabs = [], string $orderBy = 'name', string $direction = 'ASC' )
    {
        $servData = $this->SERVICE_DATA;
        if ( empty(self::$tabsOrigin) )
        {
            $tO = $servData->select(['tab'], distinct: true)->exe();
            foreach( $tO as $to ) 
                self::$tabsOrigin[] =$to['tab'];
        }

        if (empty($tabs))
        {
            $tabs = self::$tabsOrigin;
        } else {
            foreach ( $tabs as $k => $t )
                if (!in_array($t, self::$tabsOrigin))
                    throw new  \Exception("Wrong tab name ".$t." in " . __METHOD__);
        }
        $service_data = $servData->select(['*'])
                ->where('tab','IN',$tabs)
                ->orderBy($orderBy,$direction)
                ->exe();

        $tables = [];
        foreach ( $service_data as $row )
        {
            foreach ( $tabs as $tab )
                if ( $row['tab'] === $tab ) 
                    $tables[$tab][] = $row;
        }
        return $tables;
    }

    /**
     * @throws \Exception
     */
    public function getModelMaterialsSelect() : array
    {
        $mats = $this->getServiceData(['model_material']);
        $mats = $mats['model_material']??[];

        $name = '';
        foreach ( $mats as $k => &$mat )
        {
            $nameO = explode(';',$mat['name'])[0];
            if ( $nameO !== $name )
            {
                $name = $nameO;
                $mat['name'] = $nameO;
            } else {
                unset($mats[$k]);
            }
        }

        return $mats;
    }
}
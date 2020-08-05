<?php
namespace Views\_Globals\Models;

use Views\vendor\core\{Config,Model,Sessions,Request};
use Views\vendor\core\db\Database;
use Views\vendor\core\Errors\Exceptions\DBConnectException;
use Views\vendor\libs\classes\AppCodes;

class General extends Model
{

	protected $alphabet = [];
	protected $server;
	protected $rootDir;
	protected $stockDir;

    /**
     * @var array
     * config array for current user
     */
	protected $dbConfig;

    public static $serviceArr;

	public $user;
	public $users;
	public $statuses;
	public $labels;
	public $imageStatuses;
    public $IP_visiter;
	public $workingCentersDB;
	public $workingCentersSorted;
    public $localSocket = '';
    public $session = null;
    public $request = null;

    public function __construct( $server=false )
    {
        $this->server = $_SERVER;
        $this->setDirs();

        $this->IP_visiter = _WORK_PLACE_ ? $_SERVER['HTTP_X_REAL_IP'] : $_SERVER['REMOTE_ADDR'];
        $this->localSocket = _WORK_PLACE_ ? 'tcp://192.168.0.245:1234' : 'tcp://127.0.0.1:1234';

        $this->session = new Sessions();
        $this->request = new Request();

        $this->alphabet = alphabet();
    }

    /**
     * @return \mysqli
     * @throws \Exception
     */
    public function connectDBLite() : \mysqli
    {
        if ( is_object($this->connection) && ( $this->connection instanceof \mysqli) )
            return $this->connection;

        $this->dbConfig = Config::get('db');
        if ( !trueIsset($this->dbConfig) )
            throw new DBConnectException( AppCodes::DB_CONFIG_EMPTY );

        // По ID юзера из сессии - определим через какого юзера подключаться к БД
        $user = $this->session->hasKey('user') ? $this->session->getKey('user') : null;
        $userAccess = 0;
        if ( trueIsset( $user ) )
            $userAccess = (int)$user['access'] ?? 0;

        foreach ( $this->dbConfig as $userDbName => $userConnArr )
        {
            if ( isset($userConnArr['access']) )
            {
                if ( in_array( $userAccess, $userConnArr['access'] ) )
                {
                    $this->dbConfig = $userConnArr;
                    Config::set('db', [ $userDbName => $userConnArr ]);
                    return parent::connectDB( $userConnArr );
                }
            } else {
                throw new DBConnectException( AppCodes::DB_CONFIG_ACCESS_FIELD_EMPTY );
            }
        }

        throw new DBConnectException( AppCodes::USER_DB_CONFIG_EMPTY );
    }

    /**
     * @return \mysqli
     * @throws \Exception
     */
    public function connectToDB() : \mysqli
    {
        $connection = $this->connectDBLite();

        $this->getUser();

        if ( !isset(self::$serviceArr) ) self::$serviceArr = self::getServiceArr();

        $this->statuses = $this->getStatLabArr('status');
        $this->labels = $this->getStatLabArr('labels');
        $this->getWorkingCentersDB();

        return $connection;
    }

	public function formatDate($date)
    {
        $fdate = is_int($date) ? '@'.$date : $date;
        return date_create( $fdate )->Format('d.m.Y');
    }

	protected function setDirs() 
    {
		$this->rootDir  = _rootDIR_; //'/HUF_DB';
		$this->stockDir = _stockDIR_;//$this->rootDir.'Stock';
	}


    /**
     * @return mixed
     * @throws \Exception
     */
    public function getUser()
    {
        if ( isset($this->user) )
            return $this->user;

        $userID = null;
        if ( $user = $this->session->getKey('user') )
            $userID = (int)$user['id']??'';

        $this->user = $this->findOne(" SELECT id,fio,fullFio,location,access FROM users WHERE id='$userID' ");

        if ( empty($this->user) )
            $this->request->redirect('/auth/?a=exit');

        $this->user['IP'] = $this->IP_visiter;
        return $this->user;
    }


    /**
     * @param bool $full
     * @return mixed
     * @throws \Exception
     */
    public function getUsers( bool $full=false )
    {
        if ( trueIsset($this->users) ) return $this->users;
        $logPass = '';
        if ( $full ) $logPass = "login, pass,";

        return $this->users = $this->findAsArray(" SELECT id, $logPass fio,fullFio,location,access FROM users ");
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
    public function getWorkingCentersDB()
    {
        if ( isset($this->workingCentersDB) ) return $this->workingCentersDB;

        $query = mysqli_query($this->connection, " SELECT id,name,descr,user_id FROM working_centers ");

        if ( $query === false ) throw new \Exception('Error in working centers query.',500);
        if ( !$query->num_rows ) throw new \Exception('Working Centers not found at all!',500);

        while( $centerRow = mysqli_fetch_assoc($query) )
        {
            $this->workingCentersDB[ $centerRow['name'] ][ $centerRow['id'] ] = $centerRow;
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
    public function getWorkingCentersSorted()
    {
        if ( isset($this->workingCentersSorted) ) return $this->workingCentersSorted;

        $this->getStatLabArr('status');

        $query = mysqli_query($this->connection, " SELECT * FROM working_centers ORDER BY sort_id");
        if ( !$query->num_rows ) new \Exception('Working Centers not found at all!',500);

        while( $centerRow = mysqli_fetch_assoc($query) )
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
     * возвращает строку в транслите.
     * @param $str
     * @return string
     */
    public function translit($str)
    {
		$str = mb_strtolower($str,'UTF-8');
		$chars = preg_split('//u',$str,-1,PREG_SPLIT_NO_EMPTY);

		foreach ($chars as $key => $value) {
			$ff = false;
			foreach ($this->alphabet as $alph_key => $alph_value) {
				if ( $value == $alph_key ) {
					$eng_arrmt[] = $alph_value;
					$ff = true;
					continue;
				}
			}
			if ( !$ff ) $eng_arrmt[] = $value;
		}
		return implode($eng_arrmt?:[]);
	}
	
	public function rrmdir($src) {
		$dir = opendir($src);
		while(false !== ( $file = readdir($dir)) ) {
			if (( $file != '.' ) && ( $file != '..' )) {
				$full = $src . '/' . $file;
				if ( is_dir($full) ) {
					$this->rrmdir($full);
				}
				else {
					unlink($full);
				}
			}
		}
		closedir($dir);
		rmdir($src);
	}
	
	public function getStatus($row=[], $selMode='')
    {
		$result = array();
		if ( !empty($row['status']) )
		{
            $statuses = $this->statuses;

            //  КОСТЫЛЬ!!!!
            // при добавлении новых моделей в stock status заходит ID
            // возьмём этот Id из статусов
            $result['stat_name'] = $row['status'];
            if ( $rowStatus = $this->getStatusCrutch($row['status']) ) $result['stat_name'] = $rowStatus;

			$result['stat_date'] = ($row['status_date'] == "0000-00-00") ? "" : date_create( $row['status_date'] )->Format('d.m.Y')."&#160;";

            foreach ( $statuses as $status )
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
		}
		return $result;
	}

    //  КОСТЫЛЬ!!!!
    // при добавлении новых моделей в stock status заходит ID
    // возьмём этот Id из статусов
    public function getStatusCrutch($stockStatus, $index=false)
    {
        if ( $stockStatus = (int)$stockStatus )
        {
            foreach ( $this->statuses as $status )
            {
                if ( (int)$status['id'] === $stockStatus )
                {
                    if ($index) return $status;
                    return $status['name_ru'];
                }
            }
        }
        return false;
    }
        
    public function addStatusesTable($statusT = [])
    {
        //04,07,19 - вносим новый статус в таблицу statuses
        if ( empty($statusT) ) return;

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
        $date = isset($statusT['UPdate']) ? $statusT['UPdate'] : "0000-00-00";
        $queryStr = "INSERT INTO statuses (pos_id,status,name,date) VALUES('$pos_id','$status','$name','$date')";

        mysqli_query($this->connection, $queryStr );
	}

    /**
     * Метод нужен только для внесения первого статуса в табл. statuses при посешении старых моделей
     * используется только в AddEdit_Controller
     * @param $id
     * @param $status_name
     * @param $status_date
     */
    public function getStatuses($id, $status_name, $status_date )
    {
        $statsQuery = mysqli_query($this->connection, " SELECT status,name,date FROM statuses WHERE pos_id='$id' ");

        if ( mysqli_num_rows($statsQuery) ) return;

        $statusT = [];
        $statusT['pos_id'] = $id;
        $statusT['status'] = $status_name;
        $statusT['creator_name'] = "";
        $statusT['UPdate'] = $status_date;
        $this->addStatusesTable($statusT);
    }

    /**
     * @param $str
     * @return array
     * @throws \Exception
     */
    public function getLabels($str)
    {
		$result = array();
		if ( isset($str) && !empty($str) )
		{
			$labels = $this->getStatLabArr('labels');
			$arr_labels = explode(";",$str);
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
		}
		return $result;
	}

    /**
     * @return mixed
     * @throws \Exception
     */
    private static function getServiceArr()
    {
        if ( trueIsset(self::$serviceArr) ) return self::$serviceArr;
        $serviceQuery = mysqli_query(self::$connectObj,"SELECT * FROM service_arr");

        if ( $serviceQuery === false ) throw new \Exception('Tables "labels" or "Statuses" not found',500);

        while ( $data = mysqli_fetch_assoc($serviceQuery) ) self::$serviceArr[] = $data;
        //debug('serviceArr empty');

        return self::$serviceArr;
    }

    /**
     * @param $query
     * @param string $location
     * @return bool | mixed
     * @throws \Exception
     */
    public function getStatLabArr($query, $location = '')
    {

        //статусы
		if ( $query == 'status' )
		{
		    if ( !empty($location) && trueIsset($this->statuses) )
            {
                if ( !is_int($location) ) throw new \Exception('location must be integer',500);
                $arrStatuses = [];
                foreach ( $this->statuses as $status )
                {
                    if ( $status['location'] == $location && $status['tab'] == 'status' ) $arrStatuses[] = $status;
                }
                return $arrStatuses;
            }

            if( trueIsset($this->statuses)  )
            {
                return $this->statuses;
            }

            foreach ( self::getServiceArr() as $status )
            {
                if ( $status['tab'] == 'status' ) $this->statuses[] = $status;
            }

            return $this->statuses;
		}

		//метки
		if ( $query == 'labels' )
		{
            if ( trueIsset($this->labels) ) return $this->labels;

            $c = 0;
            foreach ( self::getServiceArr() as $status )
            {
                if ( $status['tab'] == 'label' )
                {
                    $this->labels[$c]['id'] = $status['name_en'];
                    $this->labels[$c]['name'] = $status['name_ru'];
                    $this->labels[$c]['class'] = $status['class'];
                    $this->labels[$c]['info'] = $status['title'];
                    $this->labels[$c]['check'] = '';
                    $c++;
                }
            }
            return $this->labels;
		}

		// статусы картинок
		if ( $query == 'image' )
		{
            if ( isset($this->imageStatuses) ) return $this->imageStatuses;

            $c = 0;
            foreach ( self::$serviceArr as $imageStatus )
            {
                if ( $imageStatus['tab'] == 'status_image' )
                {
                    $this->imageStatuses[$c]['id'] = $imageStatus['id'];
                    $this->imageStatuses[$c]['name'] = $imageStatus['name_ru'];
                    $this->imageStatuses[$c]['name_en'] = $imageStatus['name_en'];
                    $this->imageStatuses[$c]['selected'] = '';
                    if ( $imageStatus['id'] == 27 ) $this->imageStatuses[$c]['selected'] = 1;
                    $c++;
                }
            }
            return $this->imageStatuses;
		}

		return false;
	}

    /**
     * @param int $maxAllowedFiles
     * @throws \Exception
     */
    public function backup($maxAllowedFiles = 10)
	{
		$localtime = localtime(time(), true);
		// бэкапимся только с 4х до 6
		if ( ($localtime['tm_hour']+1) < 16 || ($localtime['tm_hour']+1) >= 18 ) return;
		
		$today = date('Y-m-d');
		
		$row = mysqli_fetch_assoc( mysqli_query($this->connection, " SELECT lastdate FROM backup " ) );
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

				mysqli_query($this->connection, " UPDATE backup SET lastdate='$ddmmii' ");
			}

		}
	}
	protected function checkBackupFiles($maxAllowedFiles, $backupDir)
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
    public function statusesChangePermission(string $modelDate, int $component) : bool
    {
        if ( empty($modelDate) ) return false;

        // Сделал привязку к конкретной дате, старые модели сделаны до неё не будут проверены на наличие нужных статусов
        // это сделано что бы участки могли принять старые модели в ремонт, в случае чего
        $comp_date =  new \DateTime($modelDate) < new \DateTime("2020-08-05") ? false : true;

        // Путь прохождения модели.
        // user access => status id
        //Пример: 2 => 89 - 3д моделлер может менять статус только после Утверждения дизайна
        $changeStatusesAccess = [
            10 => 35, // утверждать эскиз, когда стоит статус эскиз
            2  => 89,  // 3д после утв. эскиза
            11 => 89, // 3д после утв. эскиза
            7 => 1,   // Валик может подписать только если модель на проверке
            9 => 89,  // подпись 3д техн. эскиза ( после подписи Валика)
            3 => 2,   // рост/поддержки после проверено
            5 => 5,   // доработка после выращено
            8 => 5,   // УчастокПДО после выращено
            4 => 5,   // Все остальные тоже после выращено
        ];

        $toShowStatuses = true;
        if ( $comp_date && ($component !== 1) && ($component !== 3) )
        {
            // Что бы предотвратить изменения статусов у модели, если не поставлен статус сдачи пред. участка
            // при постановке нужных статусов, некоторым людям начислятся деньги в кошельке работника

            if ( User::getAccess() !== 1 )
            {

                $toShowStatuses = array_key_exists(User::getAccess(),$changeStatusesAccess) && $this->isStatusPresent( (int)$changeStatusesAccess[User::getAccess()] );
            }

        }


        return $toShowStatuses;
    }

    /**
     * @param int $statusID
     * @param int $pos_id
     * @return bool
     * @throws \Exception
     */
    public function isStatusPresent(int $statusID = 0, int $pos_id = 0 ) : bool
    {
        if ( $pos_id === 0 )
            if ( trueIsset($this->id) )
            {
                $pos_id = $this->id;
            } else {
                throw new \Exception('Model ID is empty! Check stock table for this id: ' . $this->id, 0);
            }

        $query = $this->baseSql( "SELECT 1 FROM statuses WHERE pos_id='$pos_id' AND status='$statusID' " );
        if ( $query->num_rows ) return true;
        return false;
    }
	
}
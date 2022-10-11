<?php
namespace models;

use soffit\{ActiveQuery, db\Table};

class PushNotice extends General
{

    protected Table $pushnotice;
    /**
     * PushNotice constructor.
     * @throws \Exception
     */
    protected int $userID;

    public function __construct()
    {
        parent::__construct();

        $this->userID = User::getID();
        
        //$this->PUSHNOTICE = (new ActiveQuery('pushnotice'))->pushnotice;
        $this->registerTable('pushnotice');
        //$this->PUSHNOTICE = $this->PUSHNOTICE;

        $this->connectToDB();
    }

    public function checkPushNotice()
    {
	// почистим старые сперва
	$this->clearOldNotices();
		
        $noticesResult = [];
        
        $userId = 'a'.$this->userID.'a';
        //$pushRows = $this->findAsArray(" SELECT * FROM pushnotice where ip not like '%$userId%'" );
        $pushRows = $this->PUSHNOTICE->select(['*'])->where('ip','not like',"%$userId%")->exe();
        //debugAjax($pushRows1,'pushRows');

        //уходим если нет новых нотаций
        //if ( !$this->PUSHNOTICE->numRows ) 
        if ( !count($pushRows) ) return $userId;

        $c = 0;
        foreach( $pushRows as $pushRow )
        {
            $noticesResult[$c]['date'] = $pushRow['date'];

            // To proper compare. Session storage keeps strings.
            $noticesResult[$c]['not_id'] = (string)$pushRow['id']; 
            $noticesResult[$c]['pos_id'] = (string)$pushRow['pos_id'];

            $noticesResult[$c]['number_3d'] = $pushRow['number_3d'];
            $noticesResult[$c]['vendor_code'] = $pushRow['vendor_code'];
            $noticesResult[$c]['model_type'] = $pushRow['model_type'];
            $noticesResult[$c]['addEdit'] = $pushRow['addedit'];
            $noticesResult[$c]['fio'] = $pushRow['name'];
            $noticesResult[$c]['img_src'] = $pushRow['image'];

            foreach ( $this->statuses as $status )
            {
                if ( $status['id'] == $pushRow['status'] )
                {
                    $noticesResult[$c]['status'] = $status;
                    break;
                }
            }
            $c++;
        }

        return $noticesResult;
    }

    /**
     * @param int $id
     * @param int $isEdit
     * @param null $number_3d
     * @param null $vendor_code
     * @param null $model_type
     * @param null $date
     * @param null $status
     * @param null $creator_name
     * @return bool|int
     * @throws \Exception
     */
    public function addPushNotice(
        int $id, int $isEdit=1, $number_3d=null, $vendor_code=null, $model_type=null, $date=null, $status=null, $creator_name=null
    ) {
        if (!$id) return false;
        if (!$date) $date = date('Y-m-d');
        if (!$creator_name) $creator_name = User::getFIO();

        $modelData = [];
        if ( $isEdit !== 3 )
        {
            $sql = "SELECT s.number_3d as number_3d, s.vendor_code as vendor_code, s.model_type as model_type, 
                       s.status as status, i.main as img_main, i.sketch as img_sketch, i.detail as img_detail, 
                       i.onbody as img_onbody, i.pos_id as pos_id, i.img_name as img_name
                  FROM stock as s
                  LEFT JOIN images as i ON s.id = i.pos_id
                   WHERE s.id='$id' ";
            $stockQuery = $this->findAsArray($sql);

            // порядок выбора элементов
            $statusIMGOrder = ['img_main','img_sketch','img_detail','img_onbody'];
            
            foreach ( $statusIMGOrder as $statusName )
            {
                foreach ( $stockQuery as $stockData )
                {
                    if ( (int)$stockData[$statusName] === 1 )
                    {
                        $modelData = $stockData;
                        break(2);
                    }
                }
            }
            if ( empty($modelData) ) $modelData = $stockQuery[0];
        }
        

        //debug($modelData,'$stockQuery',1,1);

        if ( !$status ) $status = $modelData['status']??'';
        if ( !$number_3d ) $number_3d = $modelData['number_3d']??'';
        if ( !$vendor_code ) $vendor_code = $modelData['vendor_code']??'';
        if ( !$model_type ) $model_type = $modelData['model_type']??'';

        // полезем за картинкой
        $pathToImg='';
        if ( isset($modelData['img_name']) )
        {
            $file = $number_3d.'/'.$id.'/images/'.$modelData['img_name'];

            $pathToImg = _WORK_PLACE_ ? "http://192.168.0.245/Stock/" . $file : _stockDIR_HTTP_ . $file ;

            if ( !file_exists(_stockDIR_ . $file) ) $pathToImg = _stockDIR_HTTP_."default.jpg";
        }

        // Добавляем в базу
        $notRow = [
            'pos_id' =>$id,
            'number_3d'=>$number_3d,
            'vendor_code'=>$vendor_code,
            'model_type'=>$model_type,
            'image'=>$pathToImg,
            'status'=>$status,
            'ip'=>'',
            'name'=>$creator_name,
            'addedit'=>$isEdit,
            'date'=>$date
        ];
        $not_id = $this->PUSHNOTICE->insert('pushnotice', $notRow);
        if ( !$this->PUSHNOTICE->lastInsertID )
            throw new \Exception('Error to adding pushnotice in '. __METHOD__ ,599);
        
        // возьмем массив данных статуса по его ID
        $statusData = [];
        for( $i = 0; $i < count($this->statuses); $i++ )
        {
            if ( $this->statuses[$i]['id'] == $status )
            {
                $statusData = $this->statuses[$i];
                break;
            }
        }

        $message = [
            'newPushNotice' => [
                'date' => $date,
                'not_id' => $not_id,
                'pos_id' => $id,
                'number_3d' => $number_3d,
                'vendor_code' => $vendor_code,
                'model_type' => $model_type,
                'addEdit' => $isEdit,
                'fio' => $creator_name,
                'img_src' => $pathToImg,
                'status' => $statusData,
            ],
        ];

        if ( $this->PUSHNOTICE->lastInsertID ) // отправляем сообщение
        {
            //$oldErrorReporting = error_reporting(); // save error reporting level
            //error_reporting($oldErrorReporting & ~E_WARNING); // disable warnings
            set_error_handler(function(){return true;});
            $instance = @stream_socket_client($this->localSocket, $errNo, $errorMessage);
            restore_error_handler();
            //error_reporting($oldErrorReporting);
            if ( !$instance )
            {
                return false;
                //throw new Exception("addPushNotice() Can't connect to socket server! \n Error $errNo: " . $errorMessage);
            }

            $toUser = 'toAll';
            if ( _DEV_MODE_ ) $toUser = 'Быков В.А.';
            return fwrite($instance, json_encode(['user' => $toUser, 'message' => $message]) . "\n");

        } else {
            printf("addPushNotice() error message: %s\n");
            //$this->closeDB();
            return false;
        }
    }

    /**
     * @param $id
     * @return bool or string - client IP
     *
     * Добавляет IP посетителя в столбец уведомления, что бы не показывать это увед. ему снова
     */
    public function addIPtoNotice($id)
    {
        $newIpRow = 'a'.$this->userID.'a;';

        $addIPShowed = $this->PUSHNOTICE->sql(" UPDATE pushnotice SET ip=CONCAT(ip,'$newIpRow') WHERE id='$id' ");
        //$this->PUSHNOTICE->update('pushnotice', ['ip'=>"CONCAT(ip,'$newIpRow')"],['id','=',$id]);

        if (!$this->PUSHNOTICE->affectedRows)
        {
            printf( "addIPtoNotice() error: %s\n" );
            return false;
        }
        return $this->userID;
    }


    /**
     * @param $not_id - array массив уведомлений которые нужно закрыть
     * @return bool
     */
    public function addIPtoALLNotices($not_id)
    {
        $in = '';
        for( $i = 0; $i < count($not_id); $i++ )
            $in .= "'".$not_id[$i]."',";

        if ( !$in = trim($in,',') )
            return false;
        $newIpRow = 'a'.$this->userID.'a;';

        //CONCAT соединяет строки
        //REPLACE - заменяет в строкке
        $addIPShowed = $this->PUSHNOTICE->sql(" UPDATE pushnotice SET ip=CONCAT(ip,'$newIpRow') WHERE id IN ($in) ");
        //$this->PUSHNOTICE->update('pushnotice', ['ip'=>"CONCAT(ip,'$newIpRow')"],['id','IN',$in]);
        if ( $this->PUSHNOTICE->affectedRows ) return true;
        
        return false;
    }

    public function clearOldNotices()
    { 
        // удаляем записи которым больше 2х дней
        $date = new \DateTime('-2 days');
        $formDate = $date->format('Y-m-d');
        return $this->PUSHNOTICE->deleteFromTable('pushnotice', 'date', $formDate, '<');
    }





    /**  ДЛЯ REPAIR НОТАЙСОВ  */
    /**
     * @return mixed
     * @throws \Exception
     */
    public function getRepairNoticesData( string $forWho = '' )
    {
        $surname = User::getSurname();
		
        $sql = "SELECT s.id,s.number_3d,s.vendor_code,s.model_type,
                       i.img_name,
                       rp.sender,rp.descrNeed,
                       sr.glyphi,sr.title 
                FROM stock as s
                  LEFT JOIN images as i ON i.pos_id = s.id AND i.main='1'
                  LEFT JOIN repairs as rp ON rp.pos_id = s.id AND rp.toWhom LIKE '%$surname%' AND rp.status<>4
                  LEFT JOIN service_arr as sr ON sr.id = s.status
                      WHERE s.id IN
                        (SELECT r.pos_id FROM repairs as r WHERE r.toWhom LIKE '%$surname%' AND r.status<>4)";

		if ( $forWho === 'for_pdo' )
        {
			$sql = "SELECT s.id,s.number_3d,s.vendor_code,s.model_type,
                       i.img_name,
                       rp.sender,rp.descrNeed,
                       sr.glyphi,sr.title 
                FROM stock as s
                  LEFT JOIN images as i ON i.pos_id = s.id AND i.main='1'
                  LEFT JOIN repairs as rp ON rp.pos_id = s.id AND rp.status_date>'0000-00-00' AND rp.status<>4
                  LEFT JOIN service_arr as sr ON sr.id = s.status
                      WHERE s.id IN
                        (SELECT r.pos_id FROM repairs as r WHERE r.status_date>'0000-00-00' AND r.status<>4)";
        }
        $repNotices = $this->findAsArray($sql);
        foreach ( $repNotices as &$repNotice )
        {
            if ( trueIsset($repNotice['img_name']) )
            {
                $file = $repNotice['number_3d'].'/'.$repNotice['id'].'/images/'.$repNotice['img_name'];

                $pathToImg = _WORK_PLACE_ ? "http://192.168.0.245/Stock/" . $file : _stockDIR_HTTP_ . $file ;

                if ( !file_exists(_stockDIR_ . $file) )
                    $pathToImg = _stockDIR_HTTP_."default.jpg";

                $repNotice['img_name'] = $pathToImg;
            }
        }
        return $repNotices;
    }


    /**
     * @param array $repair
     * @return bool|int
     * @throws \Exception
     */
    public function pushRepairNotice( array $repair )
    {
        if ( empty($repair) || !trueIsset($repair['toWhom']) || !trueIsset($repair['pos_id']) )
            return false;

        //debugAjax($repair, 'Repair');
        $sql = "SELECT s.id,s.number_3d,s.vendor_code,s.model_type,
                       i.img_name,
                       sr.glyphi,sr.title 
                FROM stock as s
                  LEFT JOIN images as i ON i.pos_id = s.id AND i.main='1'
                  LEFT JOIN service_arr as sr ON sr.id = s.status
                WHERE s.id='{$repair['pos_id']}'";

        $repNotice = $this->findOne($sql);
        if ( trueIsset($repNotice['img_name']) )
        {
            $file = $repNotice['number_3d'].'/'.$repNotice['id'].'/images/'.$repNotice['img_name'];
            $pathToImg = _WORK_PLACE_ ? "http://192.168.0.245/Stock/" . $file : _stockDIR_HTTP_ . $file ;
            if ( !file_exists(_stockDIR_ . $file) )
                $pathToImg = _stockDIR_HTTP_."default.jpg";

            $repNotice['img_name'] = $pathToImg;
        }

        //debugAjax($repNotice, '$repNotices', END_AB);
        $message = [
            'newPushNoticeRepairs' => [
                'date' => $repair['date'],
                'pos_id' => $repair['pos_id'],
                'number_3d' => $repNotice['number_3d'],
                'vendor_code' => $repNotice['vendor_code'],
                'model_type' => $repNotice['model_type'],
                'sender' => $repair['sender'],
                'descrNeed' => $repair['descrNeed'],
                'img_name' => $repNotice['img_name'],
                'glyphi' => $repNotice['glyphi'],
                'title' => $repNotice['title'],
            ],
        ];

        set_error_handler(function(){return true;});
        $instance = @stream_socket_client($this->localSocket, $errNo, $errorMessage);
        restore_error_handler();
        if ( !$instance )
        {
            return false;
            //throw new Exception("addPushNotice() Can't connect to socket server! \n Error $errNo: " . $errorMessage);
        }

        //$toUser = _DEV_MODE_ ? 'Быков В.А.' : $repair['toWhom'];
        $toUser = $repair['toWhom'];
        return fwrite($instance, json_encode(['user' => $toUser, 'message' => $message]) . "\n");
    }
	
	
	/**  ДЛЯ 3Д НОТАЙСОВ  */
    /**
     * @param bool $all
     * @return mixed
     * @throws \Exception
     */
    public function getNew3DNoticesData( bool $all = false )
    {
        $surname = User::getSurname();

        $addQuery = "";
        if ( !$all )
            $addQuery = "s.modeller3D LIKE '%$surname%' AND";

        $sql = "SELECT s.id,s.number_3d,s.vendor_code,s.model_type, s.description, 
                       i.img_name,
                       sr.glyphi,sr.title 
                FROM stock as s
                  LEFT JOIN images as i ON i.pos_id = s.id
                  LEFT JOIN service_arr as sr ON sr.id = s.status
                WHERE $addQuery (s.status=89 OR s.status=8) GROUP BY s.id";

        $repNotices = $this->findAsArray($sql);
        foreach ( $repNotices as &$repNotice )
        {
            if ( trueIsset($repNotice['img_name']) )
            {
                $file = $repNotice['number_3d'].'/'.$repNotice['id'].'/images/'.$repNotice['img_name'];

                $pathToImg = _WORK_PLACE_ ? "http://192.168.0.245/Stock/" . $file : _stockDIR_HTTP_ . $file ;

                if ( !file_exists(_stockDIR_ . $file) )
                    $pathToImg = _stockDIR_HTTP_."default.jpg";

                $repNotice['img_name'] = $pathToImg;
            }
        }
        return $repNotices;
    }
	/**
	 * пока делать не будем
	 * сработает когда ставят статус "89 - Дизайн утвержден", либо меняют фамилю 3д модельера
     * @param array $repair
     * @return bool|int
     * @throws \Exception
     */
	 /*
    public function pushNew3DNotice( array $repair )
    {
        if ( empty($repair) || !trueIsset($repair['toWhom']) || !trueIsset($repair['pos_id']) )
            return false;

        //debugAjax($repair, 'Repair');
        $sql = "SELECT s.id,s.number_3d,s.vendor_code,s.model_type,
                       i.img_name,
                       sr.glyphi,sr.title 
                FROM stock as s
                  LEFT JOIN images as i ON i.pos_id = s.id AND i.main='1'
                  LEFT JOIN service_arr as sr ON sr.id = s.status
                WHERE s.id='{$repair['pos_id']}'";

        $repNotice = $this->findOne($sql);
        if ( trueIsset($repNotice['img_name']) )
        {
            $file = $repNotice['number_3d'].'/'.$repNotice['id'].'/images/'.$repNotice['img_name'];
            $pathToImg = _WORK_PLACE_ ? "http://192.168.0.245/Stock/" . $file : _stockDIR_HTTP_ . $file ;
            if ( !file_exists(_stockDIR_ . $file) )
                $pathToImg = _stockDIR_HTTP_."default.jpg";

            $repNotice['img_name'] = $pathToImg;
        }

        //debugAjax($repNotice, '$repNotices', END_AB);
        $message = [
            'newPushNoticeRepairs' => [
                'date' => $repair['date'],
                'pos_id' => $repair['pos_id'],
                'number_3d' => $repNotice['number_3d'],
                'vendor_code' => $repNotice['vendor_code'],
                'model_type' => $repNotice['model_type'],
                'sender' => $repair['sender'],
                'descrNeed' => $repair['descrNeed'],
                'img_name' => $repNotice['img_name'],
                'glyphi' => $repNotice['glyphi'],
                'title' => $repNotice['title'],
            ],
        ];

        set_error_handler(function(){return true;});
        $instance = @stream_socket_client($this->localSocket, $errNo, $errorMessage);
        restore_error_handler();
        if ( !$instance )
        {
            return false;
            //throw new Exception("addPushNotice() Can't connect to socket server! \n Error $errNo: " . $errorMessage);
        }

        //$toUser = _DEV_MODE_ ? 'Быков В.А.' : $repair['toWhom'];
        $toUser = $repair['toWhom'];
        return fwrite($instance, json_encode(['user' => $toUser, 'message' => $message]) . "\n");
    }
	*/

}
<?php
namespace Views\_SaveModel\Models;

use Views\_Globals\Models\User;
use Views\vendor\libs\classes\AppCodes;

class HandlerPrices extends Handler
{
    /**
     * HandlerPrices constructor.
     * @param int $id
     * @param \mysqli $connection
     * объект соединения прокинутый снаружи
     * @throws \Exception
     */
    //public function __construct( int $id = 0, \mysqli $connection = null )
    public function __construct( int $id = 0 )
    {
        parent::__construct($id);

//        if ( $connection )
//            $this->connection = $connection;
        $this->connection = $this->connectDBLite();

        $this->date = date('Y-m-d');
    }

    /**
     * @param string $priceType
     * @param string $author
     * @return int
     * @throws \Exception
     */
    public function addDesignPrices(string $priceType , string $author = '') : int
    {

        if ( $priceType === 'sketch' )
        {
            // Взяли фамилию автора из Инпута (по другому никак), нашли его ID из табл
            $userID = $this->getUserIDFromSurname( explode(" ", $author)[0] );
            if ( !$userID ) return -1;

            //$userID = User::getID();
            $rowGSDesign = $this->findAsArray("SELECT id as gs_id, grade_type as is3d_grade, description as cost_name, points as value FROM grading_system WHERE id IN ('91','92','99') ");

            foreach ( $rowGSDesign as &$designGrade )
            {
                $designGrade['id'] = '';
                $designGrade['user_id'] = $userID;
                $designGrade['value'] = (int)($designGrade['value'] * 100);
                $designGrade['pos_id'] = $this->id;
                $designGrade['date'] = $this->date;
            }

            //debugAjax($rowGSDesign,'$rowGSDesign', END_AB);

            return $this->insertUpdateRows($rowGSDesign, 'model_prices');
        }

        //Начислим за утвержденный дизайн 3D модели
        if ( $priceType === 'designOK' )
        {
            $sql = " UPDATE model_prices SET status='1',status_date='$this->date' WHERE pos_id='$this->id' AND (is3d_grade='2' AND gs_id='91') ";
            if ( $this->baseSql($sql) ) return 1;
            return -1;
        }

        return -1;
    }


    /**
     * @param array $ma3Dgs
     * @param $modeller3d
     * @return int
     * @throws \Exception
     */
    public function addModeller3DPrices( array $ma3Dgs, string $modeller3d ) : int
    {
        // Взяли моделлера из Инпута (по другому никак), нашли его ID из табл
        $userID = $this->getUserIDFromSurname( explode(" ", $modeller3d)[0] );
        if ( !$userID ) return -1;

        $mp3DIds = $ma3Dgs['mp3DIds'];
        $gs3Dpoints = $ma3Dgs['gs3Dpoints'];

        // пришло на удаление
        $toDell = $ma3Dgs['toDell'];
        if ( trueIsset($toDell) )
        {
            $inD  = '';
            foreach ($toDell as $toDellID) $inD .= $toDellID . ',';
            $inD = '(' . rtrim($inD, ',') . ')';
            $this->baseSql(" DELETE FROM model_prices WHERE id IN $inD ");
        }

        $gs3Dids = $ma3Dgs['gs3Dids'];
        if ( empty($gs3Dids) ) return -1;

        $in = '';
        foreach ($gs3Dids as $gs3Did) $in .= $gs3Did . ',';
        $in = trim($in, ',');
        if ( empty($in) )
            return -1;

        $rows = $this->findAsArray(" SELECT id as gs_id, grade_type as is3d_grade, work_name as cost_name FROM grading_system WHERE id IN ($in) ");
        foreach ($rows as $k => &$gsRow)
        {
            $gsRow['user_id'] = $userID;
            $gsRow['value'] = $gs3Dpoints[$k];
            $gsRow['id'] = $mp3DIds[$k];
            $gsRow['pos_id'] = $this->id;
            $gsRow['date'] = $this->date;
        }

        return $this->insertUpdateRows($rows, 'model_prices');
    }

    /**
     * @param string $priceType
     * @return bool|int|null
     * @throws \Exception
     */
    public function addTechPrices(string $priceType )
    {
        if ( $priceType === 'onVerify' ) // на проверке
        {
            // Возможно, должно выбрать из базы юзера с доступом MA_techCoord
            $userID = User::getID(); // Будет зачислено тому кто поставил статус, если у него есть MA_techCoord
            $queryGS = $this->findOne("SELECT id, grade_type, description, points FROM grading_system WHERE id='93'");
            $points = (int)($queryGS['points'] * 100);
            $cost_name = $queryGS['description'];
            $grade_type = $queryGS['grade_type'];

            $sql = "INSERT INTO model_prices ( user_id, gs_id, is3d_grade, cost_name, value, status, paid, pos_id, date ) 
				VALUES ('$userID', 93, '$grade_type','$cost_name','$points', 0, 0, '$this->id', '$this->date')";

            return $this->sql($sql);
        }

        if ( $priceType === 'SignedTechJew' )
        {
            // Узнать что это за модель!!!
            // что бы накинуть нужные оценки технолога! из табл grading_system 94 96 97 98
            $stock = $this->findOne("SELECT number_3d,vendor_code,model_type,labels FROM stock WHERE id='$this->id'");
            $material = $this->findOne("SELECT type FROM metal_covering WHERE pos_id='$this->id'");

            $hasBrill = mb_stripos( $stock['labels'], 'брилл' ) !== false;
            $hasDirectSmelting = mb_stripos( $stock['labels'], 'прямое лить' ) !== false;
            $in = '97,98'; // по умолчанию
            //if ( $material['type'] == 'Серебро' ) $in = '97,98';
            if ( $material['type'] == 'Серебро' &&  $hasDirectSmelting ) $in = '94';
            if ( $material['type'] == 'Золото' || $hasBrill ) $in = '96';
            $in = '(' . $in . ')';

            $rows = $this->findAsArray("SELECT id as gs_id, grade_type as is3d_grade, description as cost_name, points as value FROM grading_system WHERE id in $in");

            foreach ( $rows as &$gsRow )
            {
                $gsRow['user_id'] = User::getID(); // Будет зачислено тому кто поставил статус, если у него есть MA_techJew
                $gsRow['value'] = (int)($gsRow['value'] * 100);
                $gsRow['pos_id'] = $this->id;
                $gsRow['date'] = $this->date;
            }

            if ( $this->insertUpdateRows($rows, 'model_prices') !== -1 )
            {
                // После подписи валика зачислим за Сопровождение Славику
//                $sql = " UPDATE model_prices SET status='1', status_date='$this->date' WHERE pos_id='$this->id' AND (is3d_grade='2' AND gs_id='92') ";
                $sql = " UPDATE model_prices SET status='1', status_date='$this->date' WHERE pos_id='$this->id' AND is3d_grade='2' ";
                if ( $this->baseSql($sql) ) return 1;
                return -1;
            }
        }

        if ( $priceType === 'signed' ) // Проверено
        {
            //Добавим за сопровождение 3D моделей, если его не было
            $escort3D = $this->findOne("SELECT gs_id FROM model_prices WHERE pos_id='$this->id' AND gs_id='92' ", 'gs_id');
            if ( !$escort3D )
            {
                $userID = 4; // Куратор 3д дизайна (Дзюба),

                $queryGS = $this->findOne("SELECT id, grade_type, description, points FROM grading_system WHERE id='92'");
                $points = (int)($queryGS['points'] * 100);
                $cost_name = $queryGS['description'];
                $grade_type = $queryGS['grade_type'];

                $sql = "INSERT INTO model_prices ( user_id, gs_id, is3d_grade, cost_name, value, status, paid, pos_id, date ) 
				                     VALUES ('$userID', 92, '$grade_type','$cost_name','$points', 0, 0, '$this->id', '$this->date')";
                $this->sql($sql);
            }

            // зачислим  проверяющему и 3д модельеру и на всяк. случай Дизайнеру
            $sql = " UPDATE model_prices SET status='1', status_date='$this->date' WHERE pos_id='$this->id' AND (is3d_grade='4' OR is3d_grade='1' OR is3d_grade='2') ";
            if ( $this->baseSql($sql) ) return 1;
        }

        return -1;
    }

    /**
     * @param string $priceType
     * @return int
     * @throws \Exception
     */
    public function addPrint3DPrices( string $priceType ) : int
    {
        if ( $priceType === 'supports' ) // внесем прайс поддержек
        {
            $userID = User::getID(); // Будет зачислено тому кто поставил статус
            $queryGS = $this->findOne("SELECT id, grade_type, description, points FROM grading_system WHERE id='88'");
            $gradeID = (int)$queryGS['id'];
            $points = (int)($queryGS['points'] * 100);
            $cost_name = $queryGS['description'];
            $grade_type = $queryGS['grade_type'];

            $sql = "INSERT INTO model_prices ( user_id, gs_id, is3d_grade, cost_name, value, status, paid, pos_id, date ) 
				VALUES ('$userID', '$gradeID', '$grade_type','$cost_name','$points', 0, 0, '$this->id', '$this->date')";

            return $this->sql($sql);
        }

        if ( $priceType === 'printed' ) // зачислим прайсы стоимости роста и поддержек
        {
            $sql = " UPDATE model_prices SET status='1', status_date='$this->date' WHERE pos_id='$this->id' AND (is3d_grade='3' OR is3d_grade='5') ";
            if ( $this->baseSql($sql) ) return 1;
        }

        return -1;
    }

    /**
     * @param string $priceType
     * @param array $price
     * @param string $jewelerName
     * @return bool
     * @throws \Exception
     */
    public function addModJewPrices(string $priceType, array $price = [], string $jewelerName = '')
    {
        //debugAjax( $price,'$price' , END_AB );
        if ( $priceType === 'add' )
        {
            // Взяли моделлера из Инпута (по другому никак), нашли его ID из табл
            $userID = $this->getUserIDFromSurname( explode(" ", $jewelerName)[0] );
            if ( !$userID ) return -1;

            $queryGS = $this->findOne("SELECT id as gs_id, grade_type as is3d_grade, description as cost_name, points as value FROM grading_system WHERE id='95'");
            $jewPriceID = $this->findOne("SELECT id FROM model_prices WHERE pos_id='$this->id' AND (is3d_grade='6' AND status='0')");

            $queryGS['id'] = $jewPriceID['id']??'';
            //$queryGS['id'] = $price['id']??null;
            //if ( trueIsset($price['id']) ) $queryGS['id'] = (int)$price['id'];

            $queryGS['value'] = (int)$price['value'];
            $queryGS['user_id'] = $userID;
            $queryGS['pos_id'] = $this->id;
            $queryGS['date'] = $this->date;

            $row = [$queryGS];

            //debugAjax( $row,'$row' , END_AB );

            $this->insertUpdateRows($row, 'model_prices');
        }

        if ( $priceType === 'signalDone' )
        {
            $sql = " UPDATE model_prices SET status='1', status_date='$this->date' WHERE pos_id='$this->id' AND (is3d_grade='6' OR is3d_grade='7')";
            if ( $this->baseSql($sql) ) return 1;
        }

        return -1;
    }

    /**
     * для внесения стоимости роста ( пока не работает, возможно на будущее )
     * @param array $printingPrices
     * @return int
     * @throws \Exception
     */
    public function addPrintingPrices(array $printingPrices ) : int
    {
        // возьмет массив стоимостей роста из поста
        /*
        [ 'vax' => [ 0 => 89, 1 => 123], 'polymer' => []
        */
        $mpID = $this->findOne(" SELECT id FROM model_prices WHERE is3d_grade='5' ")['id'];

        $userID = User::getID(); // Будет зачислено тому кто поставил статус
        $gradeID = '';
        $points = '';
        if ( trueIsset($printingPrices['vax']) )
        {
            $gradeID = $printingPrices['vax'][0];
            $points = $printingPrices['vax'][1];
        }
        if ( trueIsset($printingPrices['polymer']) )
        {
            $gradeID = $printingPrices['polymer'][0];
            $points = $printingPrices['polymer'][1];
        }
        $queryGS = $this->findOne("SELECT grade_type, description FROM grading_system WHERE id='$gradeID'");
        $grade_type = $queryGS['grade_type'];
        $cost_name = $queryGS['description'];

        //если нет оценки по росту, то внесем её
        if ( !$mpID )
        {
            $sql = "INSERT INTO model_prices ( user_id, gs_id, is3d_grade, cost_name, value, status, paid, pos_id, date ) 
					VALUES ('$userID', '$gradeID', '$grade_type','$cost_name','$points', 0, 0, '$this->id', '$this->date')";
            if ( $this->sql($sql) ) return 1;
        } else {
            // иначе обновим её
            $sql = " UPDATE model_prices SET gs_id='$gradeID', cost_name='$cost_name', value='$points', date='$this->date'
			WHERE id='$mpID' ";
            if ( $this->baseSql($sql) ) return 1;
        }

        return -1;
    }

    /**
     * Для оплаты разных стоимостей через Менеджер оплат
     * @param array $priceIDs
     * @return array
     * @throws \Exception
     */
    public function payPrices( array $priceIDs ) : array
    {
        if ( !User::permission('paymentManager') )
            return ['error'=>AppCodes::getMessage(AppCodes::NO_PERMISSION_TO_PAY)];

        $in = "(";
        foreach ($priceIDs as $pID) $in .= $pID.',';
        $in = rtrim($in,',') . ")";

        $userID = User::getID();
        $sql = " UPDATE model_prices SET paid='1', paid_date='$this->date', who_paid='$userID' WHERE id IN $in ";
        $this->baseSql($sql);

        if ( mysqli_affected_rows($this->connection) )
            return ['success'=>AppCodes::getMessage(AppCodes::PAY_SUCCESS)];

        return ['error'=>AppCodes::getMessage(AppCodes::PAYING_ERROR)];
    }

}
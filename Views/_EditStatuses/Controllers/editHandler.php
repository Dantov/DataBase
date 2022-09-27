<?php
if( !isset($_POST['save']) ) {
        header("Location: ".$_SERVER["HTTP_REFERER"]);
        exit();
}

session_start();

date_default_timezone_set('Europe/Kiev');

if ( empty($_SESSION['selectionMode']['models']) )
{
    $result['done'] = false;
    echo json_encode($result);
    exit;
}

include('../../Glob_Controllers/classes/Handler.php');
if (!class_exists('PushNotice', false)) include( _globDIR_ . 'classes/PushNotice.php' );

$pn = new PushNotice();
$handler = new Handler(false, $_SERVER);

$conn = $handler->connectToDB();
if ( !$conn ) exit;

$status = $_POST['status'];
$date = $_POST['date'];
$creator_name = $_SESSION['user']['fio'];

$where = "WHERE id IN (";

foreach ( $_SESSION['selectionMode']['models'] as $model )
{
    $handler->setId( $model['id'] );
    $isStatusPresent = $handler->isStatusPresent($status); 
    // MA_techCoord
    if ( User::permission('MA_techCoord') )
    {
        if ( $isEdit )
        {
            if ( (int)$status === 1 ) // На проверке
                if ( !$isStatusPresent )
                    if ( $handler->addTechPrices('onVerify') === -1 ) $resp_arr['MA_techCoord'] = "not adding price";

            if ( (int)$status === 2 ) // Проверено
                if ( !$isStatusPresent )
                    if ( $handler->addTechPrices('signed') === -1 ) $resp_arr['MA_techCoord'] = "not adding price";
        }
    }
    if ( User::permission('MA_techJew') )
    {
        if ( $isEdit )
        {
            if ( (int)$status === 101 ) // Подписано технологом
                if ( !$isStatusPresent )
                    if ( $handler->addTechPrices('SignedTechJew') === -1 ) $resp_arr['MA_techCoord'] = "not adding price";
        }
    }
    if ( User::permission('MA_3dSupport') )
    {
        if ( $isEdit )
        {
            if ( (int)$status === 3 ) // Поддержки
                if ( $handler->addPrint3DPrices('supports') === -1 ) $resp_arr['MA_3dSupport'] = "not adding price";
        }
    }
    if ( User::permission('MA_3dPrinting') )
    {
        if ( $isEdit )
        {
            if ( (int)$status === 5 ) // Выращено
                if ( !$isStatusPresent )
                    if ( $handler->addPrint3DPrices('printed') === -1 ) $resp_arr['MA_3dPrinting'] = "not adding price";
        }
    }

    $statusT = [
        'pos_id' => $model['id'],
        'status' => $status,
        'creator_name' => $creator_name,
        'UPdate'   => $date
    ];
    $handler->addStatusesTable($statusT);
    
    $names = explode(' | ', $model['name']);

    //public function addPushNotice($id, $isEdit=1, $number_3d, $vendor_code, $model_type, $date, $status, $creator_name)
    $addPush = $pn->addPushNotice($model['id'], 2, $names[0], $names[1], $model['type'], $date, $status, $creator_name);
    if ( !$addPush )
    {
        $result['addPush'] = 'Error adding push notice';
    } else {
        $result['addPush'] = 'OK';
    }

    $where .= "{$model['id']},";
}

$where = trim($where,',');
$where .= ")";
$updateRow = "UPDATE stock SET status='$status', status_date='$date' $where";
$query = mysqli_query($conn, $updateRow);

$handler->closeDB();

if ( $query )
{
    $result['done'] = 1;
    $_SESSION['re_search'] = true; // флаг для репоиска
} else {
    $result['done'] = "false";
}

echo json_encode($result);

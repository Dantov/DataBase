<?php
namespace Views\_AddEdit\Controllers;

use Views\_AddEdit\Models\Handler;
use Views\_AddEdit\Models\HandlerPrices;
use Views\_Globals\Models\{ProgressCounter,PushNotice,SelectionsModel,User};
use Views\vendor\core\{Request,Sessions,Crypt};
use Views\vendor\libs\classes\AppCodes;

$request = new Request();
$progress = new ProgressCounter();

if ( $request->post('userName') && $request->post('tabID') )
    $progress->setProgress($request->post('userName'), $request->post('tabID'));

$progressCounter = 0;
$overallProcesses = 12;
//============= counter point ==============//
$progress->progressCount( ceil( ( ++$progressCounter * 100 ) / $overallProcesses ) );

$resp_arr = [];
$resp_arr['processes'] = [];

$id = (int)Crypt::strDecode($request->post('id'));
//debug($id);
$isEdit = (int)$request->post('edit') === 2 ? true : false ;

chdir(_stockDIR_);

$handler = new Handler($id);
try {
    $handler->connectToDB();
} catch ( \Exception $e) {
    exit($e->getMessage() . " connectToDB Failed in " . __METHOD__ );
}



$date = date("Y-m-d");
if ( $isEdit === true ) {
    $number_3d = $handler->setNumber_3d( strip_tags(trim($_POST['number_3d'])) );
} else {
    if ( (int)$request->post('edit') === 3 ) {
        $number_3d = $handler->setNumber_3d($request->post('number_3d'));
    } else {
        $number_3d = $handler->setNumber_3d();
    }
}

$permissions = $handler->permittedFields();

$vendor_code = strip_tags(trim($_POST['vendor_code']));
$model_type  = strip_tags(trim($_POST['model_type']));

$handler -> setVendor_code($vendor_code);
$handler -> setModel_typeEn($model_type);
$handler -> setModel_type($model_type);
$handler -> setIsEdit($isEdit);
$handler -> setDate($date);

// проверяем поменялся ли номер 3Д
if ( $isEdit === true ) $handler->checkModel();

//============= counter point ==============//
$progress->progressCount( ceil( ( ++$progressCounter * 100 ) / $overallProcesses ) );

// добавляем во все коиплекты артикул, если он есть
$handler->addVCtoComplects($vendor_code, $number_3d);

//$model_material = $handler->makeModelMaterial($_POST['model_material'],$_POST['samplegold'],$_POST['whitegold'],$_POST['redgold'],$_POST['eurogold']);
$str_labels = $handler->makeLabels($_POST['labels']);

// берем все остальное
$collection   = $handler->setCollections($_POST['collection']);
$author       = strip_tags(trim($_POST['author']));
$modeller3d   = strip_tags(trim($_POST['modeller3d']));
$jewelerName  = strip_tags(trim($_POST['jewelerName']));
$model_weight = strip_tags(trim($_POST['model_weight']));
$description  = $_POST['description'];
$size_range   = strip_tags(trim($_POST['size_range']));
$print_cost   = strip_tags(trim($_POST['print_cost']));
$model_cost   = strip_tags(trim($_POST['model_cost']));
$creator_name = $_SESSION['user']['fio'];
// число ID статуса
$status = (int)$_POST['status'];

$datas = "";
if ( !empty($number_3d) && $permissions['number_3d'] )
    $datas .= "number_3d='$number_3d',";

if ( $permissions['vendor_code']  )
    $datas .= "vendor_code='$vendor_code',";

if ( !empty($collection) && $permissions['collections'] )
    $datas .= "collections='$collection',";

if ( !empty($author) && $permissions['author'] )
    $datas .= "author='$author',";

if ( !empty($modeller3d) && $permissions['modeller3d'] )
    $datas .= "modeller3D='$modeller3d',";

if ( $permissions['jewelerName'] )
    $datas .= "jewelerName='$jewelerName',";

if ( !empty($model_type) && $permissions['model_type'] )
    $datas .= "model_type='$model_type',";

if ( $permissions['size_range'] )
    $datas .= "size_range='$size_range',";

if ( !empty($print_cost) && $permissions['print_cost'] )
    $datas .= "print_cost='$print_cost',";

if ( !empty($model_cost) && $permissions['model_cost'] )
    $datas .= "model_cost='$model_cost',";

if ( !empty($model_weight) && $permissions['model_weight'] )
    $datas .= "model_weight='$model_weight',";

if ( $permissions['description'] )
    $datas .= "description='".trim($description)."',";

if ( $permissions['labels'] )
    $datas .= "labels='$str_labels',";

$datas = trim($datas,',');

//============= counter point ==============//
$progress->progressCount( ceil( ( ++$progressCounter * 100 ) / $overallProcesses ) );

$isCurrentStatusPresent = null;

// добавляем новую модель
if ( $isEdit === false )
{
    $id = $handler -> addNewModel($number_3d, $model_type); // возвращает id новой модели при успехе
    if ( !$id ) exit('Error in addNewModel(). No ID is coming!');

    if ( $status === 0 ) $status = 35; // если забли поставить статус при доб. новой модели

    $datas .= ",status='$status',
                status_date='$date',
                creator_name='$creator_name',
                date='$date'
    ";

    //04,07,19 - вносим статус в таблицу statuses
    $statusT = [
        'pos_id'      => $id,
        'status'      => $status,
        'creator_name'=> $creator_name,
        'UPdate'      => $date
    ];
    $handler->addStatusesTable($statusT);
    $updateModelData = $handler->updateDataModel($datas, $id);
} else {
    // редактирование старой
    if ( $status && User::permission('statuses') )
        $isCurrentStatusPresent = $handler->isStatusPresent($status);

    $updateModelData = $handler->updateDataModel($datas);
    $handler->updateCreater($creator_name);      // добавим создателя, если его не было

    if ( $status && User::permission('statuses') )
        $handler->updateStatus($status, $creator_name); // обновляем статус
}

if ( !$updateModelData )
    exit('$updateModelData Error');

//============= counter point ==============//
$progress->progressCount( ceil( ( ++$progressCounter * 100 ) / $overallProcesses ) );




/// --------- Добавляем Стоимости ----------///
$payments = new HandlerPrices($id, $handler->connection);
require_once "paymentsController.php";
/// --------- END Стоимости ----------///





//-------------- материалы ----------------//
if ( $permissions['material'] )
{
    if ( !empty($_POST['mats']) )
    {
        $materialRows = $handler->makeBatchInsertRow($_POST['mats'], $id, 'metal_covering');
        //debug($materialRows,'makeBatchInsertRow',1,1);
		
        $resp_arr['materials']['insertUpdate'] = $handler->insertUpdateRows($materialRows['insertUpdate'], 'metal_covering');
        $resp_arr['materials']['delete'] = $handler->removeRows($materialRows['remove'], 'metal_covering');
    }
}
//-------------- материалы ----------------//




//---------- добавляем камни ----------//
if ( $permissions['gems'] )
{
    //если камни есть то добавляем их
    if ( !empty($request->post('gems')) )
    {
        $gems = $request->post('gems');
        $gemsRows = $handler->makeBatchInsertRow( $gems, $id, 'gems');
        $resp_arr['gems']['insertUpdate'] = $handler->insertUpdateRows($gemsRows['insertUpdate'], 'gems');
        $resp_arr['gems']['delete'] = $handler->removeRows($gemsRows['remove'], 'gems');
    }
}
//============= counter point ==============//
$progress->progressCount( ceil( ( ++$progressCounter * 100 ) / $overallProcesses ) );
// конец добавляем камни









//--------- добавляем картинки---------//
if ( $permissions['images'] )
{
    $imgRows = [];
    if ( !empty($_POST['image']['imgFor']) )
    {
        // Обновляем флажки на существующих картинках
        $imgRows = $handler->makeBatchImgInsertRow($_POST['image']);
        //debug($imgRows,'$imgRows',1);
        $handler->insertUpdateRows($imgRows['updateImages'], 'images');
    }

    if ( $imgCount = count($_FILES['UploadImages']['name']?:[]) )
    {
        if( !file_exists($number_3d) ) mkdir($number_3d, 0777, true);
        if( !file_exists($number_3d.'/'.$id) ) mkdir($number_3d.'/'.$id, 0777, true);
        if( !file_exists($number_3d.'/'.$id.'/images') ) mkdir($number_3d.'/'.$id.'/images', 0777, true);

        if ( $newImages = $handler->addImageFiles($_FILES['UploadImages'], $imgRows['newImages']) )
        {
            $insertImages = $handler->insertUpdateRows($newImages, 'images');
            if ( is_array($insertImages) ) debug($insertImages,'Error in insertUpdateRows',1);
        }

    }

}
//============= counter point ==============//
$progress->progressCount( ceil( ( ++$progressCounter * 100 ) / $overallProcesses ) );
// ----- конец добавляем картинки ----- //




// ----- Добавляем STL FILE ----- //
if ( !empty($_FILES['fileSTL']['name'][0]) && $permissions['stl'] )
{
    if( !file_exists($number_3d) ) mkdir($number_3d, 0777, true);
    if( !file_exists($number_3d.'/'.$id.'/stl') ) mkdir($number_3d.'/'.$id.'/stl', 0777, true);

    $querSTL = $handler -> addSTL($_FILES['fileSTL']);

    if ( $querSTL ) {

    } else {
        exit();
    }
}
//============= counter point ==============//
$progress->progressCount( ceil( ( ++$progressCounter * 100 ) / $overallProcesses ) );
//END Добавляем STL FILE


// ----- Добавляем 3dm FILE ----- //
if ( !empty($_FILES['file3dm']['name'][0]) && $permissions['stl'] )
{
    if( !file_exists($number_3d) ) mkdir($number_3d, 0777, true);
    if( !file_exists($number_3d.'/'.$id.'/3dm') ) mkdir($number_3d.'/'.$id.'/3dm', 0777, true);

    $query3dm = $handler->add3dm($_FILES['file3dm']);

}
//============= counter point ==============//
$progress->progressCount( ceil( ( ++$progressCounter * 100 ) / $overallProcesses ) );
// ----- END 3dm FILE ----- //


// ----- Добавляем Ai FILE ----- //
if ( !empty($_FILES['fileAi']['name'][0]) && $permissions['ai'] ) {

    if( !file_exists($number_3d) ) mkdir($number_3d, 0777, true);
    if( !file_exists($number_3d.'/'.$id.'/ai') ) mkdir($number_3d.'/'.$id.'/ai', 0777, true);

    $querSTL = $handler->addAi($_FILES['fileAi']);

    if ( $querSTL ) {

    } else {
        exit;
    }

}
//============= counter point ==============//
$progress->progressCount( ceil( ( ++$progressCounter * 100 ) / $overallProcesses ) );
//END Добавляем Ai FILE











// добавляем доп. артикулы
if ( $permissions['vc_links'] )
{
    $vc = array();
    $vc['dop_vc_name'] =  $_POST['dop_vc_name_'];
    $vc['num3d_vc']	   =  $_POST['num3d_vc_'];
    $vc['descr_dopvc'] =  $_POST['descr_dopvc_'];

    $quer_dop_vc = $handler->addDopVC( $vc );

    if ( $quer_dop_vc ) {

    } else {
        exit();
    }
}
//============= counter point ==============//
$progress->progressCount( ceil( ( ++$progressCounter * 100 ) / $overallProcesses ) );
//конец добавляем доп. артикулы

/// --------- Добавляем Описания ----------///
if ( $permissions['description'] )
{
    $addNotes = $handler->addNotes( $_POST['notes'] );
    $resp_arr['notes'] = $addNotes;
}

/// --------- Добавляем ремонты ----------///
if ( $permissions['repairs'] )
{
    if ( $permissions['repairs3D'] )
    {
        $repairResp = $handler->addRepairs( $_POST['repairs']['3d'] );
    }
    if ( $permissions['repairsJew'] )
    {
        $repairResp = $handler->addRepairs( $_POST['repairs']['jew'] );
    }
}
//============= counter point ==============//
$progress->progressCount( ceil( ( ++$progressCounter * 100 ) / $overallProcesses ) );
/// --------- END ремонты ----------///





// флаг для репоиска
if ( $_SESSION['searchFor'] ) $_SESSION['re_search'] = true;

$lastMess = "Модель добавлена";
if ( $isEdit === true ) $lastMess = "Данные изменены";
$resp_arr['isEdit'] = $isEdit;
$resp_arr['number_3d'] = $number_3d;
$resp_arr['model_type'] = $model_type;
$resp_arr['lastMess'] = $lastMess;
$resp_arr['id'] = $id;

$pn = new PushNotice();
$addPushNoticeResp = $pn->addPushNotice($id, $isEdit?2:1, $number_3d, $vendor_code, $model_type, $date, $status, $creator_name);
if ( !$addPushNoticeResp ) $resp_arr['errors']['pushNotice'] = 'Error adding push notice';

if ( !empty($_SESSION['selectionMode']['models']) )
{
    $selection = new SelectionsModel( new Sessions() );
    $selection->getSelectedModels();
}

//============= counter point ==============//
$progress->progressCount( 100 );

echo json_encode($resp_arr);
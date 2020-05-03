<?php
namespace Views\_AddEdit\Controllers;

use Views\_AddEdit\Models\Handler;
use Views\_Globals\Models\{ProgressCounter,PushNotice,SelectionsModel};
use Views\vendor\core\{Request,Sessions};

$request = new Request();

$progress = new ProgressCounter();
if ( isset($_POST['userName']) && isset($_POST['tabID']) )
{
    $progress->setProgress($_POST['userName'], $_POST['tabID']);
}

$manualProcesses = 3;

$imagesProcesses = 0;
if ( !empty($_FILES['UploadImages']['images'][0]) ) {
    $imagesProcesses = count( $_FILES['UploadImages']['images']?:[] );
}
//debug($_FILES,'$_FILES');

$stlProcesses = 0;
if ( !empty($_FILES['fileSTL']['name'][0]) ) {
    $stlProcesses = 1;
}

$repairsProcesses = count($_POST['repairs']['3d']?:[]) > 0 ? 1 : 0;
$stonesProcesses = count( $_POST['gemsName']?:[] ) > 0 ? 1 : 0;
$dopVCProcesses = count( $_POST['dop_vc_name_']?:[] ) > 0 ? 1 : 0;

$overalProcesses = $manualProcesses + $imagesProcesses + $stlProcesses + $repairsProcesses + $stonesProcesses + $dopVCProcesses;
$overalProgress = 0;
$progressCounter = 0;

$resp_arr = [];
$resp_arr['processes'] = [];

/*debug($manualProcesses,'$manualProcesses');
debug($imagesProcesses,'$imagesProcesses');
debug($stlProcesses,'$stlProcesses');
debug($stonesProcesses,'$stonesProcesses');
debug($dopVCProcesses,'$dopVCProcesses');
debug($repairsProcessec,'$repairsProcesses');
debug($overalProcesses,'$overalProcesses',1);*/

$id = (int)$request->post('id');
if ( (int)$request->post('edit') === 2 )  //isset($_POST['edit']) && (int)$_POST['edit'] === 2
{
    $isEdit = true;
} else {
    $isEdit = false; // новая модель!
}

//debug($_POST['mats'],'mats',1);

chdir(_stockDIR_);


$handler = new Handler($id);

if ( !$handler->connectToDB() ) exit;

$date = trim($_POST['date']);
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
$overalProgress =  ceil( ( ++$progressCounter * 100 ) / $overalProcesses );
$progress->progressCount( $overalProgress );
$resp_arr['processes']['manual'][] = $overalProgress;

// добавляем во все коиплекты артикул, если он есть
$handler -> addVCtoComplects($vendor_code, $number_3d);

// формируем строку model_material
//$model_material = $handler->makeModelMaterial($_POST['model_material'],$_POST['samplegold'],$_POST['whitegold'],$_POST['redgold'],$_POST['eurogold']);

// формируем строку model_covering
//$model_covering = $handler->makeModelCovering($_POST['rhodium'],$_POST['golding'],$_POST['blacking'],$_POST['rhodium_fill'],$_POST['onProngs'],$_POST['onParts'],$_POST['rhodium_PrivParts']);

$str_labels =  $handler->makeLabels($_POST['labels']);

// берем все остальное
$collection   = $handler->setCollections($_POST['collection']);
$author       = strip_tags(trim($_POST['author']));
$modeller3d   = strip_tags(trim($_POST['modeller3d']));
$jewelerName   = strip_tags(trim($_POST['jewelerName']));
$model_weight = strip_tags(trim($_POST['model_weight']));
$description  = $_POST['description'];
$size_range   = strip_tags(trim($_POST['size_range']));
$print_cost   = strip_tags(trim($_POST['print_cost']));
$model_cost   = strip_tags(trim($_POST['model_cost']));
$creator_name    = $_SESSION['user']['fio'];
$status = $_POST['status']; // число

$datas = "";

if ( !empty($number_3d) && $permissions['number_3d'] ) $datas .= "number_3d='$number_3d',";

if ( $permissions['vendor_code']  ) $datas .= "vendor_code='$vendor_code',";

if ( !empty($collection) && $permissions['collections'] ) $datas .= "collections='$collection',";

if ( !empty($author) && $permissions['author'] ) $datas .= "author='$author',";

if ( !empty($modeller3d) && $permissions['modeller3d'] ) $datas .= "modeller3D='$modeller3d',";
if ( $permissions['jewelerName'] ) $datas .= "jewelerName='$jewelerName',";

if ( !empty($model_type) && $permissions['model_type'] ) $datas .= "model_type='$model_type',";
if ( $permissions['size_range'] ) $datas .= "size_range='$size_range',";

if ( !empty($print_cost) && $permissions['print_cost'] ) $datas .= "print_cost='$print_cost',";
if ( !empty($model_cost) && $permissions['model_cost'] ) $datas .= "model_cost='$model_cost',";

//if ( $permissions['covering'] ) $datas .= "model_covering='$model_covering',";
//if ( $permissions['material'] ) $datas .= "model_material='$model_material',";

if ( !empty($model_weight) && $permissions['model_weight'] ) $datas .= "model_weight='$model_weight',";

if ( $permissions['description'] ) $datas .= "description='".trim($description)."',";
if ( $permissions['labels'] ) $datas .= "labels='$str_labels',";

$datas = trim($datas,',');

//т.е добавляем новую модель
if ( $isEdit === false )
{
    $id = $handler -> addNewModel($number_3d, $model_type); // возвращает id новой модели при успехе
    if ( !$id ) exit('Error in addNewModel(). No ID is coming!');

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
    // end
    //debug($datas,'$datas',1);
    $updateModelData = $handler->updateDataModel($datas, $id);
} else {
    // редактирование старой
    $updateModelData = $handler->updateDataModel($datas);
    $handler->updateCreater($creator_name);    // добавим создателя, если его не было
    $handler->updateStatus($status, $creator_name); // обновляем статус
}

if ( !$updateModelData )
{
    exit('$updateModelData Error');
}
//============= counter point ==============//
$overalProgress =  ceil( ( ++$progressCounter * 100 ) / $overalProcesses );
$progress->progressCount( $overalProgress );
$resp_arr['processes']['manual'][] = $overalProgress;


//-------------- материалы ----------------//
if ( $permissions['material'] )
{
    if ( !empty($_POST['mats']) )
    {
        $materialRows = $handler->makeBatchInsertRow($_POST['mats'], $id, 'metal_covering');
        //debug($materialRows,'makeBatchInsertRow',1);
        $resp_arr['materials']['insertUpdate'] = $handler->insertUpdateRows($materialRows['insertUpdate'], 'metal_covering');
        $resp_arr['materials']['delete'] = $handler->removeRows($materialRows['remove'], 'metal_covering');
    }
}
//-------------- материалы ----------------//



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

    //============= counter point ==============//
    $overalProgress =  ceil( ( ++$progressCounter * 100 ) / $overalProcesses );
    $progress->progressCount( $overalProgress );
    $resp_arr['processes']['picts'][] = $overalProgress;
}
// ----- конец добавляем картинки ----- //






// ----- Добавляем STL FILE ----- //
if ( !empty($_FILES['fileSTL']['name'][0]) && $permissions['stl'] )
{
    if( !file_exists($number_3d) ) mkdir($number_3d, 0777, true);
    if( !file_exists($number_3d.'/'.$id.'/stl') ) mkdir($number_3d.'/'.$id.'/stl', 0777, true);

    $querSTL = $handler -> addSTL($_FILES['fileSTL']);

    if ( $querSTL ) {

        //============= counter point ==============//
        $overalProgress =  ceil( ( ++$progressCounter * 100 ) / $overalProcesses );
        $progress->progressCount( $overalProgress );
        $resp_arr['processes']['stl'] = $overalProgress;

    } else {
        exit();
    }

}//END Добавляем STL FILE



// ----- Добавляем Ai FILE ----- //
if ( !empty($_FILES['fileAi']['name'][0]) && $permissions['ai'] ) {

    if( !file_exists($number_3d) ) mkdir($number_3d, 0777, true);
    if( !file_exists($number_3d.'/'.$id.'/ai') ) mkdir($number_3d.'/'.$id.'/ai', 0777, true);

    $querSTL = $handler->addAi($_FILES['fileAi']);

    if ( $querSTL ) {

    } else {
        exit;
    }

}//END Добавляем Ai FILE



//---------- добавляем камни ----------//
//$gem_rows_count = count($_POST['gemsName']?:[]);
if ( $permissions['gems'] )
{
    //если камни есть то добавляем их
    $gems = array();
    $gems['name']  = &$_POST['gemsName'];
    $gems['cut']   = &$_POST['gemsCut'];
    $gems['val']   = &$_POST['gemsVal'];
    $gems['diam']  = &$_POST['gemsDiam'];
    $gems['color'] = &$_POST['gemsColor'];

    $quer_gem = $handler -> addGems( $gems );

    if ( $quer_gem ) {
        //============= counter point ==============//
        $overalProgress =  ceil( ( ++$progressCounter * 100 ) / $overalProcesses );
        $progress->progressCount( $overalProgress );
        $resp_arr['processes']['gems'] = $overalProgress;
    } else {
        exit();
    }
} // конец добавляем камни




// добавляем доп. артикулы
if ( $permissions['vc_links'] )
{
    $vc = array();
    $vc['dop_vc_name'] =  $_POST['dop_vc_name_'];
    $vc['num3d_vc']	   =  $_POST['num3d_vc_'];
    $vc['descr_dopvc'] =  $_POST['descr_dopvc_'];

    $quer_dop_vc = $handler->addDopVC( $vc );

    if ( $quer_dop_vc ) {
        //============= counter point ==============//
        $overalProgress =  ceil( ( ++$progressCounter * 100 ) / $overalProcesses );
        $progress->progressCount( $overalProgress );
        $resp_arr['processes']['dopVC'] = $overalProgress;

    } else {
        exit();
    }
} //конец добавляем доп. артикулы



/// --------- Добавляем ремонты ----------///
if ( $permissions['repairs'] )
{
    if ( $permissions['repairs3D'] )
    {
        $repairResp = $handler->addRepairs( $_POST['repairs']['3d'] );
        if ( $repairResp ) {
            //============= counter point ==============//
            $overalProgress =  ceil( ( ++$progressCounter * 100 ) / $overalProcesses );
            $progress->progressCount( $overalProgress );
            $resp_arr['processes']['repairs'] = $overalProgress;

        }
    }
    if ( $permissions['repairsJew'] )
    {
        $repairResp = $handler->addRepairs( $_POST['repairs']['jew'] );
        if ( $repairResp ) {
            //============= counter point ==============//
            $overalProgress =  ceil( ( ++$progressCounter * 100 ) / $overalProcesses );
            $progress->progressCount( $overalProgress );
            $resp_arr['processes']['repairs'] = $overalProgress;

        }
    }
}
/// --------- END ремонты ----------///


$handler->closeDB();
// флаг для репоиска
if ( $_SESSION['searchFor'] ) $_SESSION['re_search'] = true;

$handler->unsetSessions();

$lastMess = "Модель добавлена";
if ( $isEdit === true ) $lastMess = "Данные изменены";
$resp_arr['isEdit'] = $isEdit;
$resp_arr['number_3d'] = $number_3d;
$resp_arr['model_type'] = $model_type;
$resp_arr['lastMess'] = $lastMess;
$resp_arr['id'] = $id;

$resp_arr['manualProcesses'] = $manualProcesses;
$resp_arr['imagesProcesses'] = $imagesProcesses;
$resp_arr['stlProcesses'] = $stlProcesses;
$resp_arr['stonesProcesses'] = $stonesProcesses;
$resp_arr['repairsProcesses'] = $repairsProcesses;
$resp_arr['dopVCProcesses'] = $dopVCProcesses;

$pn = new PushNotice();
$addPushNoticeResp = $pn->addPushNotice($id, $isEdit?2:1, $number_3d, $vendor_code, $model_type, $date, $status, $creator_name);
if ( !$addPushNoticeResp ) $resp_arr['errors']['pushNotice'] = 'Error adding push notice';

if ( !empty($_SESSION['selectionMode']['models']) )
{
    $selection = new SelectionsModel( new Sessions() );
    $selection->getSelectedModels();
}

//============= counter point ==============//
$overalProgress =  ceil( ( ++$progressCounter * 100 ) / $overalProcesses );
$progress->progressCount( 100 );
$resp_arr['processes']['manual'][] = $overalProgress;

echo json_encode($resp_arr);
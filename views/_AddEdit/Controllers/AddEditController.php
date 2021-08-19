<?php
namespace Views\_AddEdit\Controllers;

use Views\_AddEdit\Models\AddEdit;
use Views\_SaveModel\Models\Handler;
use Views\_Globals\Controllers\GeneralController;
use Views\_Globals\Models\User;
use Views\_SaveModel\Models\ImageConverter;
use Views\vendor\core\Crypt;
use Views\vendor\core\Files;
use Views\vendor\libs\classes\AppCodes;

class AddEditController extends GeneralController
{

    public $stockID = null;
    public $component = null;


    /**
     * @throws \Exception
     */
    public function beforeAction()
    {
        if ( !User::permission('addModel') && !User::permission('editModel') && !User::permission('editOwnModels') )
            $this->redirect('/main/');

        $request = $this->request;
        if ( $request->isAjax() )
        {
            try 
            {
                if ( $request->isPost() && $modelsTypeRequest = $request->post('modelsTypeRequest') )
                    $this->actionVendorCodeNames($modelsTypeRequest);

                if ( $request->isPost() && ( (int)$request->post('paid') === 1) )
                    $this->actionPaidRepair($request->post);

                if ( $request->post('deleteFile') )
                {
                    $fileName = $request->post('fileName');
                    $id = (int)$request->post('id');
                    $fileType = $request->post('fileType');
                    if ( !empty($fileName) && !empty($id) && !empty($fileType) )
                    {
                        $this->actionDeleteFile($id, $fileName, $fileType);
                    }
                }

                if ( $request->post('dellPosition') )
                    if ( $id = (int)$request->post('id') )
                        $this->actionDeletePosition($id);

                if ( $request->isGet() && $this->isQueryParam('masterLI') )
                    $this->getMasterLI( (int)$request->get('masterLI') );


            } catch (\TypeError | \Error | \Exception $e) {
                if ( _DEV_MODE_ )
                {
                    exit( json_encode([
                        'error'=>[
                            'message'=>$e->getMessage(),
                            'code'=>$e->getCode(),
                            'file'=>$e->getFile(),
                            'line'=>$e->getLine(),
                            'trace'=>$e->getTrace(),
                            'previous'=>$e->getPrevious(),
                        ]
                    ]) );
                } else {
                    exit( json_encode([
                        'error'=>[
                            'message'=>AppCodes::getMessage(AppCodes::SERVER_ERROR)['message'],
                            'code'=>$e->getCode(),
                        ],
                    ]) );
                }
            }
            exit;
        }

        $id = (int)$this->getQueryParam('id');
        if ( $id < 0 || $id > PHP_INT_MAX ) $this->redirect('/main/');
        $component = (int)$this->getQueryParam('component');
        if ( $component < 1 || $component > 3 ) $this->redirect('/main/');

        $this->stockID = $id;
        $this->component = $component;
    }

    /**
     * Action of this Controller
     * @throws \Exception
     */
    public function action()
    {
        $id = $this->stockID;
        $component = $this->component;
        $addEdit = new AddEdit($id);

        // список разрешенных для ред полей
        $permittedFields = $addEdit->permittedFields();

        $prevPage = $addEdit->setPrevPage();

        $dataTables = $addEdit->getDataTables();

        // Списки добавлений
        $data = $addEdit->getDataLi();
        $collLi        = $data['collections'];
        $authLi        = $data['author'];
        $mod3DLi       = $data['modeller3d'];
        $jewelerNameLi = $data['jeweler'];
        $modTypeLi     = $data['model_type'];
        $gems = $addEdit->getGemsLi();
        $gems_sizesLi = $gems['gems_sizes'];
        $gems_cutLi   = $gems['gems_cut'];
        $gems_namesLi = $gems['gems_names'];
        $gems_colorLi = $gems['gems_color'];
        $vc_namesLI = $addEdit->getNamesVCLi();

        $dataArrays = [
            'imgStat' => $addEdit->getStatLabArr('image'),
            'materialsData' => $this->parseMaterialsData($dataTables),
        ];
        $materialsData = $dataArrays['materialsData']['materials'];
        $coveringsData = $dataArrays['materialsData']['coverings'];
        $handlingsData = $dataArrays['materialsData']['handlings'];

        $modelPrices = [];

        
        if ( $component === 1 )  // чистая форма
        {
            if ( !User::permission('addModel') ) $this->redirect('/main/');
            $this->title = 'Добавить новую модель';
            $haveStl = 'hidden';
            $haveAi = 'hidden';

            // статус эскиз по умолчанию
            $row['status'] = $addEdit->getStatusCrutch(35, true);
            $statusesWorkingCenters = $addEdit->getStatus();
            $labels = $addEdit->getLabels();
        }

        if ( $component === 2 )  // редактирование
        {
            if ( $id > 0 )
                if ( !$addEdit->checkID($id) ) 
                    $this->redirect('/main/');

            $row = $addEdit->getGeneralData();
            $editBtn = false;
            if ( User::permission('editModel') )
            {
                $editBtn = true;
            } elseif ( User::permission('editOwnModels') ) {
                $userRowFIO = explode(' ', $_SESSION['user']['fio'])[0];
                if ( mb_stristr($row['author'], $userRowFIO) !== FALSE || 
                    mb_stristr($row['modeller3D'], $userRowFIO) !== FALSE || 
                    mb_stristr($row['jewelerName'], $userRowFIO) !== FALSE )
                    $editBtn = true;
            }
            if (!$editBtn) $this->redirect('/main/');


            $this->title = 'Редактировать ' . $row['number_3d'] . '-' . $row['model_type'];

            $complected = $addEdit->getComplected($component);
            $stl_file = $addEdit->getStl();
            $rhino_file = $addEdit->get3dm();
            $ai_file = $addEdit->getAi();

            $materials = $addEdit->getMaterials();
            $repairs = $addEdit->getRepairs();
            $countRepairs = $addEdit->countRepairs( $repairs );
            $notes = $addEdit->getDescriptions();

            $images  = $addEdit->getImages();
            //debug($images,'images',1);


            // Чтобы вызывать этот медод из Вида,
            // создадим такой костыль
            $setPrevImg = function( $image ) use (&$addEdit)
            {
                return $addEdit->origin_preview_ImgSelect($image);
            };

            $mainImage = $addEdit->origin_preview_ImgSelect($images[0]);
            foreach ( $images as $image )
            {
                if ( trueIsset($image['main']) )
                {
                    $mainImage = $addEdit->origin_preview_ImgSelect($image);
                    break;
                }
                if ( trueIsset($image['sketch']) )
                {
                    $mainImage = $addEdit->origin_preview_ImgSelect($image);
                    break;
                }
            }


            $gemsRow  = $addEdit->getGems();
            $dopVCs  = $addEdit->getDopVC();

            $num3DVC_LI = $addEdit->getNum3dVCLi( $dopVCs );

            $labels = $addEdit->getLabels($row['labels']);

            $statusesWorkingCenters = $addEdit->getStatus($row['status']['id']??0);

            $modelPrices = $addEdit->getModelPrices();
        }


        if ( $component === 3 ) // добавление комплекта
        {
            if ( !User::permission('addComplect') ) $this->redirect('/main/');

            $row = $addEdit->getGeneralData();
            $complected = $addEdit->getComplected($component);

            $this->title = 'Добавить комплект для ' . $row['number_3d'];

            $noStl = "";
            $haveStl = "hidden";
            $haveAi = 'hidden';
            $ai_hide = 'hidden';

            $materials = $addEdit->getMaterials(false,true);
            $gemsRow  = $addEdit->getGems(true);
            $dopVCs  = $addEdit->getDopVC(true);

            $num3DVC_LI = $addEdit->getNum3dVCLi( $dopVCs );

            $images  = $addEdit->getImages(true);
            $labels = $addEdit->getLabels($row['labels']);

            // Чтобы вызывать этот медод из Вида,
            // создадим такой костыль
            $setPrevImg = function( $image ) use (&$addEdit)
            {
                return $addEdit->origin_preview_ImgSelect($image);
            };

            $id = 0; // нужен 0 что бы добавилась новая модель

            // статус эскиз по умолчанию
            $row['status'] = $addEdit->getStatusCrutch(35, true);
            $statusesWorkingCenters = $addEdit->getStatus();
        }

        /* ===== JS includes ===== */
        $this->includeJSFile('ResultModal.js', ['defer','timestamp'] );
        $this->includeJSFile('deleteModal.js', ['defer','timestamp'] );
        $this->includeJSFile('add_edit.js', ['defer','timestamp'] );
        $this->includeJSFile('sideButtons.js', ['defer','timestamp','path'=>_views_HTTP_.'_Globals/js/'] );
        $this->includeJSFile('statusesButtons.js', ['defer','timestamp','path'=>_views_HTTP_.'_Globals/js/'] );
        $this->includeJSFile('submitForm.js', ['defer','timestamp'] );
        $this->includeJSFile('Repairs.js', ['defer','timestamp'] );
        if ( $permittedFields['files'] )
        {
            $this->includeJSFile('HandlerFiles.js', ['defer','timestamp'] );
            $fileTypes = ["image/jpeg", "image/png", "image/gif"];
            if ( $permittedFields['rhino3dm'] && empty($rhino_file) ) $fileTypes[] = ".3dm";
            if ( $permittedFields['stl'] && empty($stl_file) )
            {
                $fileTypes[] = ".stl";
                $fileTypes[] = ".mgx";
            }
            if ( $permittedFields['ai'] && empty($ai_file) )
            {
                $fileTypes[] = ".ai";
                $fileTypes[] = ".dxf";
            }

            $fileTypes = json_encode($fileTypes,JSON_UNESCAPED_UNICODE);

            $js = <<<JS
            let handlerFiles;
            window.addEventListener('load',function() {
              handlerFiles = new HandlerFiles( document.getElementById('drop-area'),document.getElementById('addImageFiles'),$fileTypes);
            },false);
JS;
            $this->includeJS($js);
        } else {
            $js = <<<JS
            let handlerFiles;
JS;
            $this->includeJS($js);
        }


        /* ===== PHP includes ===== */
        $compact1 = compact([
            'gems_sizesLi','gems_cutLi','gems_namesLi','gems_colorLi','vc_namesLI','num3DVC_LI','materialsData',
            'coveringsData','handlingsData','modTypeLi',
        ]);
        $this->includePHPFile('resultModal.php');
        $this->includePHPFile('deleteModal.php');
        $this->includePHPFile('num3dVC_input_Proto.php', $compact1);
        $this->includePHPFile('protoGemsVC_Rows.php', $compact1);
        $this->includePHPFile('upDownSaveSideButtons.php');


        $gradingSystem = $addEdit->gradingSystem();
        if ( User::permission('MA_modeller3D') )
        {
            $gradingSystem3D = $addEdit->gradingSystem(1);
            $gradingSystem3DRep = $addEdit->gradingSystem(8);
            $this->includePHPFile('grade3DModal.php', compact(['gradingSystem3D','gradingSystem3DRep']) );
        }
        if ( User::permission('modelAccount') )
            $this->includeJSFile('gradingSystem.js', ['defer','timestamp'] );


        /** Смотрим можно ли изменять статус **/
        $toShowStatuses = $addEdit->statusesChangePermission($row['date']??date("Y-m-d"), $component);


		$changeCost = in_array(User::getAccess(), [1,2,8,9,10,11]);

        $save = Crypt::strEncode("_".time()."!");
        $this->session->setKey('saveModel', $save);
        $compact2 = compact([
            'id','component','dellWD','prevPage','collLi','authLi','mod3DLi','jewelerNameLi','modTypeLi','gems_sizesLi','gems_cutLi','toShowStatuses','cT','dcT',
            'gems_namesLi','gems_colorLi','vc_namesLI','permittedFields','collections_len','mainImage','images','setPrevImg','notes','modelPrices','gradingSystem','countRepairs',
            'row','stl_file','rhino_file','ai_file','repairs','materials', 'gemsRow','dopVCs','num3DVC_LI','save','changeCost',
            'dataArrays','materialsData','coveringsData','handlingsData', 'statusesWorkingCenters','material','covering','labels','complected',
        ]);
        return $this->render('addEdit', $compact2);
    }

    protected function parseMaterialsData($dataTables)
    {
        $res = [];
        $materials = [];
        foreach ( $dataTables['metal_color'] as $metalColor )
        {
            $materials['colors'][] = $metalColor['name'];
        }
        $materials['colors'][] = "Нет";

        foreach ( $dataTables['model_material'] as $modelMaterials )
        {
            $namesProbes = explode(';', $modelMaterials['name']);
            $name = $namesProbes[0];
            $probe = $namesProbes[1];

            $materials['names'][$name] = $name;
            if ( !empty( $probe ) )
            {
                $materials['probes'][$name][] = $probe;
            } else {
                $materials['probes'][$name] = [];
            }
        }
        $materials['probes']['none'][] = "Нет";
        $res['materials'] = $materials;

        $coverings = [];
        foreach ( $dataTables['model_covering'] as $modelCoverings )
        {
            $namesCovers = explode(';', $modelCoverings['name']);
            $name = $namesCovers[0];
            $area = $namesCovers[1];

            $coverings['names'][$name] = $name;

            if ( !empty( $area ) )
            {
                $coverings['areas'][$area] = $area;
            }
        }
        $res['coverings'] = $coverings;
        $res['handlings'] = $dataTables['handling'];

        return $res;
    }

    /**
     * @param $modelsTypeRequest
     * @throws \Exception
     */
    protected function actionVendorCodeNames($modelsTypeRequest)
    {
        $handler = new Handler();
        $handler->connectDBLite();

        $resp = $handler->getModelsByType($modelsTypeRequest);

        $handler->closeDB();

        echo json_encode($resp);
    }

    /**
     * @param $post
     * @throws \Exception
     */
    protected function actionPaidRepair($post)
    {
        $repairPaid = (int)$post['paid'];
        $repairID = (int)$post['repairID'];
        $repairCost = (int)$post['cost'];

        if ($repairPaid === 1 && ($repairID < 0 || $repairID > 983495) ) {
            if ( $repairCost < 0 || $repairCost > 983495 )
            {
                echo json_encode(['error'=>'Wrong incoming data']);
            }
        }

        $handler = new Handler();
        $handler->connectDBLite();
        $handler->setDate( date('Y-m-d') );

        $result['done'] = $handler->setRepairPaid($repairID, $repairCost);

        echo json_encode($result);
    }

    /**
     * @param $modelID integer
     * @param $fileName string
     * @param $fileType string
     * @throws \Exception
     */
    protected function actionDeleteFile( $modelID, $fileName, $fileType )
    {
        if ( $modelID > 0 && $modelID < 999999 )
        {
            $handler = new Handler($modelID);
            $handler->connectDBLite();

            $result['id'] = $modelID;
            $result['fileName'] = $fileName;

            if ( !$result['text'] = $handler->deleteFile($fileName, $fileType) ) $result['text'] = 'Ошибка при удалении файла: ';

            $handler->closeDB();
            $this->session->setKey('re_search', true);

            echo json_encode($result);
        }
    }

    /**
     * @param $modelID
     * @throws \Exception
     */
    protected function actionDeletePosition( int $modelID ) : void
    {
        $resultDell['dell'] = 'Ошибка при удалении!';
        if ( !User::permission('dellModel') )
            exit( json_encode($resultDell) );


        if ( $modelID > 0 && $modelID < PHP_INT_MAX )
        {
            $handler = new Handler($modelID);
            $handler->connectToDB();
            if ( !$handler->checkID($modelID) )
                exit( json_encode($resultDell) );

            $resultDell = $handler->deleteModel();
            if ( $resultDell['success'] == 1 )
            {
                $pn = new \Views\_Globals\Models\PushNotice();
                $pn->addPushNotice($modelID, 3, $resultDell['number_3d'], $resultDell['vendor_code'], $resultDell['model_type'], $handler->date, $resultDell['status'], $handler->user['fio']);
                $this->session->setKey('re_search', true);
            }
            //$handler->closeDB();

            //$result['dell'] = $resultDell['dell'];
            exit( json_encode($resultDell) );
        }
    }

    /**
     * Проверим оценку на зачисление. Что бы не изменять оценки после зачисления
     * @param array $modelPrices
     * @param int $gradeType
     * @return bool
     */
    protected function isCredited( array $modelPrices = [], int $gradeType ) : bool
    {
        //debug($modelPrices,'$modelPrices',1);
        foreach ( $modelPrices as $modelPrice )
        {
            if ( (int)$modelPrice['is3d_grade'] !== $gradeType ) continue;
            if ( $modelPrice['status'] ) return true;
        }
        return false;
    }

    /**
     * @param int $which
     * @throws \Exception
     */
    protected function getMasterLI( $which )
    {
        $data = (new AddEdit())->getDataLi();
        $res = [];
        switch ( $which )
        {
            case 0:
                $res['li'] = $data['modeller3d'];
                break;
            case 1:
                $res['li'] = $data['jeweler'];
                break;
            case 2:
                $res['li'] = $data['jeweler'];
                break;
        }
        if ( $res )
            exit( json_encode($res) );

        exit( json_encode(['error'=>AppCodes::getMessage(AppCodes::MODEL_OUTDATED)]) );
    }

}
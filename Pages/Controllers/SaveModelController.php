<?php
namespace controllers;

use soffit\{Crypt,Files,Registry};
use libs\classes\{AppCodes,Validator};

use models\{ 
    PushNotice,SelectionsModel,User,Handler, 
    HandlerPrices, Condition, HandlerRepairs, 
    ImageConverter, SaveModelProgressCounter 
};


/**
 * Date: 02.12.2020
 * Time: 21:05
 *
 * Обрабатывает данные из формы сохранения модели
 */
class SaveModelController extends GeneralController
{

    /**
     * @var SaveModelProgressCounter()
     */
    public $progress;

    /**
     * @var Handler()
     */
    public $h;

    /**
     * @var
     * form fields uses here, in other methods
     */
    public $stockID;
    public $number3d;
    public $modelType;
    public $date;

    /**
     * @var array
     * данные необходимы для оплат прайсов
     */
    public $paymentsRequisite = [];

    /**
     * @var bool
     * Отражает присутствие полученного статуса в списке статусов этой модели
     */
    public $isCurrentStatusPresent;

    /**
     * @var array
     */
    public $response = [];

    /**
     * @throws \Exception
     */
    public function beforeAction() : void
    {
        if ( !$this->request->isAjax() )
            $this->redirect('main/');

        try {
            $saveModel = $this->session->getKey('saveModel');
            if ($saveModel) {
                $saveModel = Crypt::strDecode($saveModel);
                $save = Crypt::strDecode( $this->request->post('save') );
                if ($saveModel !== $save)
                    exit(json_encode(['error' => AppCodes::getMessage(AppCodes::MODEL_OUTDATED)]));

                /** Method success here!! **/
                $files = Files::instance();
                $img_fP = $files->count('updateImages');
                $stl_fP = $files->count('fileSTL');
                $rhino_fP = $files->count('file3dm');
                $ai_fP = $files->count('fileAi');
                $overallPr = $img_fP + $stl_fP + $rhino_fP + $ai_fP + 7;

                $this->progress = new SaveModelProgressCounter( $this->request->post('userName'),
                    $this->request->post('tabID'), $overallPr);

            } else {
                exit(json_encode(['error' => AppCodes::getMessage(AppCodes::MODEL_OUTDATED)]));
            }
        } catch ( \TypeError | \Error | \Exception $e ) {
            $this->serverError_ajax( $e );
        }
    }

    /**
     * @throws \Exception
     */
    public function action()
    {

        $progress = $this->progress;
        //============= CP ==============//
        $progress->count();

        chdir(_stockDIR_);
        $request = $this->request;

        //debugAjax($request->post,"post",END_AB);

        $this->stockID = (int)Crypt::strDecode($request->post('id'));
        $handler = $this->h = new Handler($this->stockID);
        try {
            $handler->connectToDB();
        } catch ( \Exception $e) {
            $this->serverError_ajax($e);
        }

        Condition::set( (int)$request->post('edit') );
        $isEdit = (int)$request->post('edit') === 2 ? true : false;

        $date = $this->date = date("Y-m-d");
        if ( Condition::isNew() )
            $this->number3d = $handler->setNumber_3d();

        $validator = Registry::init()->validator;//new Validator();
        $validator->reset();

        if ( Condition::isInclude() || Condition::isEdit() )
        {
            $this->number3d = $validator->validateField('number_3d',$request->post('number_3d'));
            if ( $validator->getLastError() )
                $this->validationFailedResponse($validator->getAllErrors());

            $this->number3d = $handler->setNumber_3d( $this->number3d );
        }

        $this->modelType = $validator->validateField('model_type',$request->post('model_type'));

        $handler->setModel_typeEn($this->modelType);
        $handler->setModel_type($this->modelType);

        $handler->setIsEdit($isEdit);
        $handler->setDate($date);

        // проверяем поменялся ли номер 3Д
        //if ( Condition::isEdit() ) $handler->checkModel();

        try {

            $textDataSave = $this->actionSaveData_Text();

            $pricesDataSave = $this->actionSaveData_Prices(new HandlerPrices($this->stockID));

            $repairsDataSave = $this->actionSaveData_Repairs();

            $filesDataSave = $this->actionSaveData_Files();

        } catch ( \TypeError | \Error | \Exception $e) {
            $this->serverError_ajax($e);
        }

    }

    public function afterAction()
    {
        parent::afterAction();

        $this->session->dellKey('saveModel');

        $this->actionResponse();
    }


    /**
     * @throws \Exception
     */
    protected function actionSaveData_Text()
    {
        $request = $this->request;
        $handler = $this->h;

        //debugAjax($request->post,'post',END_AB);
        $validator = Registry::init()->validator; //new Validator();

        $insertData = "";
        // уже отвалидированы
        if ( User::permission('number_3d'))
            $insertData .= "number_3d='$this->number3d',";
        if ( User::permission('model_type'))
            $insertData .= "model_type='$this->modelType',";

        // Валидируем все остальное
        if ( User::permission('vendor_code') && $vendor_code = $request->post('vendor_code'))
        {
            $vendor_code = $validator->validateField('vendor_code', $vendor_code);
            $handler->setVendor_code($vendor_code);
            $this->paymentsRequisite['vendor_code'] = $vendor_code;
            $insertData .= "vendor_code='$vendor_code',";
        }
        if ( User::permission('author') && $author =$request->post('author'))
        {
            $author = $validator->validateField('author',$author);
            $this->paymentsRequisite['author'] = $author;
            $insertData .= "author='$author',";
        }
        if ( User::permission('modeller3d') && $modeller3d = $request->post('modeller3d'))
        {
            $modeller3d = $validator->validateField('modeller3d', $modeller3d);
            $this->paymentsRequisite['modeller3d'] = $modeller3d;
            $insertData .= "modeller3D='$modeller3d',";
        }
        if ( User::permission('jewelerName') && $jewelerName = $request->post('jewelerName'))
        {
            $jewelerName = $validator->validateField('jewelerName', $jewelerName);
            $this->paymentsRequisite['jewelerName'] = $jewelerName;
            $insertData .= "jewelerName='$jewelerName',";
        }
        if ( User::permission('size_range') && $size_range =$request->post('size_range') )
        {
            $size_range = $validator->validateField('size_range', $size_range);
            $insertData .= "size_range='$size_range',";
        }
        if ( User::permission('print_cost') && $print_cost=$request->post('print_cost') )
        {
            $print_cost   = $validator->validateField('print_cost', $print_cost);
            $insertData .= "print_cost='$print_cost',";
        }
        if ( User::permission('model_cost') && $model_cost = $request->post('model_cost') )
        {
            $model_cost   = $validator->validateField('model_cost', $model_cost);
            $insertData .= "model_cost='$model_cost',";
        }
        if ( User::permission('model_weight') )
        {
            $model_weight =$request->post('model_weight');
            $model_weight = $validator->validateField('model_weight', $model_weight);
            $insertData .= "model_weight='$model_weight',";
        }
        if ( User::permission('description') && $description  =$request->post('description') )
        {
            $description  = $validator->validateField('description', $description);
            $insertData .= "description='$description',";
        }

        // Валидация массивов данных
        if ( User::permission('labels') && $str_labels = $request->post('labels') )
        {
            $validator->validateFields('labels', $str_labels );
            $str_labels  = $handler->makeLabels($str_labels);
            $insertData .= "labels='$str_labels',";
        }
        if ( User::permission('collections') && $collection = $request->post('collections') )
        {
            $validator->validateFields('collections', $collection );
            $collection   = $handler->setCollections( $collection );
            $insertData .= "collections='$collection',";
        }
        if ( User::permission('material') && $mats = $request->post('mats') ) {
            $validator->validateFields('mats', $mats);
        }
        if ( User::permission('gems') && $gems = $request->post('gems') ) {
            $validator->validateFields('gems', $gems);
        }
        if ( User::permission('vc_links') && $vc_l = $request->post('vc_links') ) {
            $validator->validateFields('vc_links', $vc_l);
        }

        // При наличии ошибок валидации - выходим. Отправим тексты ошибок в браузер
        if ( $validator->getLastError() )
            $this->validationFailedResponse($validator->getAllErrors());
        // Validation END


        //debugAjax($request->post('vc_links'),'vc_links',END_AB);

        // ID статуса
        $status  = (int)$request->post('status');
        $this->paymentsRequisite['status'] = $status;
        $creator_name = User::getFIO();
        $insertData = trim($insertData,',');

        /** сохраняем новую модель **/
        $updateModelData = false;
        if ( Condition::isNew() || Condition::isInclude() )
        {
            $this->stockID = $handler->addNewModel($this->number3d, $this->modelType); // возвращает id новой модели при успехе
            if ( !$this->stockID )
                throw new \Exception('Error in addNewModel(). No ID is coming!',198);

            // если забли поставить статус при доб. новой модели
            if ( $status === 0 ) $status = 35;

            $insertData .= ",status='$status',
                status_date='$this->date',
                creator_name='$creator_name',
                date='$this->date'
            ";

            //04,07,19 - вносим статус в таблицу statuses
            $statusT = [
                'pos_id'      => $this->stockID,
                'status'      => $status,
                'creator_name'=> $creator_name,
                'UPdate'      => date("Y-m-d H:i:s"),//$this->date
            ];
            $handler->addStatusesTable($statusT);

            $updateModelData = $handler->updateDataModel($insertData);
        }

        /** Редактируем **/
        if ( Condition::isEdit() )
        {
            // Проверяем поменялся ли номер 3Д, чтобы переместить файлы модели в др. папку
            if ( $oldNum3D = $handler->checkModel3DNum() )
            {
                if ( $handler->moveModelFiles($oldNum3D) )
                    $this->response['movedModelFiles'] = "Изменился номер 3Д. Файлы перемещены.";
            }

            if ( $status && User::permission('statuses') )
                $this->isCurrentStatusPresent = $handler->isStatusPresent($status);

            $handler->updateDataModel($insertData);

            // добавим создателя, если его не было
            $handler->updateCreater($creator_name);

            // обновляем статус
            if ( $status && User::permission('statuses') )
                $handler->updateStatus($status, $creator_name);
        }

        // добавляем во все комплекты артикул, если он есть
        if ( User::permission('vendor_code') && isset($vendor_code) )
            $handler->addVCtoComplects($vendor_code);

        // Доп. Описания
        if ( User::permission('description') ) {
            if ( $notes = $request->post('notes') )
                $this->response['notes'] = $handler->addNotes($notes);
        }

        //============= CP ==============//
        $this->progress->count();

        $this->dataSave_Materials();
        $this->dataSave_Gems();
        $this->dataSave_VendorCodeLinks();

        return $updateModelData;
    }

    /**
     * МАТЕРИАЛЫ
     * @throws \Exception
     */
    protected function dataSave_Materials() : bool
    {
        if ( !User::permission('material') ) return false;

        $request = $this->request;
        $mats = $request->post('mats');
        if ( empty($mats) ) return false;

        $materialRows = $this->h->makeBatchInsertRow($mats, $this->stockID, 'metal_covering');
        if ( !$materialRows ) return false;

        //debug($materialRows,'makeBatchInsertRow',1,1);
        //debugAjax($materialRows,'makeBatchInsertRow',END_AB);

        $this->response['materials']['insertUpdate'] = $this->h->insertUpdateRows($materialRows['insertUpdate'], 'metal_covering');
        $this->response['materials']['delete'] = $this->h->removeRows($materialRows['remove'], 'metal_covering');

        //============= CP ==============//
        $this->progress->count();

        return true;
    }

    /**
     * КАМНИ
     * @throws \Exception
     */
    protected function dataSave_Gems()
    {
        if ( !User::permission('gems') ) return false;

        $request = $this->request;
        $gems = $request->post('gems');
        if ( empty($gems) ) return false;

        $gemsRows = $this->h->makeBatchInsertRow( $gems, $this->stockID, 'gems');
        if ( !$gemsRows ) return false;

        $this->response['gems']['insertUpdate'] = $this->h->insertUpdateRows($gemsRows['insertUpdate'], 'gems');
        $this->response['gems']['delete'] = $this->h->removeRows($gemsRows['remove'], 'gems');


        //============= CP ==============//
        $this->progress->count();
        return true;
    }

    /**
     * ДОП. АТРИКУЛЫ
     * @throws \Exception
     */
    protected function dataSave_VendorCodeLinks()
    {
        if ( !User::permission('vc_links') ) return false;

        $request = $this->request;
        $vcl = $request->post('vc_links');

        if ( empty($vcl) ) return false;

        $vclRows = $this->h->makeBatchInsertRow( $vcl, $this->stockID, 'vc_links');

        if ( !$vclRows ) return false;

        $this->response['vc_links']['insertUpdate'] = $this->h->insertUpdateRows($vclRows['insertUpdate'], 'vc_links');
        $this->response['vc_links']['delete'] = $this->h->removeRows($vclRows['remove'], 'vc_links');

        //============= CP ==============//
        $this->progress->count();
        return true;
    }

    /**
     * ФАЙЛЫ
     * @throws \Exception
     */
    protected function actionSaveData_Files()
    {
        if ( !User::permission('files') )
            return;

        $request = $this->request;
        $files = Files::instance();

        if ( User::permission('images') )
        {
            $imgRows = [];
            $images = $request->post('image');

            //debugAjax($images,'$images',END_AB);

            if ( !empty($images['imgFor']) )
            {
                // Обновляем статусы существующих картинок
                $imgRows = $this->h->makeBatchImgInsertRow($images);
                $this->h->insertUpdateRows($imgRows['updateImages'], 'images');
            }

            if ( $files->count('UploadImages') || Condition::isInclude() )
            {
                if( !file_exists($this->number3d) )
                    mkdir($this->number3d, 0777, true);

                $path = $this->number3d.'/'.$this->stockID.'/images/';
                if( !file_exists($path) )
                    mkdir($path, 0777, true);

                $i = 0;
                $newImages = $imgRows['newImages'];
                if ( !empty($newImages[0]['img_name']) && $newImages[0]['sketch'] == 1 )
                {
                    $this->h->addIncludedSketch($newImages);
                    $i++;
                }

                $uploadImages = $files->makeHUA('UploadImages');
                foreach ( $uploadImages as $uploadImage )
                {
                    if ( $fileName = $this->h->uploadImageFile($uploadImage, true) )
                    {
                        $newImages[$i]['img_name'] = $fileName;
                    }
                    $i++;
                    //============= CP ==============//
                    $this->progress->count();
                }

                // вносим данные новых картинок
                $insertImages = $this->h->insertUpdateRows($newImages, 'images');
                if ( $insertImages === -1 )
                    throw new \Exception('Error in insertUpdateRows',1);
            }

        }


        if ( User::permission('stl') )
        {
            if ( $files->has('fileSTL') )
            {
                if( !file_exists($this->number3d) )
                    mkdir($this->number3d, 0777, true);

                $path = $this->number3d.'/'.$this->stockID.'/stl/';
                if( !file_exists($path) )
                    mkdir($path, 0777, true);

                $zipData = $this->h->openZip($path);

                $stlFileNames = [];
                $uploadStl = $files->makeHUA('fileSTL');
                foreach ( $uploadStl as $stl )
                {
                    $zipData['stl'] = $stl;
                    if ( $stlFileNames[] = $this->h->uploadStlFile( $zipData, $path ) )
                        //============= CP ==============//
                        $this->progress->count();
                }
                // closing Zip
                $this->h->insertStlData( $stlFileNames, $zipData );
            }

            if ( $files->has('file3dm') )
            {
                if( !file_exists($this->number3d) )
                    mkdir($this->number3d, 0777, true);

                $path = $this->number3d.'/'.$this->stockID.'/3dm/';
                if( !file_exists($path) )
                    mkdir($path, 0777, true);

                $this->h->add3dm( $files->get('file3dm') );

                //============= CP ==============//
                $this->progress->count();
            }
        }

        if ( User::permission('ai') )
        {
            if ( $files->has('fileAi') )
            {
                if( !file_exists($this->number3d) )
                    mkdir($this->number3d, 0777, true);

                $path = $this->number3d.'/'.$this->stockID.'/ai/';
                if( !file_exists($path) )
                    mkdir($path, 0777, true);

                $this->h->addAi( $files->get('fileAi') );
                //============= CP ==============//
                $this->progress->count();
            }
        }

    }

    /**
     * @param HandlerPrices $payments
     * @throws \Exception
     */
    public function actionSaveData_Prices( HandlerPrices $payments )
    {
        $status = $this->paymentsRequisite['status']??'';
        $author = $this->paymentsRequisite['author']??'';
        $modeller3d = $this->paymentsRequisite['modeller3d']??'';
        $jewelerName = $this->paymentsRequisite['jewelerName']??'';

        /** Добавим стоимости дизайна только для новых моделей. Запуститься только с правами Дизайнера (т.е Дзюба)**/
        if (User::permission('MA_design'))
        {
            if ( Condition::isNew() || Condition::isInclude() )
                if ( $status === 35 )
                    if ( !$this->isCurrentStatusPresent )
                        if ( $payments->addDesignPrices('sketch', $author) === -1 )
                            $this->response['MA_design'] = AppCodes::getMessage(AppCodes::NOTHING_DONE)['message'];
        }

        /** зачислили дизайнеру, за утвержденный дизайн **/
        if ( User::permission('paymentManager') && User::permission('artCouncil') )
        {
            if ( $status === 89 )
                if ( !$this->isCurrentStatusPresent && $payments->isStatusPresent(35) )
                    if ($payments->addDesignPrices('designOK') === -1)
                        $this->response['MA_design'] = "not adding price";
        }

        /** Вставка оценок моделироания */
        if ( User::permission('MA_modeller3D') )
        {
            // инициируем вставку оценок моделироания только ели есть MA_modeller3D
            // и имя FIO моделлера == FIO юзера
            if ( $this->request->post('ma3Dgs') && trueIsset($modeller3d) )
                $payments->addModeller3DPrices($this->request->post('ma3Dgs'), $modeller3d);
        }

        if (User::permission('MA_techCoord'))
        {
            if ( Condition::isEdit() )
            {
                if ( $status === 1 ) // На проверке
                    if ( !$this->isCurrentStatusPresent )   // && $payments->isStatusPresent(47) 47 -'Готово 3D'
                        if ($payments->addTechPrices('onVerify') === -1)
                            $this->response['MA_techCoord'] = "not adding price";

                if ( $status === 2 ) // Проверено
                    if (!$this->isCurrentStatusPresent && $payments->isStatusPresent(101) && $payments->isStatusPresent(1) )
                        if ($payments->addTechPrices('signed') === -1)
                            $this->response['MA_techCoord'] = "not adding price";
            }
        }

        if (User::permission('MA_techJew')) { // Технолог Юв (Валик)
            if ( Condition::isEdit() ) {
                if ( $status === 101 ) // Подписано технологом
                    if ( !$this->isCurrentStatusPresent && $payments->isStatusPresent(89) && $payments->isStatusPresent(1)  )
                        if ($payments->addTechPrices('SignedTechJew') === -1)
                            $this->response['MA_techJew'] = AppCodes::getMessage(AppCodes::NOTHING_DONE)['message'];
            }
        }

        if (User::permission('MA_3dSupport'))
        {
            if ( Condition::isEdit() )
            {
                if ( $status === 3 ) // Поддержки Убрал $this->isCurrentStatusPresent. Может выставлять поддержки много раз
                    if ( $payments->isStatusPresent(2) )
                        if ($payments->addPrint3DPrices('supports') === -1)
                            $this->response['MA_3dSupport'] = "not adding price";
            }
        }

        if (User::permission('MA_3dPrinting'))
        {
            if ( Condition::isEdit() ) {
                // На будущее
//        if ( $handler->isStatusPresent(2) ) // Есть Подписано - зачисляем стоимость печати
//            $handler->addPrintingPrices( $_POST['printingPrices']??[] );

                if ( $status === 5 ) //Выращено
                    if (!$this->isCurrentStatusPresent && $payments->isStatusPresent(2) )
                        if ($payments->addPrint3DPrices('printed') === -1)
                            $this->response['MA_3dPrinting'] = "not adding price";
            }
        }

        /** инициируем вставку оценок модельера-доработчика  */
        if (User::permission('MA_modellerJew'))
        {
            if ( Condition::isEdit() )
            {
                if ( !trueIsset($jewelerName) )
                    $this->validationFailedResponse( ['Не заполнено поле "Модельер-доработчик" '] );

                if ( trueIsset($this->request->post('modellerJewPrice')) )
                    $payments->addModJewPrices('add', $this->request->post('modellerJewPrice'), $jewelerName );
            }
        }

        //if (User::permission('MA_modellerJew')) Возможно по UserAccess 8 участок ПДО
        if ( $status === 41 ) //На сбыте
            if ( !$this->isCurrentStatusPresent && $payments->isStatusPresent(89) && $payments->isStatusPresent(101) )
                if ($payments->addModJewPrices('signalDone') === -1)
                    $this->response['MA_3dPrinting'] = AppCodes::getMessage(AppCodes::NOTHING_DONE)['message'];
    }


    /**
     * @throws \Exception
     */
    protected function actionSaveData_Repairs()
    {
        $request = $this->request;
        $repairs = $request->post('repairs');
        $repairsResponse = [];

        if ( empty($repairs) ) return $repairsResponse;
        
        if ( !User::permission('repairs') )
            return $repairsResponse;

        $hR = new HandlerRepairs($this->stockID);
        

        if ( User::permission('repairs3D') && isset($repairs['3d']) )
            $repairsResponse['3d'] = $hR->addRepairs( $repairs['3d'] );

        if ( User::permission('repairsJew') && isset($repairs['jew']) )
            $repairsResponse['jew'] = $hR->addRepairs( $repairs['jew'] );

        if ( User::permission('repairsProd') && isset($repairs['prod']) )
            $repairsResponse['prod'] = $hR->addRepairs( $repairs['prod'] );

        //============= CP ==============//
        $this->progress->count();

        return $repairsResponse;
    }


    /**
     * Sending response via Ajax
     * @throws \Exception
     */
    protected function actionResponse()
    {
        // флаг для репоиска
        if ( $this->session->getKey('searchFor') )
            $this->session->setKey('re_search',true);

        $lastMess = "Модель добавлена";
        if ( Condition::isEdit() ) $lastMess = "Данные изменены";

        $this->response['isEdit'] = Condition::isEdit();
        $this->response['number_3d'] = $this->number3d;
        $this->response['model_type'] = $this->modelType;
        $this->response['lastMess'] = $lastMess;
        $this->response['id'] = $this->stockID;

        $status = $this->paymentsRequisite['status']??'';
        $vendor_code = $this->paymentsRequisite['vendor_code']??'';

        $pn = new PushNotice();
        $addPushNoticeResp = $pn->addPushNotice($this->stockID,
            Condition::isEdit()?2:1, $this->number3d, $vendor_code, $this->modelType, date('Y-m-d'), $status, User::getFIO() );

        if ( !$addPushNoticeResp )
            $this->response['errors']['pushNotice'] = 'Error adding push notice';

        if ( !empty($_SESSION['selectionMode']['models']) )
        {
            $selection = new SelectionsModel();
            $selection->getSelectedModels();
        }

        //============= CP ==============//
        $this->progress->count(100);

        exit( json_encode($this->response) );
    }

    protected function validationFailedResponse( array $errors )
    {
        $this->response['validateErrors'] = $errors;

        exit( json_encode($this->response) );
    }

}
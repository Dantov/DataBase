<?php
namespace Views\_EditStatuses\Controllers;

use Views\_SaveModel\Controllers\SaveModelController;
use Views\_SaveModel\Models\Condition;
use Views\_SaveModel\Models\HandlerPrices;
use Views\_EditStatuses\Models\EditStatusesModel;
use Views\_Globals\Controllers\GeneralController;
use Views\_Globals\Models\{
    ProgressCounter, PushNotice, SelectionsModel, User
};
use Views\vendor\libs\classes\AppCodes;


class EditStatusesController extends GeneralController
{

    public $title = 'Изменить статусы';

    /**
     * @throws \Exception
     */
    public function beforeAction()
    {
        $request = $this->request;
        if ( $request->isAjax() )
        {
			try 
			{
				if ( $request->isPost() && $request->post('save') )
				    $this->actionSaveStatuses();

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
            //if ( $request->isPost() && $request->post('save') ) $this->actionSaveStatuses();
            exit;
        }
    }

    /**
     * @throws \Exception
     */
    public function action()
    {
        $edit = new EditStatusesModel(false);

        $permittedFields = $edit->permittedFields();
        $prevPage = $edit->setPrevPage();

		// Не будет ставить чекбокс. У моделей бывают разные статусы, отобразить их все "не возможно"!
        $status = $edit->getStatus();

        $header = "Проставить статус для моделей: ";

        $models = $edit->modelsData();

        $compact = compact([
            'prevPage','status','header','models'
        ]);

        $this->includePHPFile('upDownSaveSideButtons.php','','',_viewsDIR_.'_AddEdit/includes/');
        $this->includePHPFile('resultModal.php','','',_viewsDIR_.'_AddEdit/includes/');
        $this->includeJSFile('ResultModal.js', ['defer','timestamp','path'=>_views_HTTP_.'_AddEdit/js/'] );
        $this->includeJSFile('sideButtons.js', ['defer','timestamp','path'=>_views_HTTP_.'_Globals/js/'] );
        $this->includeJSFile('statusesButtons.js', ['defer','timestamp','path'=>_views_HTTP_.'_Globals/js/'] );
        $this->includeJSFile('submitForm.js', ['defer','timestamp'] );

        return $this->render('edit', $compact);
    }

    /**
     * @throws \Exception
     */
    public function actionSaveStatuses()
    {
        $request = $this->request;
        $session = $this->session;
        $selectionMode = $session->getKey('selectionMode');
        $status = $request->post('status');
        $date = date('Y-m-d');

        //debugAjax($status,'status');


        $result = [
            'done'=>'',
        ];

        if ( empty($selectionMode['models']) || empty($status) )
        {
            $result['done'] = false;
            exit (json_encode($result['done']));
        }

        $progress = new ProgressCounter();
        if ( isset($_POST['userName']) && isset($_POST['tabID']) )
        {
            $progress->setProgress($request->post('userName'), $request->post('tabID'));
        }
        $progressCounter = 0;
        $overallProcesses = count($selectionMode['models']??[]);

        $pn = new PushNotice();
        $payments = new HandlerPrices(false);
        $payments->connectDBLite();

        //debugAjax($payments->getStatusInfo($status),'statusINFO',"end_ajax_buffer");

        $pricesController = new SaveModelController('1'); // Не запустит Action()
		
        // флаги редакт. модели
        $component = 2;
        Condition::set($component);

        $in = "";

		//Внесем статусы в табл. Statuses для каждой модели. И, добавим стоимости, если необходимо
        foreach ( $selectionMode['models'] as $model )
        {
            $modelID = $model['id'];
            $payments->setId($modelID);
			
            // пропустим итерацию, если статусы в данной модели менять запрещено!
            $modelDate =  $payments->findOne("SELECT date FROM stock WHERE id='$modelID'")['date'];
            //debugAjax($modelDate, "modelDate");
            if ( !$payments->statusesChangePermission($modelDate, $component) ) //
                continue;

            //debugAjax($model, "model-END", END_AB);
				
            $isCurrentStatusPresent = $payments->isStatusPresent($status);
            $statusT = [
                'pos_id' => $modelID,
                'status' => $status,
                'creator_name' => User::getFIO(),
                'UPdate'   => $date
            ];
            $payments->addStatusesTable($statusT);
			
            $pricesController->isCurrentStatusPresent = $isCurrentStatusPresent;
            $pricesController->paymentsRequisite['status'] = (int)$status;

			//Зачисление стоимостей на каждую модель
            $pricesController->actionSaveData_Prices($payments);

            $names = explode(' | ', $model['name']);
            $addPush = $pn->addPushNotice($modelID, 2, $names[0], $names[1], $model['type'], $date, $status, User::getFIO());
            if ( !$addPush )
            {
                $result['addPush'] = 'Error adding push notice';
            } else {
                $result['addPush'] = 'OK';
            }
            $in .= $model['id'] . ",";

            //============= counter point ==============//
            $progress->progressCount( ceil( ( ++$progressCounter * 100 ) / $overallProcesses ) );
        }
        //debug('','last',1);
        //debugAjax(123,'END',END_AB);

		// Изменим статусы в табл. Stock
        $update = false;
        $in = rtrim($in,',');
        if ( !empty($in) )
        {
            $in = "(" . rtrim($in,',') . ")";
            $sql = "UPDATE stock SET status='$status', status_date='$date' WHERE id IN $in";
            $update = $payments->baseSql($sql);
        }


        if ( $update )
        {
            $result['done'] = 1;
            $selection = new SelectionsModel($session);
            $selection->getSelectedModels();
        } else {
            $result['done'] = "false";
        }

        $progress->progressCount( 100 );
        exit( json_encode($result) );
    }

}
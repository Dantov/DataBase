<?php
namespace controllers;

use models\{
    ProgressCounter, PushNotice, SelectionsModel, User,
    EditStatusesModel, HandlerPrices, Condition
};
use libs\classes\AppCodes;


class EditStatusesController extends GeneralController
{

    public string $title = 'Изменить статусы';

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
				if ( $request->post('save') ) $this->actionSaveStatuses();

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
    }

    /**
     * @throws \Exception
     */
    public function action()
    {
        $edit = new EditStatusesModel();

        $permittedFields = $edit->permittedFields();
        $prevPage = $edit->setPrevPage();

		// Не будет ставить чекбокс. У моделей бывают разные статусы, отобразить их все "не возможно"!
        $status = $edit->getStatus(selMode: 'selectionMode');

        $header = "Проставить статус для моделей: ";

        $models = $edit->modelsData();

        $compact = compact([
            'prevPage','status','header','models'
        ]);

        $this->includePHPFile(name: 'upDownSaveSideButtons.php', path: _WEB_VIEWS_.'add-edit/');
        $this->includePHPFile(name: 'resultModal.php', path: _WEB_VIEWS_.'add-edit/');

        $this->includeJSFile('ResultModal.js', ['defer','timestamp','path'=>'web/views/add-edit/js/'] );
        $this->includeJSFile('sideButtons.js', ['defer','timestamp','path'=>'web/views/globals/js/'] );
        $this->includeJSFile('statusesButtons.js', ['defer','timestamp','path'=>'web/views/globals/js/'] );
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

        if ( empty($selectionMode['models']) || empty($status) ) {
            $result['done'] = false;
            exit (json_encode($result['done']));
        }

        $progress = new ProgressCounter();
        if ( ($uName = $request->post('userName')) && ($tID = $request->post('tabID')) )
            $progress->setProgress($uName, $tID);
        $progressCounter = 0;
        $overallProcesses = count($selectionMode['models']??[]);

        $pn = new PushNotice();
        $payments = new HandlerPrices();

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
				
            $pricesController->isCurrentStatusPresent = $payments->isStatusPresent($status);
            $statusT = [
                'pos_id' => $modelID,
                'status' => $status,
                'creator_name' => User::getFIO(),
                'UPdate'   => $date
            ];
            $payments->addStatusesTable($statusT);
			
            $pricesController->paymentsRequisite['status'] = (int)$status;

			//Зачисление стоимостей на каждую модель
            $pricesController->actionSaveData_Prices($payments);

            $names = explode(' | ', $model['name']);
            $addPush = $pn->addPushNotice($modelID, 2, $names[0]??'', $names[1]??'', $model['type']??'', $date, $status, User::getFIO());
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
        //debugAjax(123,'END',END_AB);

		// Изменим статусы в табл. Stock
        //$edit = new EditStatusesModel();

        $update = false;
        $in = rtrim($in,',');
        if ( !empty($in) )
        {
            $in = "(" . rtrim($in,',') . ")";
            //$sql = "UPDATE stock SET status='$status', status_date='$date' WHERE id IN $in";
            //$update = $payments->baseSql($sql);
            $payments->update(table:"stock", row:['status'=>$status,'status_date'=>$date], where:['id','IN',$in]);
            if ( $payments->affectedRows )
                $update = true;
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
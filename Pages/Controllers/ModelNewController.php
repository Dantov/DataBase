<?php
namespace controllers;

use models\{AddEdit,User};
use soffit\{Crypt};

class ModelNewController extends AddEditController
{

    public string $title = 'Добавить новую модель';

    /**
     * Clear form for a new model to create
     * @throws \Exception
     */
    public function action()
    {
        if ( !User::permission('addModel') ) 
            $this->redirect('/main/');

        $component = 1;
        $model = new AddEdit();
        $formVars = $this->getFormVars( $model );
        
        $row['status'] = $model->getStatusByID(35, true); // статус эскиз по умолчанию
        $row['modeller3D']   = '';
        $row['author']       = '';
        $row['model_type']   = '';
        $row['size_range']   = '';
        $row['model_weight'] = '';
        $row['description']  = '';
        $row['collections']  = [];

        $statusesWorkingCenters = $model->getStatus();
        $labels = $model->getLabels();
        $changeCost = in_array(User::getAccess(), [1,2,8,9,10,11]);

        /** Смотрим можно ли изменять статус **/
        $toShowStatuses = true;
        /** Смотрим можно ли удалять последний проставленый статус **/
        $toDellLastStatus = false;

        $save = Crypt::strEncode("_".time()."!");
        $this->session->setKey('saveModel', $save);

        $this->phpIncludes( $model, $formVars );
        $this->jsIncludes( $model, $formVars );

        $compact = compact([ 'component','formVars','toShowStatuses','row','save','changeCost','statusesWorkingCenters',
            'labels','toDellLastStatus']);
        return $this->render('new', $compact);
    }
}
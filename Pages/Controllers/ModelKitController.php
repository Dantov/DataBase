<?php
namespace controllers;

use models\{AddEdit,User};
use soffit\{Crypt};

class ModelKitController extends AddEditController
{

    /**
     * Clear form for a new model to create
     * @throws \Exception
     */
    public function action()
    {
        if ( !User::permission('addComplect') ) 
            $this->redirect('/main/');

        $component = 3;
        $model = new AddEdit($this->stockID);
        
        //==========================
        $formVars = $this->getFormVars($model);
        $row = $model->getGeneralData();
        $this->title = 'Добавить комплект для ' . $row['number_3d'];

        // статус эскиз по умолчанию
        $row['status'] = $model->getStatusByID(35, true);

        $formVars['id'] = 0;
        $formVars['complected'] = $model->getComplected($component);
        $formVars['materials'] = $model->getMaterials(false,true);
        $formVars['gemsRow']  = $model->getGems(true);
        $formVars['dopVCs']  = $model->getDopVC(true);
        $formVars['num3DVC_LI'] = $model->getNum3dVCLi( $formVars['dopVCs'] );
        $formVars['images']  = $model->getImages(true);
        $labels = $model->getLabels($row['labels']);

        $statusesWorkingCenters = $model->getStatus();

        // Чтобы вызывать этот медод из Вида,
        $setPrevImg = function( $image ) use (&$model)
        {
            return $model->origin_preview_ImgSelect($image);
        };
        //============================

        /** Смотрим можно ли изменять статус **/
        $toShowStatuses = true;
        /** Смотрим можно ли удалять последний проставленый статус **/
        $toDellLastStatus = false;
        $changeCost = in_array(User::getAccess(), [1,2,8,9,10,11]);
        $save = Crypt::strEncode("_".time()."!");
        $this->session->setKey('saveModel', $save);

        $this->phpIncludes( $model, $formVars );
        $this->jsIncludes( $model, $formVars );

        $compact = compact(['component','formVars','toShowStatuses','row','save','changeCost','statusesWorkingCenters',
            'labels','toDellLastStatus','setPrevImg']);
        return $this->render('kit', $compact);
    }
}
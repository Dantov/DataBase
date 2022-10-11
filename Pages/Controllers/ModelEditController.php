<?php
namespace controllers;

use models\{AddEdit,User,Statuses};
use soffit\{Crypt};

class ModelEditController extends AddEditController
{

    /**
     * Clear form for a new model to create
     * @throws \Exception
     */
    public function action()
    {
        if ( !User::permission('editModel') || !User::permission('editOwnModels') ) 
            $this->redirect('/main/');
        
        $model = new AddEdit($this->stockID);
        if ( !$model->checkID($this->stockID) ) 
            $this->redirect('/main/');

        $row = $model->getGeneralData();

        /** Last check for getting out of here! */
        $editBtn = false;
        if ( User::permission('editModel') )
        {
            $editBtn = true;
        } elseif ( User::permission('editOwnModels') ) {
            $userRowFIO = explode(' ',User::getFIO())[0];
            if ( mb_stristr($row['author'], $userRowFIO) !== FALSE || 
                mb_stristr($row['modeller3D'], $userRowFIO) !== FALSE || 
                mb_stristr($row['jewelerName'], $userRowFIO) !== FALSE )
                $editBtn = true;
        }
        if (!$editBtn) $this->redirect('/main/');


        /** OK Success here! */
        $this->title = 'Редактировать ' . $row['number_3d'] . '-' . $row['model_type'];
        $component = 2;
        $formVars = $this->getFormVars($model);

        $formVars['id'] = $this->stockID;
        $formVars['complected'] = $model->getComplected($component);
        $formVars['stl_file'] = $model->getStl();
        $formVars['rhino_file'] = $model->get3dm();
        $formVars['ai_file'] = $model->getAi();
        $formVars['materials'] = $model->getMaterials();
        $formVars['repairs'] = $model->getRepairs();
        $formVars['countRepairs'] = $model->countRepairs( $formVars['repairs'] );
        $formVars['notes'] = $model->getDescriptions();
        $formVars['images']  = $model->getImages();
        $formVars['gemsRow']  = $model->getGems();
        $formVars['dopVCs']  = $model->getDopVC();
        $formVars['num3DVC_LI'] = $model->getNum3dVCLi( $formVars['dopVCs'] );
        $formVars['modelPrices'] = $model->getModelPrices();
        $labels = $model->getLabels($row['labels']);
        if ( empty($row['status']) )
        {
            $s = new Statuses($this->stockID);
            $lastStat = $s->findLastStatus();
            $row['status'] = $s->getStatusByID($lastStat['status'],true);
            $s->updateStockStatus( $row['status'] );
        }
        $statusesWorkingCenters = $model->getStatus($row['status']['id']??0);

        /** Смотрим можно ли изменять статус **/
        $toShowStatuses = $model->statusesChangePermission($row['date']??date("Y-m-d"), $component);

        /** Смотрим можно ли удалять последний проставленый статус **/
        $toDellLastStatus = false;
        if ( $toShowStatuses )
        {
            $s = new Statuses($this->stockID);
            $lastStatusArray = $s->findLastStatus();
            if ( !$s->isSingle() && $s->checkStatusBelongUser( (int)$lastStatusArray['status']) )
                $toDellLastStatus = true;
        }

        // For use this function in View
        $setPrevImg = function( $image ) use (&$model)
        {
            return $model->origin_preview_ImgSelect($image);
        };
        /** Setting up preview image */
        if ( $formVars['images'] )
        {
            $mainImage = $model->origin_preview_ImgSelect($formVars['images'][0]);
            foreach ( $formVars['images'] as $image )
            {
                if ( !empty($image['main']) )
                {
                    $mainImage = $model->origin_preview_ImgSelect($image);
                    break;
                }
                if ( !empty($image['sketch']) )
                {
                    $mainImage = $model->origin_preview_ImgSelect($image);
                    break;
                }
            }
        } else {
            $mainImage = _stockDIR_HTTP_ . 'default.jpg';
        }

        $changeCost = in_array(User::getAccess(), [1,2,8,9,10,11]);

        $save = Crypt::strEncode("_".time()."!");
        $this->session->setKey('saveModel', $save);

        $this->phpIncludes( $model, $formVars );
        $this->jsIncludes( $model, $formVars );

        $compact = compact(['component','formVars','toShowStatuses','row','save','changeCost','statusesWorkingCenters',
            'labels','toDellLastStatus','setPrevImg','mainImage']);
        return $this->render('edit', $compact);
    }

}
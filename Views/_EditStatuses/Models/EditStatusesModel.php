<?php
namespace Views\_EditStatuses\Models;
use Views\_AddEdit\Models\AddEdit;


class EditStatusesModel extends AddEdit
{

    public function __construct( int $id = 0 )
    {
        parent::__construct($id);
    }

    /**
     * 
     * @return array
     * @throws \Exception
     */
    public function modelsData()
    {
        $sellMode = $this->session->getKey('selectionMode');
        $selectedModels = $sellMode['models']??[];
        
        // debug($selectedModels,'models');
        $ids = '';
        foreach ($selectedModels as $model ) 
            $ids .= $model['id'] . ',';
        
        $ids = trim($ids,',');
        if ( empty($ids) ) 
            $this->request->redirect('/main');

        $images = $this->findAsArray("SELECT img_name,pos_id FROM images WHERE (main='1' OR sketch='1' OR onbody='1') AND (pos_id IN ($ids))");
        $stockModels = $this->findAsArray(" SELECT * FROM stock WHERE id IN ($ids) ");

         //debug($images,'images',1);
        // debug($stockModels,'stockModels');

        foreach ($stockModels as &$stockModel )
        {
            $modelID = $stockModel['id'];
            $statusID = $stockModel['status'];
            //$stockModel['img_name'] = '';
            foreach ($images as $image )
            {
                if ( (int)$image['pos_id'] === (int)$modelID ) {
                    $stockModel['img_name'] = $image['img_name'];
                    //continue 2;
                }
            }
            foreach ($this->statuses as $status )
            {
                if ( (int)$status['id'] === (int)$statusID ) {
                    $stockModel['status'] = $status;
                    continue 2;
                }
            }
        }

        //debug($this->statuses,'statuses');
        //debug($stockModels,'stockModels',1);
 
        return $stockModels;
    }

}
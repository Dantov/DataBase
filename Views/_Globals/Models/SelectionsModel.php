<?php
/**
 * User: Admin
 * Date: 30.04.2020
 * Time: 23:42
 */

namespace Views\_Globals\Models;

//use Views\vendor\core\Sessions;



class SelectionsModel extends General
{
    //public $session;
    protected $controller;

    public function __construct( $session = null, $controller = null )
    {
        parent::__construct();
        if ( $controller instanceof \Views\_Main\Controllers\MainController )
            $this->controller = $controller;
    }

    public function selectionModeToggle($selToggle)
    {
        $selectionMode = $this->session->getKey('selectionMode');
        $resp = 0;

        if ( $selToggle === 1 ) {
            $selectionMode['activeClass'] = "btnDefActive";
            $resp = 'on';
        }
        if ( $selToggle === 2 ) {
            $selectionMode['activeClass'] = "";
            $resp = 'off';
            if (isset($selectionMode['showModels']))
            {
                unset($selectionMode['showModels']);
                $assist = $this->session->getKey('assist');
                $assist['collectionName'] = 'Все Коллекции';
                $this->session->setKey('assist', $assist);
            }

        }

        $selectionMode['models'] = [];

        $this->session->setKey('selectionMode', $selectionMode);
        exit( json_encode($resp) );
    }

    public function checkBoxToggle($checkBox)
    {
        $id = (int)$_POST['modelId'];
        $name = isset($_POST['modelName']) ? $_POST['modelName'] : "";
        $type = isset($_POST['modelType']) ? $_POST['modelType'] : "";
        if ( $checkBox === 1 ) {
            $_SESSION['selectionMode']['models'][$id] = array(
                'id' => $id,
                'name' => $name,
                'type' => $type
            );
        }
        if ( $checkBox === 2 ) {
            unset($_SESSION['selectionMode']['models'][$id]);
        }

        $resp['checkBox'] = $checkBox;
        $resp['id'] = $_POST['modelId'];
        $resp['name'] = $_POST['modelName'];
        $resp['type'] = $type;

        exit( json_encode($resp) );
    }

    public function checkSelectedModels()
    {
        $selectionMode = $this->session->getKey('selectionMode');
        if ( trueIsset($selectionMode['models']) )
            exit( json_encode($selectionMode['models']) );

        exit(json_encode([]));
    }

    /**
     * @throws \Exception
     */
    public function getSelectedModels()
    {
        $selectionMode = $this->session->getKey('selectionMode');
        $selectedModels = $selectionMode['models'];

        if ( empty($selectionMode['models']) )
        {
            $request = $this->controller->request;
            if ( $request->isAjax() )
                exit( json_encode('false') );

            $selectionMode = $this->session->getKey('selectionMode');
            unset($selectionMode['showModels']);
            $this->session->setKey('selectionMode', $selectionMode);
            $this->controller->redirect('/main');
        }


        $assist = $this->session->getKey('assist');
        $orderBy = $assist['reg'];
        $sortDirect = $assist['sortDirect'];

        $statQuery = "";
        if ( isset($assist['regStat']) && $assist['regStat'] != "Нет" )
        {
            $regStat = $assist['regStat'];
            $statQuery = "AND status='$regStat'";
        }

        $modelIds = '';
        foreach( $selectedModels as $model ) $modelIds .= $model['id'] .',';
        if ( !empty($modelIds) ) {
            $modelIds = '(' . rtrim($modelIds,',') . ')';
        } else {
            $modelIds = '(0)';
        }
        $selectRow = "SELECT * FROM stock WHERE id IN $modelIds $statQuery ORDER BY $orderBy $sortDirect";

        $assist['collectionName'] = 'Выделенное';
        $assist['collection_id'] = -1;
        $assist['page'] = 0;
        $assist['startfromPage'] = 0;
        $selectionMode['showModels'] = 1;
        $this->session->setKey('assist', $assist);
        $this->session->setKey('selectionMode', $selectionMode);

        return $this->findAsArray($selectRow);
    }
}
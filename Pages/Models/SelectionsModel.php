<?php
/**
 * User: Admin
 * Date: 30.04.2020
 * Time: 23:42
 */
namespace models;



class SelectionsModel extends General
{
    protected $controller;

    public function __construct( $session = null, $controller = null )
    {
        parent::__construct();

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
            if (isset($selectionMode['models']))
            {
                unset($selectionMode['models']);
                $assist = $this->session->getKey('assist');
                $assist['collectionName'] = 'Все Коллекции';
                $this->session->setKey('assist', $assist);
            }
        }

        $selectionMode['models'] = [];

        $this->session->setKey('selectionMode', $selectionMode);
        exit( json_encode($resp) );
    }

    public function checkBoxToggle( int $checkBox ) : void
    {
        $request = $this->request;
        $session = $this->session;
        $selMode = $this->session->getKey('selectionMode');

        $id   = (int)$request->post('modelId');
        $name = $request->post('modelName')??"";
        $type = $request->post('modelType')??"";

        if ( $checkBox === 1 ) 
        {
            $selMode['models'][$id] = [
                'id' => $id,
                'name' => $name,
                'type' => $type
            ];
        }
        if ( $checkBox === 2 )
            unset($selMode['models'][$id]);

        $session->setKey('selectionMode',$selMode);

        $resp['checkBox'] = $checkBox;
        $resp['id'] = $id;
        $resp['name'] = $name;
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
            if ( $this->request->isAjax() )
                exit( json_encode('false') );

            $selectionMode = $this->session->getKey('selectionMode');
            unset($selectionMode['showModels']);
            $this->session->setKey('selectionMode', $selectionMode);
            $this->request->redirect('main/');
        }

        $res = "";
        foreach ( $selectedModels as $posID )
        {
            if ( !$posID['id'] ) continue;
            $res .= $posID['id'] . ",";
        }
        $res = trim($res,',');
        $res = "(id IN ($res))";

        return trim($res,',');
    }
}
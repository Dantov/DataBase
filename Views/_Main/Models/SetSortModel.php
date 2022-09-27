<?php
namespace Views\_Main\Models;
use Views\_Globals\Models\General;
use Views\vendor\core\ActiveQuery;


/**
 * SetSortModel класс для переменных сортировки и выборки
 */
class SetSortModel extends General
{

    /**
     * @var $sessions
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     *
     * @param $params array
     * @return bool|string
     * @throws \Exception
     */
    public function setSort($params=[])
    {
        foreach ($params as $paramName => $paramValue) 
        {
            switch ($paramName) 
            {
                // ***** Pagin START ******* //
                case 'page':
                    $this->setPagination($paramName, $paramValue);
                    break;
                case 'start_FromPage':
                    $this->setPagination($paramName, $paramValue);
                    break;
                case 'st_prevPage':
                    $this->setPagination($paramName, $paramValue);
                    break;
                // ***** Pagin END ******* //
                
                case 'coll_show':
                    $this->showCollection($paramValue);
                    break;
                case 'sortby':
                    $this->setOrderBy($paramValue);
                    break;
                case 'row_pos':
                    $this->setRowPos($paramValue);
                    break;
                case 'maxpos':
                    $this->setMaxPos($paramValue);
                    break;
                case 'regStat':
                    return $this->sortByStatus($paramValue);
                    break;
                case 'mt':
                    return $this->sortBy('type',$paramValue);
                    break;
                case 'mat':
                    return $this->sortBy('material',$paramValue);
                    break;
                case 'gem':
                    return $this->sortBy('gem',$paramValue);
                    break;
                case 'sortDirect':
                    $this->sortDirect($paramValue);
                    break;
                case 'wcSort':
                    $this->sortByWorkingCenters($paramValue);
                    break;
                case 'countedIds':
                    return $this->setExpiredModels($paramValue);
                    break;
                case 'purgeselect':
                    return $this->purgeSort($paramValue);
                    break;
                default:
                    //
                    break;
            }
        }
        
        $session = $this->session;
        // если в поиске что-то найдено, и он нуждается в обновлении
        if ( $session->getKey('countAmount') && $session->getKey('re_search') ) 
	    {
            //return '/main/?searchFor=' . $session->getKey('searchFor');
            return '/globals/?searchFor=' . $session->getKey('searchFor');
	    }
        return false;
    }

    /**
     * убрали информацию о поиске
     */
    protected function killSearch()
    {
        $selectionMode = $this->session->getKey('selectionMode');
        if ( isset($selectionMode['showModels']) )
        {
            unset($selectionMode['showModels']);

            $assist = $this->session->getKey('assist');
            $assist['collectionName'] = 'Все Коллекции';

            $this->session->setKey('assist', $assist);
            $this->session->setKey('selectionMode', $selectionMode);
        }

        $this->session->dellKey('foundRow');
        $this->session->dellKey('countAmount');
        $this->session->dellKey('searchFor');

        $this->session->dellKey('re_search'); //было добавлено!!! 04,05,20
    }
    
    protected function setPagination($param, $value) 
    {
        $session = $this->session;
        $assist = $session->getKey('assist');
        
        // start_FromPage - это флаг с какого квадратика начинать отрисовывать 
	    // st_prevPage - это флаг что нужно отрисовать кнопку назад на пред. часть страниц

        if ( $param === 'page' )           $assist['page'] = (int)$value; // кликнули по квадратику пагинации
        if ( $param === 'start_FromPage' ) $assist['startFromPage'] = (int)$value;
        if ( $param === 'st_prevPage' )    $assist['st_prevPage']   = (int)$value;
        
        $session->setKey('assist', $assist);
    }

    /**
     * @param $collID
     * @throws \Exception
     */
    protected function showCollection($collID)
    {
        $collID = (int)$collID;
        if ( $collID < -1 || $collID > 999 ) $collID = -1;

        $session = $this->session;
        $assist = $session->getKey('assist');

        if ( $collID !== -1 ) 
        {
            //$coll_row = $this->findOne(" SELECT name FROM service_data WHERE id='$collID' AND tab='collections' ");
            $coll_row = (new ActiveQuery('service_data'))->service_data
                ->select(['name'])
                ->where('id','=',$collID)
                ->and('tab','=','collections')
                ->asOne()
                ->exe();

            $assist['collectionName'] = $coll_row['name'];
            $assist['collection_id'] = $collID;
            $assist['page'] = 0;
            $assist['startFromPage'] = 0;
        } else {
            $assist['collectionName'] = "Все Коллекции";
            $assist['collection_id'] = -1; // -1 = все коллекции
            $assist['page'] = 0;
            $assist['startFromPage'] = 0;
        }
        $session->setKey('assist',$assist);
        $this->killSearch();
    }

    protected function setOrderBy($column)
    {
        $session = $this->session;
        $assist = $session->getKey('assist');

        switch ($column) {
            case "number_3d":
                $assist['reg'] = "number_3d";
                break;
            case "date":
                $assist['reg'] = "date";
                break;
            case "vendor_code":
                $assist['reg'] = "vendor_code";
                break;
            case "status":
                $assist['reg'] = "status";
                break;
            default:
                $assist['reg'] = "number_3d";
                break;
        }
        $session->setKey('assist', $assist);
        $session->setKey('re_search', true);
    }
    
    protected function setRowPos($rowPos) 
    {
        $session = $this->session;
        $assist = $session->getKey('assist');
        
        $row_pos = (int)$rowPos;
        if ( $row_pos > 0 && $row_pos < 6  )
        {
            $assist['drawBy_'] = $row_pos;
        }
        
        $assist['page'] = 0;
        $assist['startFromPage'] = 0;
        
        $session->setKey('assist', $assist);
        $session->setKey('re_search', true);
    }
    
    protected function setMaxPos($maxPos) 
    {
        $session = $this->session;
        $assist = $session->getKey('assist');
        
        $assist['maxPos'] = (int)$maxPos;
        
        $assist['page'] = 0;
        $assist['startFromPage'] = 0;
        
        $session->setKey('assist', $assist);
        $session->setKey('re_search', true);
    }

    /**
     * @param string $type
     * @param int $typeID
     * @throws \Exception
     */
    protected function sortBy( string $type, int $typeID ) : void
    {
        if ( $typeID > PHP_INT_MAX || $typeID < -1 ) $typeID = -1;

        $session = $this->session;
        $assist = $session->getKey('assist');

        switch ( $type )
        {
            case "material":
                $assistName = "modelMaterial";
                $mTypes = $this->getModelMaterialsSelect();
                break;
            case "type":
                $assistName = "modelType";
                $mTypes = $this->getServiceData(['model_type'])['model_type'];
                break;
            case "gem":
                $assistName = "gemType";
                $mTypes = $this->getServiceData(['gems_names'])['gems_names'];
                break;
            default:
                $assistName = "modelMaterial";
                $mTypes = $this->getModelMaterialsSelect();
                break;
        }

        if ( $typeID === -1 )
            $assist[$assistName] = "Все";

        foreach ( $mTypes as $mType )
        {
            if ( (int)$mType['id'] === $typeID )
            {
                $assist[$assistName] = $mType['name'];
                break;
            }
        }

        $session->setKey('assist', $assist);
        $session->setKey('re_search', true);
    }

    /**
     * @param $statusID
     * @return string
     * @throws \Exception
     */
    protected function sortByStatus($statusID)
    {
        $session = $this->session;
        $assist = $session->getKey('assist');
        
        $statusID = (int)$statusID;
        
        $this->connectToDB();
        $statuses = $this->statuses;

        $flag = false;
        foreach ($statuses as $status)
        {
            if ( (int)$status['id'] === $statusID )
            {
                $assist['regStat'] = $status['name_ru'];
                $flag = true;
            }
        }
        if ( !$flag )
        {
            $assist['regStat'] = "Нет";
            // выключаем поиск по истории статусов при клике на НЕТ
            $assist['byStatHistory'] = 0;
            $assist['byStatHistoryFrom'] = '';
            $assist['byStatHistoryTo'] = '';
        }

        $assist['page'] = 0;
        $assist['startFromPage'] = 0;
        $session->setKey('assist', $assist);
        
        $searchFor = $session->getKey('searchFor');
        if ( !empty($searchFor) ) 
        {
            $session->setKey('re_search', true);
            //return '/main/?searchFor=' . $searchFor;
            return '/globals/?searchFor=' . $searchFor;
        }
    }
    
    protected function sortDirect($param) 
    {
        $session = $this->session;
        $assist = $session->getKey('assist');
        
        if ( (int)$param === 1 ) $assist['sortDirect'] = "ASC";
	    if ( (int)$param === 2 ) $assist['sortDirect'] = "DESC";
       
        $session->setKey('assist', $assist);
        $session->setKey('re_search', true);
    }

    /**
     * @param $wcIDs
     * @throws \Exception
     */
    protected function sortByWorkingCenters($wcIDs)
    {
        $wcIDs = trim( htmlentities($wcIDs, ENT_QUOTES) );
        $wcIDs = explode('-',$wcIDs);

        $workingCenters = $this->getWorkingCentersDB();

        // просто проверка, что б не пришли другие айдишники центров
        $wcIDsss = [];
        $wcIDsName = '';
        foreach ( $workingCenters as $workingCenter )
        {
            foreach ( $workingCenter as $key => $wcArr )
            {
                foreach ( $wcIDs as $wcID )
                {
                    if ( (int)$wcID === (int)$key )
                    {
                        $wcIDsss[] = (int)$wcID;
                        $wcIDsName = $wcArr['name'];
                    }
                }
            }
        }
        $session = $this->session;
        $assist = $session->getKey('assist');
        
        $assist['wcSort']['ids'] = $wcIDsss;
        $assist['wcSort']['name'] = $wcIDsName;
        $assist['page'] = 0;
        $assist['startFromPage'] = 0;
        
        $session->setKey('assist', $assist);
        $session->setKey('re_search', true);
    }

    /**
     * @param $countedIds
     * @return bool|string
     * @throws \Exception
     */
    protected function setExpiredModels($countedIds)
    {
        $session = $this->session;
        $assist = $session->getKey('assist');
        
        $countedIds = trim( htmlentities($countedIds, ENT_QUOTES) );
        $in = '('.$countedIds.')';

        $selectRow = "SELECT * FROM stock WHERE id IN $in ORDER BY {$assist['reg']} {$assist['sortDirect']}";
        $foundModels = $this->findAsArray($selectRow);
        
        if ( empty($foundModels) ) return false;

        $this->killSearch();
        $session->setKey('foundRow', $foundModels);

        //debug($_SESSION['foundRow'],'foundRow=',1);
        $session->setKey('countAmount', count($foundModels));

        $assist['page'] = 0;
        $assist['startfromPage'] = 0;
        $assist['drawBy_'] = 3;
      
        $session->setKey('assist', $assist);

        return '/main/';
    }

    protected function purgeSort( int $param )
    {
        if ( $param !== 1 ) return;

        $session = $this->session;
        $assist = $session->getKey('assist');

        $assist['regStat'] = "Нет";
        $assist['byStatHistory'] = 0;
        $assist['byStatHistoryFrom'] = '';
        $assist['byStatHistoryTo'] = '';

        $assist['modelMaterial'] = 'Все';
        $assist['modelType'] = 'Все';
        $assist['gemType'] = 'Все';

        $assist['collectionName'] = "Все Коллекции";
        $assist['collection_id'] = -1;

        $assist['page'] = 0;
        $assist['startFromPage'] = 0;

        $session->setKey('assist', $assist);
        $session->setKey('re_search', true);
    }
}
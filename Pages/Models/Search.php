<?php
namespace models;
use soffit\Registry;

class Search extends General
{

    public $searchFor = '';

    /**
     * Search constructor.
     * @param array $params
     * @throws \Exception
     */
    public function __construct( array $params=[] )
    {
        parent::__construct();
    }

    protected function prepareSearchQuery( string $searchInput ) : string
    {
        // если дата
        /*
        $date = false;
        $toFindDate = '';
        if ( stristr($this->searchFor, '::') !== false )
        {
            $date = true;
            $searchParams = $this->bySearchParams();
            $searchFor = $searchParams['searchFor'];
            $toFindDate = $searchParams['toFindDate'];
        }
        */

        $stockSearchFields = [
            'number_3d','vendor_code','collections','author','jewelerName',
            'modeller3D','model_type','labels','date','description'
        ];

        // add Описания

        //LOWER( trees.title )
        $searcSTR = '';
        foreach( $stockSearchFields as $field )
        {
            $searcSTR .= "OR `".$field."` LIKE " . "'%$searchInput%' ";
        }
        if ( $descrIDs = $this->addsDescr() )
            $searcSTR .= "OR (id IN ($descrIDs))";

        $searcSTR = ltrim($searcSTR,'OR');
        if (empty($searcSTR))
            return '';

        return trim($searcSTR);
    }

    public function addsDescr()
    {
        $this->registerTable(['description']);
        $select = $this->DESCRIPTION->select(['pos_id'])->where('text','LIKE',"%$this->searchFor%")->exe();

        $res = "";
        foreach ( $select as $posID )
        {
            if ( !$posID['pos_id'] ) continue;
            $res .= $posID['pos_id'] . ",";
        }
        return trim($res,',');
    }

    /**
     * @param $searchInput
     * @return bool | mixed
     * @throws \Exception
     */
    public function search(string $searchInput) : string
    {
        if ( empty($searchInput) ) return '';

        $validator = Registry::init()->validator;

        $this->searchFor = $validator->filterSearchInput($searchInput);
        if ( empty($this->searchFor) )
        {
            $this->session->setKey('nothing', "Ничего не найдено");
            return '';
        }
        //debug($this->searchFor,'searchFor',1);
        $this->session->setKey('searchFor', $searchInput);
        $assist = $this->session->getKey('assist');
        $assist['page'] = 0;
        $assist['collectionName'] = 'Все Коллекции';
        $assist['collection_id'] = -1;

        $this->session->setKey('re_search', false);
        $this->session->setKey('assist', $assist);

        return $sQuery = $this->prepareSearchQuery($this->searchFor);   
    }


    /**
     * ::
     *
     */
    protected function bySearchParams()
    {
        $searchParams = explode("::", $this->searchFor);

        $searchFor = trueIsset($searchParams[0]) ? $searchParams[0]: '';
        $searchForDate = trueIsset($searchParams[1]) ? $searchParams[1]: '';
        $searchForDate = str_ireplace([',','-','_'], ".", $searchForDate);

        $dataPieces = explode('.',$searchForDate);

        $day = ''; $month = ''; $year = ''; $toFindDate = '';
        switch ( count($dataPieces) )
        {
            case 1:
                $year = $dataPieces[0];
                break;
            case 2:
                $month = $dataPieces[0];
                $year = $dataPieces[1];
                break;
            case 3:
                $day = $dataPieces[0];
                $month = $dataPieces[1];
                $year = $dataPieces[2];
                break;
        }
        if ( $day ) {
            $toFindDate = $year.'-'.$month.'-'.$day;
        } elseif ( $month && $year ) {
            $toFindDate = $year.'-'.$month;
        } elseif ( $year ) {
            $toFindDate = $year;
        }
        $searchParams['searchFor'] = $searchFor;
        $searchParams['toFindDate'] = $toFindDate;

        return $searchParams;
    }

}
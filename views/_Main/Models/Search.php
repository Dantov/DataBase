<?php
namespace Views\_Main\Models;
use Views\_Globals\Models\General;

class Search extends General
{

    public $searchFor = '';

    /**
     * Search constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct();

        $this->connectDBLite();

        $this->getStatLabArr('status');
    }

    /**
     * @param $searchInput
     * @return bool | mixed
     * @throws \Exception
     */
    public function search($searchInput)
    {
        if ( !is_string($searchInput) || empty($searchInput) ) return false;
        $this->searchFor = $searchFor = mb_strtolower( strip_tags( trim($searchInput) ) );
        
        $assist = $this->session->getKey('assist');
        $statuses = $this->statuses;

        $where = "";

        $regStat = 0;
        $regStat_str = $assist['regStat'];
        foreach ($statuses as $status)
        {
            if ( $status['name_ru'] === $regStat_str )
            {
                $regStat = (int)$status['id'];
                break;
            }
        }

        if ( $assist['searchIn'] === 2 && isset($assist['collectionName']) && !empty($assist['collectionName']) ) 
        {
            $collectionName = $assist['collectionName'];
            $where = "WHERE collections like '%$collectionName%' ";

            if ( $assist['byStatHistory'] != 1 && $regStat_str != "Нет" ) $where .= "AND status='$regStat' ";

        } else if ( $assist['byStatHistory'] != 1 && $regStat_str != "Нет" )
        {
            $where = "WHERE status='$regStat' ";
        }

        $select = "SELECT * FROM stock ".$where." ORDER BY ".$assist['reg']." ".$assist['sortDirect'];
        $foundAllRows = $this->findAsArray($select);

        if ( $assist['byStatHistory'] == 1 )
        {
            $dates = [];
            if ( trueIsset($assist['byStatHistoryFrom'])) $dates['from'] = $assist['byStatHistoryFrom'];
            if ( trueIsset($assist['byStatHistoryTo'])) $dates['to'] = $assist['byStatHistoryTo'];
            self::byStatusesHistory($this->connection, $regStat, $foundAllRows, $dates);
        }

        // если дата
        $date = false;
        $toFindDate = '';
        if ( stristr( $this->searchFor, '::' ) !== false )
        {
            $date = true;
            $searchParams = $this->bySearchParams();
            $searchFor = $searchParams['searchFor'];
            $toFindDate = $searchParams['toFindDate'];
        }
        //$searchFor - Окажется пуста если идет поиск только по дате - ::02.2019 Удаляем что б не было Warning от stristr
        //if ( empty($searchFor) ) unset($searchFor);

        $foundRows = []; // массив с найденными позициями
        $countAmount = 0;
        
        // Поиск совпадений
        foreach ( $foundAllRows as $row )
        {
            // если подстрока найдена, то она попадет в переменную
            $search_number_3d   = $searchFor ? stristr( mb_strtolower($row['number_3d']),   $searchFor ) : false;
            $search_vendor_code = $searchFor ? stristr( mb_strtolower($row['vendor_code']), $searchFor ) : false;
            $search_collection  = $searchFor ? stristr( mb_strtolower($row['collections']), $searchFor ) : false;
            $search_author      = $searchFor ? stristr( mb_strtolower($row['author']),      $searchFor ) : false;
            $search_jeweller    = $searchFor ? stristr( mb_strtolower($row['jewelerName']), $searchFor ) : false;
            $search_modeller3d  = $searchFor ? stristr( mb_strtolower($row['modeller3D']),  $searchFor ) : false;
            $search_model_type  = $searchFor ? stristr( mb_strtolower($row['model_type']),  $searchFor ) : false;
            $search_status      = $searchFor ? stristr( mb_strtolower($row['status']),      $searchFor ) : false;
            $search_labels      = $searchFor ? stristr( mb_strtolower($row['labels']),      $searchFor ) : false;
            $search_description = $searchFor ? stristr( mb_strtolower($row['description']), $searchFor ) : false;
            $search_date        = $date      ? stristr( $row['date'],                      $toFindDate ) : false;

            if ( trueIsset($searchFor) && trueIsset($toFindDate) )
            {
                if ( $search_date !== false )
                {
                    if ( $search_number_3d !== false || $search_vendor_code !== false || $search_collection !== false || $search_author !== false || $search_jeweller !== false ||
                        $search_modeller3d !== false || $search_model_type !== false || $search_status !== false || $search_labels !== false || $search_description !== false )
                    {
                        $foundRows[] = $row;
                        $countAmount++;
                    }
                }
            } else {
                if ( $search_number_3d !== false || $search_vendor_code !== false || $search_collection !== false || $search_author !== false || $search_jeweller !== false ||
                    $search_modeller3d !== false || $search_model_type !== false || $search_status !== false || $search_labels !== false || $search_description !== false ||
                    $search_date !== false )
                {
                    $foundRows[] = $row;
                    $countAmount++;
                }
            }
        }
        if ( !trueIsset($foundRows) ) $this->session->setKey('nothing', "Ничего не найдено");
        
        $assist['page'] = 0;
        $assist['startfromPage'] = 0;

        $this->session->setKey('countAmount', $countAmount);
        //$this->session->setKey('foundRow', $foundRows);
        $this->session->setKey('re_search', false);
        $this->session->setKey('assist', $assist);

        return $foundRows;
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


    /**
     * @param @object $connection - объект соединения
     * @param @number $statusID - ID статуса по которому ищем
     * @param @array $searchedModels - массив моделей в которых ищем
     * @param $dates - массив с датами От и До, если они были заданы
     *
     * Производит дополнительный поиск в таблице статусов
     */
    public static function byStatusesHistory($connection, $statusID, &$searchedModels, $dates = [])
    {
        if ( !$connection ) return;
        if ( !$searchedModels ) return;
        if ( !$statusID ) return;

        $andDates = "";
        if ( !empty($dates) )
        {
            $from = '';
            $andDates = " AND ( ";
            if (!empty($dates['from'])) {
                $from = $dates['from'];
                $andDates .= " date>='$from' ";
            }
            if (!empty($dates['to'])) {
                $to = $dates['to'];
                if ( !empty($from) )  $andDates .= " AND ";
                $andDates .= " date<='$to' ";
            }
            $andDates .= " ) ";
        }

        $in = '(';
        foreach ( $searchedModels as &$model ) $in .= $model['id'] . ',';
        $in = trim($in,',') . ')';
        unset($model);

        $query = "SELECT pos_id,status,date FROM statuses WHERE status='$statusID' $andDates AND pos_id IN $in";
        $resultQuery = mysqli_query($connection, $query);
        $statuses = [];
        while( $resRow = mysqli_fetch_assoc($resultQuery) ) { $statuses[] = $resRow; }


        $resultModels = [];
        foreach ( $searchedModels as $model )
        {
            foreach ( $statuses as $status )
            {
                if ( $status['pos_id'] === $model['id'] )
                {
                    $resultModels[] = $model;
                    continue 2; // что б не выводил 2 одинаковых модели если было 2 одинаковых статуса
                }
            }
        }

        $searchedModels = $resultModels;
    }


}
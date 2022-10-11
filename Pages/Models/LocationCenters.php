<?php
namespace models;

use soffit\{Router};


class LocationCenters extends Main 
{

    /**
     * Main constructor.
     * @param array $foundRow
     * @throws \Exception
     */
    public function __construct( string $searchQuery='' )
    {
        parent::__construct(searchQuery: $searchQuery);

        if ( !isset($this->assist['page']) ) 
            $this->assist['page'] = 0;
    }
	
    /*
     * Дополнтельная выборка по участкам, для приложения №2
     */
    public function dopSortByWC()
    {
        // массив ID участков
        // надо выбрать все статусы которые относятся к этим участкам
        $workCenterIds = $_SESSION['assist']['wcSort']['ids'];
        $needleStatuses = [];
        foreach ( $workCenterIds as $wcID )
        {
            foreach ( $this->statuses as $status )
            {
                if ( $status['location'] == $wcID ) $needleStatuses[] = $status;
            }
        }
        $in = 'IN (';
        foreach ( $needleStatuses as $needleStatus )
        {
            $in .= "'".$needleStatus['id'] . "','" . $needleStatus['name_ru'] . "',";
        }
        //debug($in);
        return trim($in, ',') . ')';
    }

    /**
     * Таблица рабочих центров
     * Приложение №1
     *
     */
    public function getModels()
    {
        $this->wholePos = $result['wholePos'] = count($this->row);
        $this->getWorkingCentersSorted();

        $source = Router::getControllerNameOrigin()."/tableStart.php";
        if ( !file_exists(_WEB_VIEWS_ . $source) )
                throw new \Exception("File ". $source ." not found in: " . __METHOD__, 404);

        $result['iter'] = 0;
        ob_start();
            require _WEB_VIEWS_ . $source;
            foreach( $this->row as $modelRow )
            {
                if ( !isset($modelRow['id']) ) continue;

                if ( $this->drawTableRow($modelRow) ) 
                    $result['iter']++; // счетчик отрисованных позиций
            }
            echo "</tbody></table>";

            $result['models'] = ob_get_contents();
        ob_end_clean();

        return $result;
    }


    private function drawTableRow($row, $xlsx=false)
    {
        $statusesTable = $this->getStatusesTable($row['id']);
        $lastStatus = $statusesTable[count($statusesTable)-1];

        $workingCenter = []; // здесь раб. центр к которому принадлежит последний статус
        foreach ( $this->workingCentersDB as $wCenterDB )
        {
            //debug($wCenter,'$wCenter');
            foreach ( $wCenterDB as $wCenter )
            {
                if (isset($lastStatus['status']['location']) )
                {
                    if ( $lastStatus['status']['location'] == $wCenter['id'] )
                    {
                        $workingCenter = $wCenter;
                        break;
                    }
                }
            }
        }
        //debug($workingCenter);

        // выборка по участкам
        //$wcSortName = 
        //$wcSort = $_SESSION['assist']['wcSort']['name'] ?: false;
        $wcSort = $_SESSION['assist']['wcSort']['name']??false;
        if ( $wcSort )
        {
            if ( $workingCenter['name'] !== $wcSort ) return false;
        }

        //массив со списком статусов принятия (start)
        $permittedStatusesToEditDate = [];
        foreach ( $this->workingCentersDB as $wcGlobalName => $wCentersDB )
        {
            foreach ( $wCentersDB as $subUnit )
            {
                foreach ( $this->statuses as $status )
                {
                    if ( $status['type'] === 'start' && $status['location'] == $subUnit['id'] ) $permittedStatusesToEditDate[$wcGlobalName][] = $status['id'];
                }
            }

        }
        //debug($permittedStatusesToEditDate,'permittedStatusesToEditDate',1);
        // Определим рисовать ли нам инпут смены даты или просто дату
        $locations = explode(',',$this->user['location']);
//        debug($locations);
        $wcID = $workingCenter['id']??0;
        $drawEditDate = false;
        if ( in_array($wcID,$locations) )
        {
            foreach ($permittedStatusesToEditDate as $wcName => $statusesID) {
                if ($workingCenter['name'] == $wcName) {
                    //debug($wc);
                    //debug($lastStatus['status']['id'],'id');
                    if (in_array($lastStatus['status']['id'], $statusesID))
                    {
                        $drawEditDate = true;
                        break;
                    }
                }
            }
        }


        // парсим размеры. Считаем кол-во моделей в размерном ряде.
        $sizeRange = 1;
        if ( !empty($row['size_range']) )
        {
            if ( stristr($row['size_range'], ';') !== false )
            {
                $sizes = explode(';',$row['size_range']);
                $sizeRange = 0;
                foreach ( $sizes as $size )
                {
                    if ( !empty($size) ) $sizeRange++;
                }
            }
        }

        //Готовый артикулы
        $vc_done = 0;
        $vc_balance = 0;
        if ( isset($lastStatus['status']['id']) ) if ( (int)$lastStatus['status']['id'] === 7 ) $vc_done = 1;
        $vc_balance = $sizeRange - $vc_done;

        
        if ( $xlsx ) 
        {
            $result=[];
            $result['model'] = $row;
            $result['workingCenter'] = $workingCenter;
            $result['sizeRange'] = $sizeRange;
            $result['vc_balance'] = $vc_done;
            $result['lastStatus'] = $lastStatus;
            return $result;
        }
        
        require _WEB_VIEWS_ . Router::getControllerNameOrigin() ."/tableRow.php";

        return true;
    }

    /**
     * Сформируем массив данных для вывода в excel
     * @param $row - данные модели из Stock
     * @return bool
     */
    public function drawXlsxRow($row)
    {
        return $this->drawTableRow($row,true);
    }

}
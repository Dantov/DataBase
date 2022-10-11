<?php
namespace models;

use soffit\{Router};
use libs\classes\AppCodes;

class WorkingCenters extends Main 
{

    /**
     * Main constructor.
     * @param array $searchQuery
     * @throws \Exception
     */
    public function __construct( string $searchQuery='' )
    {
        parent::__construct(searchQuery: $searchQuery);

        if ( !isset($this->assist['page']) ) 
            $this->assist['page'] = 0;
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
        
        $source = Router::getControllerNameOrigin()."/drawTableStart.php";
        if ( !file_exists(_WEB_VIEWS_ . $source) )
            throw new \Exception("File ". $source ." not found in: " . __METHOD__, 404);

        $result['iter'] = 0;
        ob_start();
            require _WEB_VIEWS_ . $source;

            foreach( $this->row as $modelRow )
            {
                if ( !isset($modelRow['id']) ) continue;

                $drawn = $this->drawTableRow( $modelRow );
                if ( $drawn ) $result['iter']++; // счетчик отрисованных позиций
            }

            echo "</tbody></table></div>";

            $result['models'] = ob_get_contents();
        ob_end_clean();

        return $result;
    }


    // Приложение №1
    /**
     * @param $row - Single Stock model data.
     * @param $xlsx - flag to pack model data for excel export.
     * @return mixed
     */
    private function drawTableRow($row, $xlsx=false)
    {
        $wCenters = $this->workingCentersSorted;
        $statusesTable = $this->getStatusesTable($row['id']); // список статусов по ID из табл. Statuses
        $lastStatus = $row['status']; // ID

        $trFill = false; // Покрасит всю строку если статус отложено или снет с произв.
        // проверка на отложено сняио с произв.
        if ( $lastStatus == 11 || $lastStatus == 88) {
            $trFill = true;  // Покрасим строку в красный
        }
        
        //debug($statusesTable,'$statusesTable',1);
        //debug($wCenters,'$wCenters',1);
        /*
         * $cKey - номер участка по порядку ID
         * $wCenter - массив с информацией об участке.
         * start end - статусы принятия сдачи
         */
        foreach ( $wCenters as $cKey => $wCenterSorted ) // распределяем даты статусов по таблице
        {
            //debug($wCenterSorted,'$wCenterSorted');
            $wCenter = [];
            if ( !isset($wCenterSorted['statuses']) ) continue;
            
            $start = $wCenterSorted['statuses']['start']??[];
            $startID = $start['id']??0;
            $end = $wCenterSorted['statuses']['end']??[];
            $endID = $end['id']??0;

            // запомним даты, для каждого участка
            // что бы выбрать самые последние ( бывает если есть несколько одинаковых статусов ) используем LastDateFinder
            // это нужно что бы отобразить более точную информацию о текущем местоположении модели.
            // Поправки делает - ExpiredCorrection
            
            foreach ( $statusesTable as $status )
            {
                if ( $status['status_id'] == $startID )
                    LastDateFinder::setDatesStart($status);
                if ( $status['status_id'] == $endID )
                    LastDateFinder::setDatesEnd($status);
            }
            $wCenter['start'] = LastDateFinder::getDateStart();
            $wCenter['end'] = LastDateFinder::getDateEnd();
            LastDateFinder::clear(); // стираем данные внутри, для следующего участка

            if (isset($wCenter['end']['date']))
                $wCenter['end']['date'] = formatDate($wCenter['end']['date']);
            if (isset($wCenter['start']['date']))
                $wCenter['start']['date'] = formatDate($wCenter['start']['date']);
        
            $wCenters[$cKey] = $wCenter;
        } 
        //END распределяем даты статусов по таблице
        //debug($wCenters,'$wCenters',1);
        
        // Корректировки по датам, и поставить просроченные
        ExpiredCorrection::run($wCenters, $lastStatus);

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

        if ( $xlsx )
        {
            $result=[];
            $result['wCenters'] = $wCenters;
            $result['sizeRange'] = $sizeRange;
            $result['trFill'] = $trFill;
            return $result;
        }

        //debug($wCenters,'$wCenters');

        require _WEB_VIEWS_ . Router::getControllerNameOrigin()."/drawTableRow.php";
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
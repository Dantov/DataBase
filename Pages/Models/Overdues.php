<?php
namespace models;

use soffit\{Router};
use libs\classes\AppCodes;

class Overdues extends Main 
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
     * таблица просроченных
     * @param bool $xls
     * @return mixed
     * @throws \Exception
     */
    public function getWorkingCentersExpired( bool $xls = false ) : array
    {
        $workingCenters = $this->getWorkingCentersSorted();

        // идем по выбранным моделям
        $countAll = 0;
        $countAllExpired = 0;
        $stockModelIDs = '';
        //debug($this->row,'$this->row',1);
        foreach ( $this->row as $stockModel )
        {
            $stockModelIDs .= $stockModel['id'].',';

            $modelStatus = $stockModel['status']; // берем ID статуса и смотри к какому центру он принадлежит

            foreach ( $workingCenters as &$wCenter )
            {
                // Посчитали все! найдем к какому участку принадлежит статус модели
                //debug($wCenter,'',1);
                $wCenterID = $wCenter['id'];
                foreach ( $this->statuses as $status )
                {
                    if ( $status['location'] == $wCenterID ) // статус принадлежит данному участку
                    {
                        if ( $status['id'] == $modelStatus ) // это наш последний статус - считаем его в текущий участок
                        {
                            if ( !isset($wCenter['countAll']) ) $wCenter['countAll'] = 0;
                            $wCenter['countAll']++;
                            $wCenter['ids'][] = $stockModel['id'];
                        }
                    }
                }
            }
            $countAll++;
        }
        unset($stockModel,$wCenter,$status,$modelStatus,$statusStartID,$statusEndID);

        //распределим статусы по моделям
        if ( !$stockModelIDs = trim($stockModelIDs,',') )
            throw new \Exception(AppCodes::getMessage(AppCodes::SERVER_ERROR)['message'], AppCodes::SERVER_ERROR );

        $statusQuery = $this->findAsArray(" SELECT * FROM statuses WHERE pos_id in ($stockModelIDs) ");

        if ( !count($statusQuery) )
            throw new \Exception(AppCodes::getMessage(AppCodes::SERVER_ERROR), AppCodes::SERVER_ERROR );
   

        $modelsStatuses = [];
        foreach( $statusQuery as $modelStatusesR )
        {
            $modelsStatuses[$modelStatusesR['pos_id']][] = $modelStatusesR;
        }
        //debug($modelsStatuses,'',1);

        /*
         * Будем проходить по раб. центрам с конца
         * что бы начинать работу с последними статусами. актуальной информацией
         */
        $workingCenters = array_reverse($workingCenters); // Возвращает массив с элементами в обратном порядке

        //добавим к $row модели её статусы
        foreach ( $this->row as $stockModel )
        {
            // если отложено или снято - уходим
            if ( $stockModel['status'] == 11 || $stockModel['status'] == 88 ) continue;

            $pos_id = $stockModel['id']; // ID модели в табл. сток
            //$modelStatuses = $modelsStatuses[$pos_id]?:[]; //массив статусов этой модели
            $modelStatuses = $modelsStatuses[$pos_id]??[]; //массив статусов этой модели
            //debug($modelStatuses,'$modelStatuses'.$c++);

            $dateStart = '';
            foreach ( $workingCenters as &$wCenter )
            {
                if ( !isset($wCenter['statuses']) ) continue;

                $statusStartID = isset($wCenter['statuses']['start']['id']) ? $wCenter['statuses']['start']['id'] : false;
                $statusEndID = isset($wCenter['statuses']['end']['id']) ? $wCenter['statuses']['end']['id'] : false;

                //debug($modelStatuses,'',1);
                // ищем статус принятия в этом массиве для данного участка
                // смотрим на его дату, если она 3х дневной давности - ищем статус сдачи
                // если его нет = модель просрочена ($expired=true) для этого участка. Но может быть уже сдана на след. участках
                // Поэтому если есть статус сдачи на след участке $expired=false

                // возьмем даты статусов сдачи/принятия для текущего участка
                foreach ($modelStatuses as $modelStatus)
                {
                    /*
                     * найдем статус сдачи. Будем работать с ним в дальнейшем, если не будет статуса сдачи.
                     */
                    if ( $statusStartID == $modelStatus['status'] ) $dateStart = $modelStatus['date'];

                    /*
                     * если есть статус сдачи на текущем учасике - модель НЕ просрочена априори. Переходим к следующей.
                     */
                    if ($statusEndID == $modelStatus['status'] ) continue 3;
                }

                //debug($wCenter);
                if ( !empty($dateStart) )
                {
                    $plusDay = 2 * 24 * 60 * 60; // +сутки в раб. день // 1 дней; 24 часа; 60 минут; 60 секунд
                    if ( date("w", strtotime($dateStart)) == 5 ) $plusDay = 4 * 24 * 60 * 60; // +3 суток с рятницы
                    if ( date("w", strtotime($dateStart)) == 6 ) $plusDay = 3 * 24 * 60 * 60; // +2 суток с субботы
                    $dateStart = strtotime($dateStart) + $plusDay;

                    if ( $this->today > $dateStart )
                    {
                        if ( !isset($wCenter['expired']) ) $wCenter['expired'] = 0;
                        $wCenter['expired']++;
                        $wCenter['expiredIds'][] = $pos_id;
                        $countAllExpired++;
                    }
                    continue 2;
                }
            }
        }

        $workingCenters = array_reverse($workingCenters);

        if ( $xls === true )
        {
            $res['countAll'] = $countAll;
            $res['countAllExpired'] = $countAllExpired;
            $res['workingCenters'] = $workingCenters;
            return $res;
        }

        $sourceStart = Router::getControllerNameOrigin()."/tableExpiredStart.php";
        $sourceRow = Router::getControllerNameOrigin()."/tableExpiredRow.php";
        $sourceEnd = Router::getControllerNameOrigin()."/tableExpiredEnd.php";

        if ( !file_exists(_WEB_VIEWS_ . $sourceStart) )
                throw new \Exception("File ". $sourceStart ." not found in: " . __METHOD__, 404);
        if ( !file_exists(_WEB_VIEWS_ . $sourceRow) )
                throw new \Exception("File ". $sourceRow ." not found in: " . __METHOD__, 404);
        if ( !file_exists(_WEB_VIEWS_ . $sourceEnd) )
                throw new \Exception("File ". $sourceEnd ." not found in: " . __METHOD__, 404);

        ob_start();
            require_once  _WEB_VIEWS_ . $sourceStart;

            foreach ( $workingCenters as $workingCenter )
                $this->drawTableExpiredRow( $workingCenter );
            
            require_once  _WEB_VIEWS_ . $sourceEnd;

            $result['models'] = ob_get_contents();
        ob_end_clean();
        $result['wholePos'] = count($workingCenters);

        return $result;
    }
    protected function drawTableExpiredRow( array $workingCenter )
    {
        $users = $this->getUsers();
        $wcUser = [];
        foreach ( $users as $user )
        {
            if ( $user['id'] == $workingCenter['user_id'] )
            {
                $wcUser['fio'] = $user['fio'];
                $wcUser['fullFio'] = $user['fullFio'];
            }
        }

        require _WEB_VIEWS_ . Router::getControllerNameOrigin()."/tableExpiredRow.php";
    }


}
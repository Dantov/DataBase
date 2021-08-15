<?php
namespace Views\_Main\Models;
use Views\_Globals\Models\General;
use Views\_Globals\Models\User;
use Views\_SaveModel\Models\ImageConverter;
use Views\vendor\libs\classes\AppCodes;

class Main extends General {

    public $assist;
    public $row;
    public $wholePos;
    //public $workingCenters;
    public $today;

    /**
     * Main constructor.
     * @param bool $assist
     * @param bool $user
     * @param array $foundRow
     * @throws \Exception
     */
    public function __construct($assist=false, $user=false, $foundRow=[] )
    {
        parent::__construct();
        if ( isset($assist) ) $this->assist = $assist;

        $this->row = !empty($foundRow) ? $foundRow : [];
        $this->today = time();
        $this->connectToDB();
    }

    /**
     * @return int
     */
    public function getToday()
    {
        return $this->today;
    }

    /**
	 * @param $iter - элемент массива
     * @return array
     */
    public function getRow($iter=false)
	{
		if ( is_int($iter) ) return $this->row[$iter];
		return $this->row;
	}
	
	public function getVeriables()
	{
		$result = array();
		
		if ( $this->assist['sortDirect'] == "ASC" )  { $result['chevron_']  = "triangle-top";    $result['chevTitle'] = "По возростанию";}  //1
		if ( $this->assist['sortDirect'] == "DESC" ) { $result['chevron_']  = "triangle-bottom"; $result['chevTitle'] = "По убыванию";} //2
		
		if ( $this->assist['reg'] == "number_3d" )	 $result['showsort'] = "№3D";
		if ( $this->assist['reg'] == "vendor_code" )   $result['showsort'] = "Арт.";
		if ( $this->assist['reg'] == "date" ) 		 $result['showsort'] = "Дате";
		if ( $this->assist['reg'] == "status" ) 	     $result['showsort'] = "Статусу";
		if ( $this->assist['reg'] == "model_type" )    $result['showsort'] = "Типу";
		
		if ( !isset($this->assist['page']) ) $this->assist['page'] = 0;
		
		$result['activeSquer'] = "";
		$result['activeWorkingCenters'] = "";
		$result['activeList']  = "";
		$result['activeSelect'] = "";
		
		if ( $this->assist['drawBy_'] == 1 ) $result['activeSquer'] = "btnDefActive";
		if ( $this->assist['drawBy_'] == 2 ) $result['activeList']  = "btnDefActive";
		if ( $this->assist['drawBy_'] == 3 ) $result['activeWorkingCenters']  = "btnDefActive";
		if ( $this->assist['drawBy_'] == 4 ) $result['activeWorkingCenters2']  = "btnDefActive";
		if ( $_SESSION['selectionMode']['activeClass'] == "btnDefActive" ) $result['activeSelect'] = "btnDefActive";
		
		$result['collectionName'] = $this->assist['collectionName'];
		
		return $result;
	}


    /**
     * @return mixed
     * @throws \Exception
     */
    public function getStatusesSelect()
	{
        $this->getUsers();
        // все возможные статусы
        $statuses = $this->statuses;
        $workingCentersDB = $this->workingCentersDB;

        foreach ( $workingCentersDB as $key => &$workingCenters )
        {
            foreach ( $workingCenters as $wcKey => &$subUnit )
            {
                $subUnit['statuses'] = []; // массив доступных статусов
                $subUnit['user'] = ''; // ответственный из Users
                foreach ( $statuses as $status )
                {
                    if ( $status['location'] == $subUnit['id'] ) $subUnit['statuses'][] = $status;

                    // проверим есть ли Ответственный
                    foreach ( $this->users as $user )
                    {
                        if ($user['id'] == $subUnit['user_id'])
                        {
                            $subUnit['user'] = $user['fio'];
                            break;
                        }
                    }
                    if (empty($subUnit['user']) ) $subUnit['user'] = 'Нет';

                }

                // удалим подУчастки с пустыми статусами
                if ( empty($subUnit['statuses']) ) unset($workingCenters[$wcKey]);
            }

            // удалим пустые Участки
            if ( empty($workingCenters) ) unset($workingCentersDB[$key]);
        }

        return $workingCentersDB;
    }



	public function getModelsFormStock()
    {

        $where = "WHERE collections<>'Детали'";
        if ( $this->assist['collectionName'] != 'Все Коллекции' ) $where = "WHERE collections like '%{$this->assist['collectionName']}%'";

        $regStat = 0;
        foreach ( $this->statuses as $status )
        {
            if ( $status['name_ru'] == $this->assist['regStat'] )
            {
                $regStat = (int)$status['id'];
                break;
            }
        }

        if ( !empty($_SESSION['assist']['drawBy_'] === 4) )
        {
            if ( !empty($_SESSION['assist']['wcSort']['ids']) )
            {
                $_SESSION['assist']['regStat'] = $this->assist['regStat'] = 'Нет';
                if ( $in = $this->dopSortByWC() ) $where .= " AND status " . $in;
            }
        }

        if ( $this->assist['regStat'] !== "Нет" && $this->assist['byStatHistory'] !== 1 )
        {
            $where .= " AND status='$regStat'";
        }

		$selectRow = "SELECT * FROM stock " . $where . " ORDER BY " .$this->assist['reg']." ".$this->assist['sortDirect'];
		$result_sort = mysqli_query($this->connection, $selectRow);

		if ( !$result_sort ) {
			printf( "Error SELECT: %s\n", mysqli_error($this->connection) );
			return false;
		}
		while( $row = mysqli_fetch_assoc($result_sort) ) $this->row[] = $row;

		if ( $this->assist['byStatHistory'] === 1 )
        {

            $dates = [];
            if ( !empty($_SESSION['assist']['byStatHistoryFrom'])) $dates['from'] = $_SESSION['assist']['byStatHistoryFrom'];
            if ( !empty($_SESSION['assist']['byStatHistoryTo'])) $dates['to'] = $_SESSION['assist']['byStatHistoryTo'];
            //debug($dates,',',1);
            Search::byStatusesHistory($this->connection, $regStat, $this->row, $dates);
        }

		return $this->row;
	}


    /**
     * Вывод по комплектам
     *
     * @return array
     * @throws \Exception
     */
	public function getModelsByRows()
    {
        $result = [
            'showByRows' => '',
            'posIter' => 0,
            'ComplShown' => 0,
            'wholePos' => 0
        ];
        $result['posIter'] = count($this->row); // кол-во всех моделей
        if ( empty($result['posIter']) ) return $result;

		$complArray = $this->countComplects();
		$this->wholePos = $result['wholePos'] = count($complArray); // кол-во комплектов

        $from = $this->assist['page'] * $this->assist['maxPos'];
        $to = ($this->assist['page'] + 1) * $this->assist['maxPos'];

        $rowImages = [];
        $rowStls = [];

        $posIds = '(';
        for ( $i = $from; $i < $to; $i++ ) $posIds .= $this->row[$i]['id'].',';
        $posIds = trim($posIds,',') . ')';
        $imagesQuery = mysqli_query($this->connection, " SELECT pos_id,img_name FROM images WHERE pos_id IN $posIds AND main=1 ");
        $stlQuer = mysqli_query($this->connection, " SELECT pos_id,stl_name FROM stl_files WHERE pos_id IN $posIds ");
        while ( $image = mysqli_fetch_assoc($imagesQuery) ) $rowImages[$image['pos_id']] = $image;
        while ( $stl = mysqli_fetch_assoc($stlQuer) ) $rowStls[$stl['pos_id']] = $stl;

        ob_start();
        for ( $i = $from; $i < $to; $i++ )
		{
			if ( !isset($complArray[$i]['id']) || empty($complArray[$i]['id']) ) continue;
			$complIterShow = $i+1;
			$thisVC = !empty($complArray[$i]['vendor_code']) ? "&#8212; Артикул: <b>{$complArray[$i]['vendor_code']}</b>" : "";

			require _viewsDIR_ . "_Main/includes/byRows.php";

			// вывод моделей в строке
			foreach( $complArray[$i]['id'] as &$value )
			{
				$result['showByRows'] .= $this->drawModel( $value, $rowImages, $rowStls, true );
				$result['iter']++; // счетчик отрисованных моделей в комплекте
			}

			echo    "</div>";
			echo "</div>";
			$result['ComplShown']++; // счетчик отрисованных комплектов
		}

        $result['showByRows'] = ob_get_contents();
        ob_end_clean();
		return $result;
	}





    /**
     * Стандартный вывод плиткой
     *
     * @return array
     * @throws \Exception
     */
    public function getModelsByTiles()
	{

		$result = [
		    'showByTiles' => '',
            'iter' => 0,
            'wholePos' => 0
        ];
		$this->wholePos = $result['wholePos'] = count($this->row);
		if ( empty($this->wholePos) ) return $result;
		
		$from = $this->assist['page'] * $this->assist['maxPos'];
		$to = ($this->assist['page'] + 1) * $this->assist['maxPos'];

		$posIds = '(';
		for ( $i = $from; $i < $to; $i++ ) $posIds .= $this->row[$i]['id'].',';
		$posIds = trim($posIds,',') . ')';

        $rowImages = $this->findAsArray(" SELECT pos_id,img_name,main,onbody,sketch FROM images WHERE pos_id IN $posIds ");
        $rowStls = $this->findAsArray(" SELECT pos_id,stl_name FROM stl_files WHERE pos_id IN $posIds ");
        foreach ( $rowStls as $key => $rowStl )
        {
            $rowStls[$rowStl['pos_id']] = $rowStl;
            unset($rowStls[$key]);
        }


		ob_start();
		for ( $i = $from; $i < $to; $i++ )
		{
		// что б не выводил пустые позиции в конце, если кол-во отображаемых больше кол-ва найденных.
			if ( !isset($this->row[$i]['id']) ) continue; 
			$this->drawModel( $this->row[$i], $rowImages, $rowStls);
			$result['iter']++; // счетчик отрисованных позиций
		}
		$result['showByTiles'] = ob_get_contents();
		ob_end_clean();


		return $result;
	}

	/**
     * Таблица рабочих центров
	 * Приложение №1
     *
	 */
    public function getModelsByWorkingCenters()
	{
        $drawBy = $_SESSION['assist']['drawBy_'];
        $result = array();
        $this->wholePos = $result['wholePos'] = count($this->row);
        $this->getWorkingCentersSorted();

        ob_start();
        $source = '';
        switch ($drawBy)
		{
			case 3:
                $source = "_Main/includes/drawTableStart.php";
				break;
            case 4:
                $source = "_Main/includes/drawTable2Start.php";
                break;
		}
        require _viewsDIR_ . $source;

        for ( $i = $this->assist['page']*$this->assist['maxPos']; $i < ($this->assist['page'] + 1)*$this->assist['maxPos']; $i++ )
        {
            // что б не выводил пустые позиции в конце, если кол-во отображаемых больше кол-ва найденных.
            if ( !isset($this->row[$i]['id']) ) continue;

            $drawn = false;
             if ( $drawBy === 3 ) $drawn = $this->drawTableRow( $this->row[$i] );
             if ( $drawBy === 4 ) $drawn = $this->drawTable2Row( $this->row[$i] );

             if ( $drawn ) $result['iter']++; // счетчик отрисованных позиций
        }

        if ( $drawBy === 3 || $drawBy === 5 ) echo "</tbody></table></div>";
        if ( $drawBy === 4 ) echo "</tbody></table>";

        $result['showByWorkingCenters'] = ob_get_contents();
        ob_end_clean();

        return $result;
    }


    /**
	 * выбирает все статусы модели из таблицы statuses
     * @param $id - модели pos_id
     * @return array
     */
    public function getStatusesTable($id)
	{
		$res = [];
        $statsQuery = mysqli_query($this->connection, " SELECT id,status,name,date,pos_id FROM statuses WHERE pos_id='$id' ");
        while( $stats_row = mysqli_fetch_assoc($statsQuery) )
		{
            $stats_row['status_id'] = $stats_row['status'];

            foreach ( $this->statuses as $status )
			{
				if ( $status['id'] == $stats_row['status_id'] ) $stats_row['status'] = $status;
			}
            $res[] = $stats_row;
		}
        return $res;
	}


	// Приложение №1
    /**
     * @param $row - Модель из таблицы Stock.
     * @param $xlsx - флаг о том. что надо предоставить данные под excel.
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
        		
            $startID = $wCenterSorted['statuses']['start']['id'];
            $endID = $wCenterSorted['statuses']['end']['id'];

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

        require _viewsDIR_ . "_Main/includes/drawTableRow.php";
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


	/*
	 * приложение №2
	 */
	protected function drawTable2Row($row, $xlsx=false)
    {
        //$wCenters = $this->getWorkingCentersSorted();

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
		$wcSort = $_SESSION['assist']['wcSort']['name'] ?: false;
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
        $wcID = $workingCenter['id'];
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
		
        require _viewsDIR_ . "_Main/includes/drawTable2Row.php";

        return true;
    }


    /**
     * таблица просроченных
     * @param bool $xls
     * @return mixed
     * @throws \Exception
     */
    public function getWorkingCentersExpired($xls = false)
	{
        $workingCenters = $this->getWorkingCentersSorted();

        // идем по выбранным моделям
		$countAll = 0;
		$countAllExpired = 0;
        $stockModelIDs = '(';
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
						if ( $status['id'] == $modelStatus ) // это нашь последний статус - считаем его в текущий участок
						{
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
        $stockModelIDs = trim($stockModelIDs,',').')';
        $statusQuery = mysqli_query($this->connection, " SELECT * FROM statuses WHERE pos_id in $stockModelIDs ");

        //if ( !$statusQuery ) header("location: "._views_HTTP_ ."Main/index.php?row_pos=3");
        if ( !$statusQuery->num_rows )
            throw new \Exception(AppCodes::getMessage(AppCodes::SERVER_ERROR), AppCodes::SERVER_ERROR );
            //header("location: "._views_HTTP_ ."Main/index.php?row_pos=1");
        //if ( $xls ) debug($statusQuery);

        $modelsStatuses = [];
        while ( $modelStatusesR = mysqli_fetch_assoc($statusQuery) )
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
            $modelStatuses = $modelsStatuses[$pos_id]?:[]; //массив статусов этой модели
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
                        $wCenter['expired']++;
                        $wCenter['expiredIds'][] = $pos_id;
                        $countAllExpired++;
					}
                    continue 2;
                }
			}
		}

        $workingCenters = array_reverse($workingCenters);
		//debug($workingCenters,'',1);

        if ( $xls === true )
        {
            $res['countAll'] = $countAll;
            $res['countAllExpired'] = $countAllExpired;
            $res['workingCenters'] = $workingCenters;
            return $res;
        }

		ob_start();
        require_once  _viewsDIR_ . "_Main/includes/drawTableExpired_Start.php";

        foreach ( $workingCenters as $workingCenter )
		{
            $this->drawTableExpiredRow( $workingCenter );
		}

        echo '<tr class="active text-bold">
					<td title="" style="text-align: right!important;">Всего /</td>
					<td title="" style="text-align: left!important;">Просроченных</td>
					<td class="success" title="Всего">'.$countAll.'</td>
					<td class="danger" title="Всего Просроченных">'.$countAllExpired.'</td>
					<td title=""></td>
				</tr>';
        echo "</tbody></table></div>";

        $result['showByWorkingCenters'] = ob_get_contents();
        $result['wholePos'] = count($workingCenters);
        ob_end_clean();

        return $result;
	}
    public function drawTableExpiredRow( $workingCenter )
	{
        $users = $this->getUsers();
        $wcUser = [];
        //debug($workingCenter);
        foreach ( $users as $user )
		{
			if ( $user['id'] == $workingCenter['user_id'] )
			{
                $wcUser['fio'] = $user['fio'];
                $wcUser['fullFio'] = $user['fullFio'];
			}
		}

        require _viewsDIR_ . "_Main/includes/drawTableExpired_Row.php";
	}


    /**
     * плиткой
     * @param array $rowImages
     * @param array $row - each model data
     * @param $comlectIdent true - drawModel вызвана в отрисовке комплекта
     * @throws \Exception
     */
	private function drawModel(&$row, &$rowImages, $rowStls, $comlectIdent = false)
	{
		//по дефолту
		$vc_show = "";
		if ( !empty($row['vendor_code']) ) $vc_show = " | ".$row['vendor_code'];
		$columns = $comlectIdent === true ? 3 : 2;

        $images = [];
        foreach ($rowImages as &$thisImage)
            if ( $thisImage['pos_id'] === $row['id'] )
                $images[] = $thisImage;

        //debug($images,'$images');

        //-------------------
        $showimg = '';
        $mainIsset = false;
        $setMainImg = function($which) use (&$mainIsset, &$images, &$showimg)
        {
            foreach ( $images as &$image )
            {
                if ($image[$which] == 1 ) {
                    $showimg = $image['img_name'];
                    $mainIsset = true;
                    break;
                }
            }
        };
        if ( !$mainIsset ) $setMainImg('main');
        if ( !$mainIsset ) $setMainImg('sketch');
        if ( !$mainIsset ) $setMainImg('onbody');
        if ( !$mainIsset ) {
            foreach ( $images as &$image )
            {
                $showimg = $image['img_name'];
                break;
            }
        }
        //-------------------


        $path = $row['number_3d'].'/'.$row['id'].'/images/';
		if ( !file_exists(_stockDIR_. $path . $showimg) ) // file_exists работает только с настоящим путём!! не с HTTP
		{
		    $showimg = _stockDIR_HTTP_ . "default.jpg";
		} else {
		    //Файл Есть!

            //Проверим на наличие превью
            $prevImgName = $this->checkSetPreviewImg($path, $showimg);
            $showimg = $prevImgName ? _stockDIR_HTTP_.$path.$prevImgName : _stockDIR_HTTP_.$path.$showimg;
        }
		
		$btn3D = false;
		if ( array_key_exists($row['id'], $rowStls) ) $btn3D = true;

		// смотрим отрисовывать ли нам кнопку едит
		$editBtn = false;
        if ( User::permission('editModel') )
        {
            $editBtn = true;
        } elseif ( User::permission('editOwnModels') ) {
            $userRowFIO = explode(' ', $this->user['fio'])[0];
            $authorFIO = $row['author'];
            $modellerFIO = $row['modeller3D'];
            $jewelerName = $row['jewelerName'];
            if ( mb_stristr($authorFIO, $userRowFIO) !== FALSE || mb_stristr($modellerFIO, $userRowFIO) !== FALSE || mb_stristr($jewelerName, $userRowFIO) !== FALSE )
                $editBtn = true;
        }
		
		$status = $this->getStatus($row);
		$labels = $this->getLabels($row['labels']);	
		$checkedSM = self::selectionMode($row['id']);
		
        // Укорочение длинны типа модели
        $modTypeCount = mb_strlen($row['model_type']);
        if ( $modTypeCount > 14 )
        {
            $modTypeStr = mb_substr($row['model_type'], 0, 11);
            $modTypeStr.= "...";
        } else {
            $modTypeStr = $row['model_type'];
        }

        // проверка на всю ширину
        $columnsLG = 2;
        if ( $_SESSION['assist']['containerFullWidth'] == 1 )
            $columnsLG = 1;
        
		require _viewsDIR_. "_Main/includes/drawModel.php";
	}
	
	
	private static function selectionMode($id) 
	{
		$defRes = ['inptAttr'=>'','class'=>'glyphicon-unchecked','active'=>'hidden'];
		if ( $_SESSION['selectionMode']['activeClass'] == "btnDefActive" ) {
			
			$defRes['active'] = "";
			
			$selectedModels = $_SESSION['selectionMode']['models'];
			if ( !empty($selectedModels) ) {
				
				if ( array_key_exists($id, $selectedModels) ) {
					$defRes['inptAttr'] = "checked";
					$defRes['class'] = "glyphicon-check";
					
					return $defRes;
				}
			}
		}
		return $defRes;
	}
	
	
	public static function selectedModelsByLi() {
		$result = "";
		$selectedModels = $_SESSION['selectionMode']['models'];

		foreach ( $selectedModels as &$value ) {
			$result .= '<li data-id="'.$value['id'].'"><a href="../ModelView/index.php?id='. $value['id'] .'" >'. $value['name'] .'</a></li>';
		}
		return $result;
	}
	
	public function countComplects() {

		$numRows = count($this->row);
		$savedrow = array();
		$complects = array();
		$cIt = 0;
		
		for ( $i = 0; $i < $numRows; $i++ ) {
			if ( empty($this->row[$i]['number_3d']) ) continue;
			$number_3d = $this->row[$i]['number_3d'];
			
			foreach ( $savedrow as &$value ) {
			// проверяем есть ли этот номер в массиве. если есть то пропускаем все такие номера, они уже посчитаны
				if ( $value == $number_3d ) continue(2);
			}

			for ( $j = 0; $j < $numRows; $j++ ) {
				
				$model_type = $this->row[$j]['model_type'];
				
				// если совпадают - значит это комплект
				if ( $number_3d == $this->row[$j]['number_3d'] ) {
					
					$id = $this->row[$j]['id'];
					$complects[$cIt]['number_3d'] = $this->row[$j]['number_3d'];
					$complects[$cIt]['vendor_code'] = $this->row[$j]['vendor_code'];
					$complects[$cIt]['modeller3D'] = $this->row[$j]['modeller3D'];
					$complects[$cIt]['collection'] = $this->row[$j]['collections'];
					
					$complects[$cIt]['id'][$id]['id'] = $this->row[$j]['id'];
					$complects[$cIt]['id'][$id]['number_3d'] = $this->row[$j]['number_3d'];
					$complects[$cIt]['id'][$id]['author'] = $this->row[$j]['author'];
					$complects[$cIt]['id'][$id]['modeller3D'] = $this->row[$j]['modeller3D'];
					$complects[$cIt]['id'][$id]['model_type'] = $this->row[$j]['model_type'];
					$complects[$cIt]['id'][$id]['labels'] = $this->row[$j]['labels'];
					$complects[$cIt]['id'][$id]['status'] = $this->row[$j]['status'];
					$complects[$cIt]['id'][$id]['date'] = $this->row[$j]['date'];
					
					if (  $this->toPdf === true ) {
						$complects[$cIt]['model_type'][$id]['id'] = $id;
						$complects[$cIt]['model_type'][$id]['model_type'] = $model_type;
						$complects[$cIt]['model_type'][$id]['images'] = $this->get_Images_FromPos($id);
						$complects[$cIt]['model_type'][$id]['dop_VC'] = $this->get_DopVC_FromPos($id);
						$complects[$cIt]['model_type'][$id]['model_weight'] = $this->row[$j]['model_weight'];
						$complects[$cIt]['model_type'][$id]['status'] = $this->row[$j]['status'];
					}
					
					$savedrow[] = $number_3d; // сохранем номер в массив, как посчитанный
					
				}
			}
			$cIt++;
		}
		return $complects;
	}
	public function drawPagination()
    {
		$pagination = '';
		
		$max_shown_pagin = 10; // максимальное число отображаемых квадратиков пагинации
		//округлили вверх - это общее кол-во страниц(квадратиков)
		$paginLength = ceil( $this->wholePos / $this->assist['maxPos'] );
	
		$pagination .= '<nav aria-label="Page navigation">';
        $pagination .= '<ul class="pagination">';
		
		// если был переход на след. часть квадратиков то рисуем кнопку назад
		if ( isset($this->assist['st_prevPage']) && $this->assist['startFromPage'] != 0 ) {
			$startI = $this->assist['startFromPage'] - $max_shown_pagin; // флаг - с какой стр. начинать рисовать квадратики
			$pagination .= "
				<li>
					<a href=\"/main/?page=$startI&start_FromPage=$startI\" aria-label=\"Next\" title=\"Назад на пред. 10\">
						<span aria-hidden=\"true\">&laquo;</span>
					</a>
				</li>
			";
		}
		
		// цикл по отрисовке квадратиков
		for ( $i = $this->assist['startFromPage']; $i < $paginLength; $i++ ) {
			
			$classAct = '';
			if ( $this->assist['page'] == $i ) $classAct = 'class="active"';
			$pagination .= "<li $classAct>";
			$pagination .= '<a href="/main/?page='.$i.'">'.($i+1).'</a>';
			$pagination .= '</li>';
			
			// если это не первая стр. то начинаем проверять на кратность макс. разрешенных квадратиков т.е 10
			if ( $i != 0 )	$nn = ($i+1) / $max_shown_pagin; 
			
			// если остаток от деления целый, знач завершаем цикл и рисуем кнопку вперед
			// для след. страниц пагинации.
			if ( is_int($nn) ) {
				$nextI = $i + 1; // определяем след. страницу на которую перейдем после клика
				$pagination .= "
				<li>
					<a href=\"/main/?page=$nextI&start_FromPage=$nextI&st_prevPage=$nextI\" aria-label=\"Next\" title=\"Вперед на след. 10\">
						<span aria-hidden=\"true\">&raquo;</span>
					</a>
				</li>
				";
				break;
			}
		}
		
		$pagination .= '</ul>';
        $pagination .= '</nav>';
		return $pagination;
	}
}
<?php
namespace controllers;

use models\{LocationCenters};
use widgets\Pagination;

class LocationCentersController extends MainController
{

    /**
     * @throws \Exception
     * // ============== Locations centers ============== //
     */
    public function action()
    {
		$session = $this->session;
        $assist = $session->getKey('assist');
        $assist['drawBy_'] = 4;
        $session->setKey('assist', $assist);

        $activeLC = "btnDefActive";
		$loc = new LocationCenters($this->searchQuery);
		$toolsPanel = $loc->getToolsPanelVars();

		/** New Pagination. Starts before main stock query*/
		$totalM = $loc->totalModelsCount();
		$pagination = new Pagination( $totalM, $assist['maxPos'], $assist['page'] );
        $loc->start = $pagination->getStart();
        $loc->perPage = $assist['maxPos'];

		//если нет поиска, выбираем из базы
		//if ( empty($this->foundRows) )
			$loc->getModelsFormStock( array_key_exists("showall",$this->getQueryParams()) );

		// начинаем вывод моделей
        $wholePos = 0; $iter = 0; $statistic ='';
		//if ( empty($session->nothing) ) 
        //{
			// менюшка для выборки по рабочим центрам
			$workCentersSort = true;
			$workingCenters = $loc->workingCentersDB;
			
			$getterModels = $loc->getModels();
			$showModels = $getterModels['models'];
			$iter = $getterModels['iter'];

			$wholePos = $totalM;

			$modelsFrom = $loc->start;
        	$modelsTo = $loc->start + $iter;

            $statistic = $loc->statusBar('tiles', compact(['modelsFrom','modelsTo','wholePos','iter']));
		//}

		$compacted = compact(['toolsPanel','activeLC','showModels','iter','wholePos',
			'statistic','pagination','workCentersSort','workingCenters']);
		return $this->render('loc', $compacted);
	}


}
<?php
namespace controllers;

use models\{
    Kits, SetSortModel, Search, 
    ToExcel, PushNotice, SelectionsModel
};
use widgets\Pagination;


class KitsController extends MainController
{

    /**
     * @throws \Exception
     */
    public function action()
    {
		$session = $this->session;
        $assist = $session->getKey('assist');
        $assist['drawBy_'] = 2;
        $session->setKey('assist', $assist);

        $activeKits = "btnDefActive";
		$kits = new Kits($this->searchQuery);
		$toolsPanel = $kits->getToolsPanelVars();

		/** New Pagination. Starts before main stock query*/
		$totalM = $kits->totalModelsCount();
		$pagination = new Pagination( $totalM, $assist['maxPos'], $assist['page'] );
        $kits->start = $pagination->getStart();
        $kits->perPage = $assist['maxPos'];

		//если нет поиска, выбираем из базы
		//$totalModelsFound = 0;
		//if ( !trueIsset($this->searchQuery) )
		$totalModelsFound = $kits->getModelsFormStock( array_key_exists("showall",$this->getQueryParams()) );

		// начинаем вывод моделей
        $wholePos = 0; $iter = 0; $statistic = '';
		//if ( empty($session->nothing) ) 
        //{
			$models = $kits->getModels();

			$showModels = $models['showByRows'];
			$wholePos   = $models['wholePos']??0;
			$iter       = $models['iter']??0;
			$posIter    = $models['posIter']??0;

			$ComplShown = $kits->totalKits();
			$modelsFrom = $kits->start;
        	$modelsTo   = $kits->start + $iter;

            $statistic = $kits->statusBar('kits',compact(['totalM','modelsFrom','modelsTo','wholePos','iter','posIter','ComplShown']));
		//}

		//For tiles and kits
		$this->includeJSFile(name: 'Selects.js', options: ['defer','timestamp','path'=>'web/views/main/js/']);
		
		$compacted = compact(['toolsPanel','activeKits','showModels','iter','wholePos','statistic','pagination']);
		return $this->render('kits', $compacted);
	}


}
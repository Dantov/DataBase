<?php
namespace controllers;

use models\{WorkingCenters};
use widgets\Pagination;


class WorkingCentersController extends MainController
{

    /**
     * @throws \Exception
     */
    public function action()
    {
		$session = $this->session;
        $assist = $session->getKey('assist');
        $assist['drawBy_'] = 3;
        $session->setKey('assist', $assist);

        $activeWC = "btnDefActive";
		$wc = new WorkingCenters($this->searchQuery);
		$toolsPanel = $wc->getToolsPanelVars();

		/** New Pagination. Starts before main stock query*/
		$totalM = $wc->totalModelsCount();
		$pagination = new Pagination( $totalM, $assist['maxPos'], $assist['page'] );
        $wc->start = $pagination->getStart();
        $wc->perPage = $assist['maxPos'];

		//если нет поиска, выбираем из базы
		//if ( !trueIsset($this->foundRows) )
			$wc->getModelsFormStock( array_key_exists("showall",$this->getQueryParams()) );
		
        $wholePos = 0; $iter = 0; $statistic = '';
		//if ( empty($session->nothing) )
        //{
			$getterModels = $wc->getModels();
			$showModels = $getterModels['models'];
			$iter = $getterModels['iter'];
			//$wholePos = $getterModels['wholePos'];

			$wholePos = $totalM;

			$modelsFrom = $wc->start;
        	$modelsTo = $wc->start + $iter;

        	$statistic = $wc->statusBar('tiles', compact(['modelsFrom','modelsTo','wholePos','iter']));

            $this->varBlock['container'] = 2; //уберем класс container в шаблоне чтоб стало шире
		//}

		$compacted = compact(['toolsPanel','activeWC','showModels','iter','wholePos','statistic','pagination']);
		return $this->render('wc', $compacted);
	}


}
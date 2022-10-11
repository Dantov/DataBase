<?php
namespace controllers;

use models\{Overdues};


class OverduesController extends MainController
{

    /**
     *  ============== Табличка участов с просроченными ============== 
     * @throws \Exception
     */
    public function action()
    {
		$session = $this->session;
        $assist = $session->getKey('assist');
        $assist['drawBy_'] = 5;
        $session->setKey('assist', $assist);

        $activeOver = "btnDefActive";
		$wc = new Overdues($this->searchQuery);
		$toolsPanel = $wc->getToolsPanelVars();

		$wc->start = 0;
		$wc->perPage = $wc->totalModelsCount();

		//если нет поиска, выбираем из базы
		//if ( !trueIsset($this->foundRows) )
			$wc->getModelsFormStock( array_key_exists("showall",$this->getQueryParams()) );

		// начинаем вывод моделей
        $wholePos = 0;
		//if ( empty($session->nothing) )
        //{
			$getterModels = $wc->getWorkingCentersExpired();
			$showModels = $getterModels['models'];
			$wholePos = $getterModels['wholePos'];
		//}

		$compacted = compact(['toolsPanel','activeOver','showModels','wholePos']);
		return $this->render('overdues', $compacted);
	}


}
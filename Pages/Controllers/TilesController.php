<?php
namespace controllers;

use models\{Tiles};
use widgets\Pagination;

class TilesController extends MainController
{

    /**
     * @throws \Exception
     */
    public function action()
    {
        $session = $this->session;
        $assist = $session->getKey('assist');
        $assist['drawBy_'] = 1;
        $session->setKey('assist', $assist);

        $activeTiles = "btnDefActive";
		$tiles = new Tiles($this->searchQuery);
		$toolsPanel = $tiles->getToolsPanelVars();

		/** New Pagination. Starts before main stock query*/
		$totalM = $tiles->totalModelsCount();
		$pagination = new Pagination( $totalM, $assist['maxPos'], $assist['page'] );
        $tiles->start = $pagination->getStart();
        $tiles->perPage = $assist['maxPos'];

		//если нет поиска, выбираем из базы
		//if ( !trueIsset($this->foundRows) )
		$tiles->getModelsFormStock( array_key_exists("showall",$this->getQueryParams()) );

		// начинаем вывод моделей
		$wholePos = 0;
        $iter = 0; 
        $statsbottom = '';
		//if ( empty($session->nothing) )
        //{
			$getterModels = $tiles->getModels();
			$showModels = $getterModels['showByTiles'];

			$iter = $getterModels['iter'];
			$wholePos = $totalM;

			$modelsFrom = $tiles->start;
        	$modelsTo = $tiles->start + $iter;

            $statsbottom = $tiles->statusBar('tiles', compact(['modelsFrom','modelsTo','wholePos','iter']));
		//}
        
        //For tiles and kits
		$this->includeJSFile(name: 'Selects.js', options: ['defer','timestamp','path'=>'web/views/main/js/']);

		$compacted = compact(['toolsPanel','activeTiles','showModels','iter','wholePos','statsbottom','pagination']);
		return $this->render('tiles', $compacted);
	}


}
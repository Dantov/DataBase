<?php
namespace Views\_Main\Controllers;

use Views\_Main\Models\{Main, SetSortModel, Search, ToExcel};
use Views\_Globals\Controllers\GeneralController;
use Views\_Globals\Models\{PushNotice, SelectionsModel};

use Views\vendor\libs\classes\AppCodes;
use Views\vendor\core\HtmlHelper;

class MainController extends GeneralController
{
	
    public string $title = 'ХЮФ 3Д База';
    public array $foundRows = [];

    public function __construct()
    {
        parent::__construct();
    }


    /**
     * @throws \Exception
     */
    public function beforeAction()
    {
        $params = $this->getQueryParams();
        $request = $this->request;
        $session = $this->session;

        if ( $request->isAjax() )
        {
            // ******* Status history ******** //
            if ( $request->post('statHistoryON') ) $this->selectByStatHistory();
            if ( $request->post('changeDates') ) $this->changeDatesByStatHistory();

            if ( $request->post('changeStatusDate') ) $this->changeStatusDate();

            // ******* Selection mode ******** //
            if ( $request->post('selections') )
            {
                $selections = new SelectionsModel($session);
                if ( $selToggle = (int)$request->post('toggle') )
                    $selections->selectionModeToggle($selToggle);

                if ( $checkBox = (int)$request->post('checkBox') )
                    $selections->checkBoxToggle($checkBox);

                if ( $request->post('checkSelectedModels') )
                    $selections->checkSelectedModels();
            }

            // ******* Exports PDF/Excel ******** //
            if ( (int)$request->post('collectionPDF') === 1 ) $this->collectionPDF();

            if ( (int)$request->get('excel') === 1 )
            {
                try {
                    $excel = new ToExcel();
                    if ( (int)$request->get('getXlsx') === 1 )
                    {
                        $excel->setProgress($_GET['userName'], $_GET['tabID']);
                        $excel->getXlsx();
                    }
                    if ( (int)$request->get('getXlsxFwc') === 1 )
                    {
                        $excel->setProgress($_GET['userName'], $_GET['tabID']);
                        $excel->getXlsxFwc();
                    }
                    if ( (int)$request->get('getXlsxExpired') === 1 )
                    {
                        $excel->setProgress($_GET['userName'], $_GET['tabID']);
                        $excel->getXlsxExpired();
                    }
                    // Возврат имени файлв для Excel
                    if ( (int)$request->get('getFileName') === 1 )
                    {
                        $assist = $session->getKey('assist');
                        $searchFor = $session->getKey('searchFor');
                        if ( trueIsset($excel->foundRows) )
                        {
                            $collectionName = $searchFor;
                        } else {
                            //$collectionName = (int)$assist['searchIn'] === 1 ? $searchFor : $assist['collectionName'].'_-_'.$searchFor;
                            $collectionName = $assist['collectionName'];
                        }
                        $date = date('d.m.Y');
                        $res['fileName'] = $excel->translit($collectionName) . '_'. $date;
                        exit( json_encode($res) );
                    }

                } catch (\Exception | \Error $e) {
                    if ( _DEV_MODE_ )
                    {
                        $resp = ['error'=>$e->getMessage(), $e->getCode()];
                    } else {
                        $resp = ['error'=>AppCodes::getMessage(AppCodes::EXCEL_EXPORT_ERROR)['message'], AppCodes::EXCEL_EXPORT_ERROR];
                    }
                    exit( json_encode($resp) );
                }
            }
            exit;
        }   // ---- Exit AJAX ---- //

        // ******* SEARCH ******* //
        if ( $session->hasKey('searchFor') || $session->getKey('re_search') )
        {
            $search = new Search($params);
            $this->foundRows = $search->search( $session->getKey('searchFor') );
        }

        // ******* SORT ******* //
        if ( !empty($params) )
        {
            $setSort = new SetSortModel();
            // вернет адрес для редиректа, или false если редирект не нужен
            if ( $url = $setSort->setSort($params) )
            {
                $this->redirect($url);
            }
        }

        // ******* SELECTED MODELS ******* //
        if ( $this->isQueryParam('selected-models-show') || isset($session->getKey('selectionMode')['showModels']) )
        {
            //$selections = new SelectionsModel($session);
            $this->foundRows = (new SelectionsModel($session, $this))->getSelectedModels();
        }


    }

    /**
     * @throws \Exception
     */
    public function action()
    {
        $session = $this->session;
        $session->setKey('id_notice',0);
        $assist = $session->getKey('assist');

		//$_SESSION['id_notice'] = 0;
		$main = new Main( $assist, $session->getKey('user'), $this->foundRows );

		//if (!_DEV_MODE_) $main->backup(10);

		$variables   = $main->getVeriables();
        //debug($variables,'',1);
		$chevron_    = $variables['chevron_'];
		$chevTitle   = $variables['chevTitle'];
		$showsort    = $variables['showsort'];
		$activeSquer = $variables['activeSquer'];
		$activeWorkingCenters = $variables['activeWorkingCenters']??'';
		$activeWorkingCenters2 = $variables['activeWorkingCenters2']??'';
		$activeList  = $variables['activeList'];
		$collectionName = $variables['collectionName'];
		
		$modelTypes = $main->getServiceData(['model_type'])['model_type'];
		$modelMaterials = $main->getModelMaterialsSelect();
		$modelGemTypes = $main->getServiceData(['gems_names'])['gems_names'];

		//debug($modelMaterials,'$modelMaterials',1);

		// выборка статусов
		$status = $main->getStatusesSelect();
		$selectedStatusName = $assist['regStat'];

		$toggleSelectedGroup = 'hidden';
                $selectedModelsByLi = '';
		if ( $variables['activeSelect'] == 'btnDefActive' ) 
		{
			$toggleSelectedGroup = '';
			$selectedModelsByLi = Main::selectedModelsByLi();
		}
		
		/** почистим старые уведомления */
		(new PushNotice())->clearOldNotices();

		//если нет поиска, выбираем из базы
		if ( !trueIsset($this->foundRows) )
			$main->getModelsFormStock( array_key_exists("showall",$this->getQueryParams()) );
		    

		// начинаем вывод моделей
        $iter = null;$statsbottom =null;$wholePos = null;$pagination=null;
		if ( !isset($_SESSION['nothing']) ) 
        {
			$showModels = '';
			$drawBy_ = (int)$_SESSION['assist']['drawBy_']?:false;

            // Когда ?page=num больше кол-ва найденнных моделей. Вываливает ошибку mysql, из-зп того что $posIds пустой
            if ( count($main->row) < ($_SESSION['assist']['page'] * $_SESSION['assist']['maxPos']) )$this->redirect('/main/?page=0');
            
            $statusBar = function( string $type, array $vars ) : string
            {
                extract($vars);
                static $html = new HtmlHelper();

                $f1 = $html->tag('i')->setTagText('Сортировка по: ')->create() . $showsort;
                $f2 = $html->tag('i')->setTagText('Найдено (Изделий): ')->create() . $wholePos;
                $f3 = $html->tag('i')->setTagText('Изделий &mdash; ')->create() . ($posIter??'');
                $f4 = $html->tag('i')->setTagText('Показано: Комплектов &mdash; ')->create() . ($ComplShown??'');
                $f5 = $html->tag('i')->setTagText('Показано: ')->create() . $iter;
                
                switch ($type)
                {
                    case 'tiles':
                        return  $f1 . " || ". $f2 . " || " . $f5;
                    break;
                    case 'complects':
                        return  $f1 . " || ". $f2 . " || " . $f4 . $f5;
                    break;
                }
            };
			// ============== Плиткой ============== //
			if ( $drawBy_ === 1 )
			{
				$getterModels = $main->getModelsByTiles();
				$showModels = $getterModels['showByTiles'];
				$iter = $getterModels['iter'];
				$wholePos = $getterModels['wholePos'];
                $statsbottom = $statusBar('tiles', compact(['showsort','wholePos','iter']));
				//$statsbottom = "<i>Сортировка по: </i>".$showsort." || "."<i>Найдено (Изделий):</i> ".$wholePos." || "."<i>Показано:</i> ".$iter;
			}


			// ============== Комплектами ============== //
            $posIter = '';
            $ComplShown = '';
			if ( $drawBy_ === 2 ) {

				$getterModels = $main->getModelsByRows();
				$showModels = $getterModels['showByRows'];

				$posIter = $getterModels['posIter'];
				$wholePos = $getterModels['wholePos'];
				$ComplShown = $getterModels['ComplShown'];
				$iter = $getterModels['iter'];
                                $statsbottom = $statusBar('tiles', compact(['showsort','wholePos','iter','posIter','ComplShown']));
                                /*
                                $statsbottom = $html->tag('i')->setTagText('Сортировка по: ')->create() . $showsort." || ".
                                            $html->tag('i')->setTagText('Найдено (Комплектов): ')->create() . $wholePos .
                                            $html->tag('i')->setTagText('(Изделий): ')->create() . $posIter." || " .
                                            $html->tag('i')->setTagText('Показано: Комплектов &mdash; ')->create() . $ComplShown .
                                            $html->tag('i')->setTagText('Изделий &mdash; ')->create() . $iter;
                                */
				//$statsbottom = "<i>Сортировка по: </i>".$showsort." || "."<i>Найдено (Комплектов):</i> ".$wholePos." <i>(Изделий):</i> ".$posIter." || "."<i>Показано: Комплектов &mdash; </i>".$ComplShown.". <i>Изделий &mdash; </i>".$iter;
			}


            // ============== Рабочие Центры ============== //
                        $workingCenters = '';
                        $workCentersSort = false;
			if ( $drawBy_ === 3 || $drawBy_ === 4 ) {
				// менюшка для выборки по рабочим центрам
				if ( $drawBy_ === 4 ) {
					$workCentersSort = true;
					$workingCenters = $main->workingCentersDB;
				}

				$getterModels = $main->getModelsByWorkingCenters();
				$showModels = $getterModels['showByWorkingCenters'];
				$iter = $getterModels['iter'];
				$wholePos = $getterModels['wholePos'];

				$statsbottom = "<i>Сортировка по: </i>".$showsort." || "."<i>Найдено (Изделий):</i> ".$wholePos." || "."<i>Показано:</i> ".$iter;
			}
            if ( $drawBy_ === 3 ) $this->varBlock['container'] = 2; //уберем класс container в шаблоне чтоб стало шире



            // ============== Табличка участов с просроченными ============== //
			if ( $drawBy_ === 5 ) 
            {
				$getterModels = $main->getWorkingCentersExpired();
				$showModels = $getterModels['showByWorkingCenters'];
				$wholePos = $getterModels['wholePos'];
			}

		} else {
			 //если ничего не найдено
			$wholePos = 0;
		}


		// --- Пагинация --- //
		if ( $drawBy_ !== 5 ) {
			$pagination = '';
			// начинаем рисовать пагинацию если кол-во отображаемых моделей больше кол-ва разрешенных к показу
			if ($wholePos > $_SESSION['assist']['maxPos'])
				$pagination = $main->drawPagination();
		}

		$this->includePHPFile('modalStatuses.php',compact(['status','selectedStatusName']));
		$this->includePHPFile('progressModal.php','','',_globDIR_. 'includes/');
		$this->includeJSFile('Selects.js',['defer','timestamp']);
		
		$compacted = compact(['variables','modelGemTypes','modelTypes','modelMaterials','chevron_','chevTitle','showsort',
            'activeSquer','activeWorkingCenters','activeWorkingCenters2','activeList','collectionName','status',
            'selectedStatusName','toggleSelectedGroup','selectedModelsByLi','showModels','drawBy_','iter','wholePos','statsbottom',
    		'posIter','ComplShown','workingCenters','workCentersSort','pagination']);

        $this->varBlock['activeMenu'] = 'active';
		return $this->render('main', $compacted);
	}


	protected function selectByStatHistory()
    {
        $assist = $this->session->getKey('assist');
        $request = $this->request;

        $checked = (int)$request->post('byStatHistory');
        if ( $checked )
        {
            $assist['byStatHistory'] = 1;
            echo json_encode(['ok'=>1]);
        } else {
            $assist['byStatHistory'] = 0;

            $assist['byStatHistoryFrom'] = '';
            $assist['byStatHistoryTo'] = '';
            echo json_encode(['ok'=>0]);
        }
        $this->session->setKey('assist', $assist);
        exit;
    }

    protected function changeDatesByStatHistory()
    {
        $assist = $this->session->getKey('assist');
        $request = $this->request;
        if ( $from = $request->post('byStatHistoryFrom') )
        {
            $assist['byStatHistoryFrom'] = $from==='0000-00-00'?'':$from;
            echo json_encode(['ok'=>$from]);
        }
        if ( $to = $request->post('byStatHistoryTo') )
        {
            $assist['byStatHistoryTo'] = $to==='0000-00-00'?'':$to;
            echo json_encode(['ok'=>$to]);
        }
        $this->session->setKey('assist', $assist);
        exit;
    }

    /**
     * @throws \Exception
     */
    protected function changeStatusDate()
    {
        $request = $this->request;
        $id = (int)$request->post('id');
        $newDate = trim( htmlentities($request->post('newDate'), ENT_QUOTES) );

        if ( !$id ) return;
        if ( !validateDate( $newDate, 'Y-m-d' ) ) return;

        $aq = new ActiveQuery('statuses');

        //$result = $st->baseSql(" UPDATE statuses SET date='$newDate' WHERE id='$id' ");
        $aq->update(table: "statuses", row: ['date'=>$newDate], where: ['id'=>$id]);
        //if ( $result )
        if ( $aq->affectedRows )
        {
            echo json_encode(['ok'=>1]);
        } else {
            echo json_encode(['ok'=>0]);
        }
        exit;
    }
    
    protected function collectionPDF()
    {
        require _viewsDIR_ . "_Main/Controllers/collectionExportController.php";
        exit;
    }
    
}
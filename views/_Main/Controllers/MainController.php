<?php
namespace Views\_Main\Controllers;

use Views\_Globals\Models\PushNotice;
use Views\_Globals\Models\User;
use Views\_Main\Models\{Main, SetSortModel, Search, ToExcel};
use Views\_Globals\Controllers\GeneralController;
use Views\_Globals\Models\SelectionsModel;
//use Views\vendor\core\Registry;
use Views\vendor\libs\classes\AppCodes;


class MainController extends GeneralController
{
	
    public $title = 'ХЮФ 3Д База';
    public $foundRows = [];



    public function __construct($controllerName)
    {
        parent::__construct($controllerName);
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
        if ( $this->isQueryParam('selected-models-show') || $session->getKey('selectionMode')['showModels'] )
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
		$_SESSION['id_notice'] = 0;
		$main = new Main( $session->getKey('assist'), $session->getKey('user'), $this->foundRows ); //$session->getKey('foundRow')

		if (!_DEV_MODE_) $main->backup(10);

		$variables   = $main->getVeriables();
		$chevron_    = $variables['chevron_'];
		$chevTitle   = $variables['chevTitle'];
		$showsort    = $variables['showsort'];
		$activeSquer = $variables['activeSquer'];
		$activeWorkingCenters = $variables['activeWorkingCenters'];
		$activeWorkingCenters2 = $variables['activeWorkingCenters2'];
		$activeList  = $variables['activeList'];
		$collectionName = $variables['collectionName'];
		
		$modelTypes = $main->getServiceData(['model_type'])['tables']['model_type'];
		$modelMaterials = $main->getModelMaterialsSelect();
		$modelGemTypes = $main->getServiceData(['gems_names'])['tables']['gems_names'];

		//debug($modelMaterials,'$modelMaterials',1);

		// выборка статусов
		$status = $main->getStatusesSelect();
		$selectedStatusName = $_SESSION['assist']['regStat'];

		$toggleSelectedGroup = 'hidden';
		if ( $variables['activeSelect'] == 'btnDefActive' ) 
		{
			$toggleSelectedGroup = '';
			$selectedModelsByLi = Main::selectedModelsByLi();
		}
		
		/** почистим старые уведомления */
		(new PushNotice())->clearOldNotices();
		/*
		if (User::getAccess() === 9 )
		{
			$pn = new PushNotice();
			$pn->clearOldNotices();
		}
		*/

		//если нет поиска, выбираем из базы
		if ( !trueIsset($this->foundRows) )
			$main->getModelsFormStock( array_key_exists("showall",$this->getQueryParams()) );
		    

		// начинаем вывод моделей
		if ( !isset($_SESSION['nothing']) ) {
			$showModels = '';
			$drawBy_ = (int)$_SESSION['assist']['drawBy_']?:false;

            // Когда ?page=num больше кол-ва найденнных моделей. Вываливает ошибку mysql, из-зп того что $posIds пустой
            if ( count($main->row) < ($_SESSION['assist']['page'] * $_SESSION['assist']['maxPos']) )$this->redirect('/main/?page=0');


			// ============== Плиткой ============== //
			if ( $drawBy_ === 1 )
			{
				$getterModels = $main->getModelsByTiles();
				$showModels = $getterModels['showByTiles'];
				$iter = $getterModels['iter'];
				$wholePos = $getterModels['wholePos'];

				$statsbottom = "<i>Сортировка по: </i>".$showsort." || "."<i>Найдено (Изделий):</i> ".$wholePos." || "."<i>Показано:</i> ".$iter;
			}


			// ============== Комплектами ============== //
			if ( $drawBy_ === 2 ) {

				$getterModels = $main->getModelsByRows();
				$showModels = $getterModels['showByRows'];

				$posIter = $getterModels['posIter'];
				$wholePos = $getterModels['wholePos'];
				$ComplShown = $getterModels['ComplShown'];
				$iter = $getterModels['iter'];

				$statsbottom = "<i>Сортировка по: </i>".$showsort." || "."<i>Найдено (Комплектов):</i> ".$wholePos." <i>(Изделий):</i> ".$posIter." || "."<i>Показано: Комплектов &mdash; </i>".$ComplShown.". <i>Изделий &mdash; </i>".$iter;
			}


            // ============== Рабочие Центры ============== //
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
			if ( $drawBy_ === 5 ) {
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
		
		$compacted = compact(['variables','modelGemTypes','modelTypes','modelMaterials','chevron_','chevTitle','showsort','activeSquer','activeWorkingCenters',
		'activeWorkingCenters2','activeList','collectionName','collectionList','status','selectedStatusName',
		'toggleSelectedGroup','selectedModelsByLi','showModels','drawBy_','iter','wholePos','statsbottom',
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

        $general = new \Views\_Globals\Models\General();
        $general->connectDBLite();

        $result = $general->baseSql(" UPDATE statuses SET date='$newDate' WHERE id='$id' ");
        if ( $result )
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
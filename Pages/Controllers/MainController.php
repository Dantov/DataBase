<?php
namespace controllers;

use models\{
    Main, SetSortModel, Search, PDFExports,
    ToExcel, PushNotice, SelectionsModel
};

use libs\classes\AppCodes;
use soffit\{Router};

class MainController extends GeneralController
{
	
    public string $title = 'ХЮФ 3Д База';
    public string $searchQuery = '';

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
            if ( (int)$request->post('collectionPDF') === 1 ) 
                $this->collectionPDF();

            if ( (int)$request->get('excel') === 1 )
            {
                try {
                    
                    if ( (int)$request->get('getXlsx') === 1 )
                    {
                        $excel = new ToExcel('getXlsx');
                        $excel->setProgress($_GET['userName'], $_GET['tabID']);
                        $excel->getXlsx();
                    }
                    if ( (int)$request->get('getXlsxFwc') === 1 )
                    {
                        $excel = new ToExcel('getXlsxFwc');
                        $excel->setProgress($_GET['userName'], $_GET['tabID']);
                        $excel->getXlsxFwc();
                    }
                    if ( (int)$request->get('getXlsxExpired') === 1 )
                    {
                        $excel = new ToExcel('getXlsxExpired');
                        $excel->setProgress($_GET['userName'], $_GET['tabID']);
                        $excel->getXlsxExpired();
                    }
                    // Возврат имени файлв для Excel
                    if ( (int)$request->get('getFileName') === 1 )
                    {
                        $excel = new ToExcel('getFileName');
                        $res['fileName'] = $excel->filename;
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
        }   
        // ---- Exit AJAX ---- //


        /** Will exit this method here, becouse code below need only for usefull pages */
        if ( Router::getControllerName() === 'Main' ) return;

        // ******* SORT ******* //
        if (!empty($params))
        {
            $setSort=new SetSortModel();

            // вернет адрес для редиректа, или false если редирект не нужен
            if ($url=$setSort->setSort($params))
                $this->redirect($url);
        }

        // ******* SEARCH ******* //
        if ( $session->hasKey('searchFor') || $session->getKey('re_search') )
        {
            $search = new Search($params);
            $this->searchQuery = $search->search( $session->getKey('searchFor') );
        }

        // ******* SELECTED MODELS ******* //
        if ( $this->isQueryParam('selected-models-show') || isset($session->getKey('selectionMode')['showModels']) )
        {
            $this->searchQuery = (new SelectionsModel($session, $this))->getSelectedModels();
            $assist = $session->assist;
            $assist['collectionName'] = "Выбрано_".date("d.m.Y");
            $session->setKey('assist',$assist);
        }

        /** почистим старые уведомления **/
        (new PushNotice())->clearOldNotices();

        /** INCLUDES BLOCK FOR ALL */
        $this->varBlock['activeMenu'] = 'active';
        $this->includePHPFile(name: 'progressModal.php', path: _globDIR_);
        $wcSortedFull = (new Main())->getStatusesToolsPanel();
        $this->includePHPFile(name: 'modalStatuses.php', vars: compact(['wcSortedFull']), path: _WEB_VIEWS_.'main/');

        //if (!_DEV_MODE_) $main->backup(10);

    }

    /**
     * Now it only redirects to usefull pages
     * @throws \Exception
     */
    public function action()
    {
        $params = $this->getQueryParams();
        switch ( $this->session->assist['drawBy_'] )
        {
            case 1:
                $this->redirect('tiles/',$params);
                break;
            case 2:
                $this->redirect('kits/',$params);
                break;
            case 3:
                $this->redirect('working-centers/',$params);
                break;
            case 4:
                $this->redirect('location-centers/',$params);
                break;
            case 5:
                $this->redirect('overdues/',$params);
                break;
            default:
                $this->redirect('tiles/',$params);
                break;
        }
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

        $aq = new ActiveQuery();
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
        $session = $this->session;
        $assist = $session->getKey('assist');

        // ******* SEARCH ******* //
        if ($searchFor = $session->getKey('searchFor'))
            $this->searchQuery = (new Search())->search($searchFor);

        // ******* SELECTED MODELS ******* //
        if (!empty($session->getKey('selectionMode')['models']))
            $this->searchQuery = (new SelectionsModel())->getSelectedModels();

        //debug($this->searchQuery,'fff',1);

        $pdf = new PDFExports($this->searchQuery);
        $pdf->collectionExport();
        $pdf->output();
    }
    
}
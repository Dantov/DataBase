<?php
namespace Views\_UserPouch\Controllers;


use Views\_Globals\Models\User;
use Views\_UserPouch\Models\UserPouch;
use Views\_Globals\Controllers\GeneralController;
use Views\vendor\core\Cookies;
use Views\_Globals\Widgets\Pagination;

class UserPouchController extends GeneralController
{

    public string $title = 'ХЮФ::Кошелёк Работника';

    /**
     * $start - от пагинации, позиция с которой начать выборку 
     */
    public $start;
    /**
     * $perPage - кол-во выбираемых позиций для отобр. на одной странице
     */
    public $page;

    public $ppCount;

    public $workerID;
    public $tab;
    public $month;
    public $year;

    public $searchForPM = '';

    /**
     * @throws \Exception
     */
    public function beforeAction() : void
    {
        $request = $this->request;

        if ( !User::permission('paymentManager') )
            if ( !User::permission('userPouch') )
                $this->redirect('main/');

        $this->ppCount = (int)$request->get('ppCount') ?: 30;
        $this->page = (int)$request->get('page');
        $this->workerID = $this->session->getKey('user')['id'];
        $this->tab = $this->getView( (int)$request->get('tab') );
        $this->month = (int)$request->get('month');
        $this->year = (int)$request->get('year');

        $this->searchForPM = $request->get('searchForPM');
    }

    /**
     * @param $tab
     * @return string
     */
    protected function getView( int $tab ) : string
    {
        switch ($tab)
        {
            case 1: return 'all'; break;
            case 2: return 'paid'; break;
            case 3: return 'notpaid'; break;
            case 4: return 'notCredited'; break;
            default : return 'all'; break;
        }
    }

    /**
     * @throws \Exception
     */
    public function action()
    {
        $userPouch = new UserPouch($this->tab, $this->workerID, $this->month, $this->year, $this->searchForPM??'');

        ///*** Паганация *** ///
        $totalM = $userPouch->totalModelsHasPrices();
        $totalMP = $userPouch->totalPrices();

        //$perpage = 30;
        $pagination = new Pagination( $totalM, $this->ppCount, $this->page );
        $userPouch->start = $pagination->getStart();

        //$userPouch->perPage = $perpage;
        $userPouch->perPage = $this->ppCount;

        $stockInfo = $userPouch->getStockInfo();
        $modelPrices = $userPouch->getModelPrices();
        $statistic = $userPouch->getStatistic();

        $ppCount = $this->ppCount;
        $tab = $this->tab;
        $workerID = $this->workerID;
        $monthID = $this->month;
        $yearID = $this->year;
        $page = $this->page;
        $searchForValue = $this->searchForPM;
        $title = 'Кошелёк работника';

        $this->includeJSFile('UserPouch.js',['defer','timestamp']);

        $compacts = compact([
            'ppCount','modelPrices','stockInfo','tab','statistic','workerID','monthID','yearID','page','pagination',
            'totalM','totalMP','searchForValue','title',
        ]);
        return $this->render('userpouch', $compacts);
    }


}
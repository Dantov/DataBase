<?php

namespace Views\_PaymentManager\Controllers;

use Views\_SaveModel\Models\HandlerPrices;
use Views\_Globals\Models\User;
use Views\_UserPouch\Controllers\UserPouchController;
use Views\_PaymentManager\Models\PaymentManager;
use Views\vendor\core\Registry;
use Views\vendor\libs\classes\{AppCodes,URLCrypt};
use Views\_Globals\Widgets\Pagination;

class PaymentManagerController extends UserPouchController
{

    public $title = 'ХЮФ::Менеджмент Оплат';


    /**
     * @throws \Exception
     */
    public function beforeAction() : void
    {
        $request = $this->request;

        /** проверка права юзера запускать этот манагер! */
        if ( !User::permission('paymentManager') )
            $this->redirect('main/');

        if ( $request->isAjax() )
        {
            try {
                if ( $this->isQueryParam('getPricesAll') && $request->post('priceIDs') && $request->post('posID') )
                    $this->getPricesByID( $request->post('priceIDs'), $request->post('posID') );

                if ( $this->isQueryParam('payPrice') && $request->post('prices') )
                    $this->payPrices( $request->post('prices'), $request->post('excludePricesList')??[] );

            } catch (\Error | \Exception  $e) {
                $this->serverError_ajax($e);
            }
            exit;
        }

        parent::beforeAction();
        $this->workerID = (int)$request->get('worker');
    }

    /**
     * @throws \Exception
     */
    public function action()
    {
        $pm = new PaymentManager($this->tab, $this->workerID, $this->month, $this->year, $this->searchForPM);

        ///*** Паганация *** ///
        $totalM = $pm->totalModelsHasPrices();
        $totalMP = $pm->totalPrices();
        $pagination = new Pagination( $totalM, $this->ppCount, $this->page );
        $pm->start = $pagination->getStart();
        $pm->perPage = $this->ppCount;

        $stockInfo = $pm->getStockInfo();
        $modelPrices = $pm->getModelPrices();
        $statistic = $pm->getStatistic();

        $usersList = $pm->getActiveUsers();
        $this->setWorkerByIDinList( $this->workerID, $usersList );

        $ppCount = $this->ppCount;
        $tab = $this->tab;
        $workerID = $this->workerID;
        $monthID = $this->month;
        $yearID = $this->year;
        $page = $this->page;
        $searchForValue = $this->searchForPM;
        $title = 'Менеджер Оплат';

        $pmView = true;
        $this->includeJSFile('paymentM.js',['defer','timestamp']);
        $this->includePHPFile('paymentModal.php');

        $compacts = compact([
            'ppCount','modelPrices','stockInfo','tab','statistic','usersList','workerID','monthID','yearID','pagination','totalM',
            'totalMP','page','searchForValue','pmView','title',
        ]);
        return $this->render('paymentM', $compacts);
    }

    /**
     * ставит сессию работника
     * @param $workerID
     * @param $usersList
     */
    public function setWorkerByIDinList( $workerID, $usersList ) : void
    {
        $session = $this->session;

        if ( $workerID === 0 ) 
        {
            $session->currentWorker = [
                'id' => 0,
                'fio' => 'Все работники',
            ];
            return;
        }

        foreach ($usersList as $user) 
        {
            if ( $user['id'] == $workerID ) 
            {
                $session->currentWorker = [
                    'id' => $user['id'],
                    'fio' => $user['fio'],
                ];
                return;
            }
        }
    }


    /**
     * @param string $pricesID
     * @param string $posID
     * @throws \Exception
     */
    protected function getPricesByID(string $pricesID, string $posID )
    {
        $codes = Registry::init()->appCodes;
        if ( !User::permission('paymentManager') )
            exit( json_encode( ['error'=>$codes->getCodeMessage($codes::NO_PERMISSION)] ) );

        $pricesID = explode(';', URLCrypt::strDecode( $pricesID ));
        $posID = explode(';', URLCrypt::strDecode( $posID ));

        $pm = new PaymentManager();
        $pricesArr = $pm->getPricesByID( $pricesID, $posID );

        exit( json_encode( $pricesArr ) );
    }

    /**
     * @param array $priceIDs
     * @param array $excludePricesList
     * @throws \Exception
     */
    protected function payPrices( array $priceIDs, array $excludePricesList )
    {
        if ( !User::permission('paymentManager') )
            exit( json_encode( ['error'=>AppCodes::getMessage(AppCodes::NO_PERMISSION_TO_PAY)] ) );
        //debug($priceIDs,'$priceIDs',1);

        $h = new HandlerPrices();
        $h->connectToDB();
        foreach ( $priceIDs as $key => &$pID )
        {
            if ( in_array($pID,$excludePricesList) )
            {
                if ( isset($priceIDs[$key]) ) unset($priceIDs[$key]);
                continue;
            }

            $pID = (int)URLCrypt::strDecode( $pID );
            if ( !$h->checkID($pID, 'model_prices', 'id') )
                exit( json_encode(['error'=>AppCodes::getMessage(AppCodes::WRONG_PRICE)]) );
        }

        if ( empty( $priceIDs ) )
            exit( json_encode(['error'=>AppCodes::getMessage(AppCodes::EMPTY_PRICES)]) );

        exit( json_encode($h->payPrices( $priceIDs )) );
    }

}
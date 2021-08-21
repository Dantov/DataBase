<?php
namespace Views\_PaymentManager\Models;
use Views\_UserPouch\Models\UserPouch;
use Views\vendor\core\Registry;
use Views\vendor\libs\classes\URLCrypt;

class PaymentManager extends UserPouch
{

	public function __construct( string $paidTab='', int $worker = 0, int $month = 0, int $year = 0, string $searchInput='' )
	{
		parent::__construct( $paidTab, $worker, $month, $year, $searchInput );
	}

    /**
     * @return array
     * @throws \Exception
     */
    public function getActiveUsers()
    {
        $allUsers = $this->getUsers();

        // ID раб. участков из которых нужны юзеры
        $areas = [28,1,2,3,4];
        $users = [];
        foreach ($allUsers as &$user)
        {
            $user['location'] = explode(',', $user['location']);
            foreach ($user['location'] as $location) 
            {
                if ( in_array($location, $areas) ) 
                {
                    $users[] = $user;
                    continue 2;
                }
            }
        }
        return $users;
    }

    /**
     * Для AJAX
     * @param array $pricesIDs
     * @param array $modelsID
     * @return array
     * @throws \Exception
     */
    public function getPricesByID(array $pricesIDs, array $modelsID ) : array
    {
        $inPrices = "";
        foreach ( $pricesIDs as $pID )
            if ( !empty($pID) ) $inPrices .= (int)$pID . ',';
        if ( !empty($inPrices) ) $inPrices = "(" . rtrim($inPrices,',') . ")";

        $inModels = "";
        foreach ( $modelsID as $mID )
            if ( !empty($mID) ) $inModels .= (int)$mID . ',';
        if ( !empty($inModels) ) $inModels = "(" . rtrim($inModels,',') . ")";

        $stockSql = "SELECT i.img_name,i.pos_id,i.main,i.sketch,   st.id,st.number_3d,st.vendor_code as vendorCode, st.model_type as modelType
                    FROM stock as st
                      LEFT JOIN images as i ON i.pos_id = st.id
                          WHERE st.id IN $inModels";

        $pricesSql = "SELECT mp.id as pID, mp.pos_id as posID, mp.user_id as uID, mp.gs_id as gsID, mp.is3d_grade as is3dGrade, mp.cost_name as costName, 
                             mp.value as value, mp.status as status, mp.paid as paid, mp.pos_id as posID, mp.date as date, u.fio, 
                             gs.description as gsDescr
                        FROM model_prices as mp
                          LEFT JOIN users as u ON mp.user_id = u.id
                          LEFT JOIN grading_system as gs ON gs.id = mp.gs_id
                              WHERE mp.id IN $inPrices AND (mp.status='1' AND mp.paid=0 AND mp.pos_id IN $inModels)";
        //AND i.main='1'
        $prices = [];
        $stock = [];
        try {
            //$stock = $this->findAsArray($stockSql);
            //debugAjax($stock, '$stock');
            $stock = $this->sortComplectedData( $this->findAsArray($stockSql), ['id','number_3d','modelType','vendorCode'] );
            //debugAjax($stock, '$stock2', END_AB);

            $prices = $this->findAsArray($pricesSql);
        } catch (\Exception $e) {
            $codes = Registry::init()->appCodes;
            return ['error'=>$codes->getCodeMessage($codes::SERVER_ERROR)];
        } finally {
            $sStock = [];
            foreach ( $stock as &$model )
            {
                $model['imgName'] = $model['img_name'];

                foreach ( $prices as &$price )
                    if ( $price['posID'] == $model['id'] )
                    {
                        $price['date'] = date_create( $price['date'] )->Format('d.m.Y');
                        $price['pID'] = URLCrypt::strEncode($price['pID']);
                        $model['prices'][] = $price;
                    }
                $sStock[] = $model;
            }

            return $sStock;
        }
    }

}
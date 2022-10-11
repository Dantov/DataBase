<?php
namespace models;

use libs\classes\{AppCodes,URLCrypt};

class PaymentManager extends UserPouch
{

    /**
     * @var array - Список разрешенных пользователей(участков) для загрузки прайсов
     */
    protected $permittedAccess = [];

	public function __construct( string $paidTab='', int $worker = 0, int $month = 0, int $year = 0, string $searchInput='' )
	{
		parent::__construct( $paidTab, $worker, $month, $year, $searchInput );

		$this->permittedAreas();

        if ( !$worker )
            $this->setWorkerIds();
	}

    /**
     * Определим массив с доступими юзеров,
     * на основагии разрешений текущего пользователя
     * @throws \Exception
     */
	protected function permittedAreas()
    {
        $uPerm = User::permissions();
        $permissionAreasAccess = [
            // разрешение    => user access

            "PM_modeller3D"  => 2,
            "PM_3dPrinting"  => 3,
            "PM_modellerJew" => 5,
            "PM_techJew"     => 7,
            "PM_techCoord"   => 9,
            "PM_design"      => 11,
        ];

        if ( User::permission('PM_all') )
        {
            foreach ( $permissionAreasAccess as $location )
                    $this->permittedAccess[] = $location;
        } else {

            foreach ( $uPerm as $ruleName => $perm )
            {
                if ( array_key_exists($ruleName,$permissionAreasAccess) )
                    if ( (bool)$perm === true )
                        $this->permittedAccess[] = $permissionAreasAccess[$ruleName];
            }
        }

    }

    /**
     * Определим массив пользователей для зашрузки прайсов
     * на основагии массива разрешений
     * @return array
     * @throws \Exception
     */
    public function getActiveUsers()
    {
        $allUsers = $this->getUsers();


        $users = [];
        foreach ($allUsers as &$user)
        {
            if ( in_array($user['access'],$this->permittedAccess) )
                $users[] = $user;
        }

        return $users;
    }

    /**
     * заполнит $this->worker. Подставит в выборку ID всех доступных пользователей.
     * @throws \Exception
     */
    public function setWorkerIds()
    {
        $activeUsers = $this->getActiveUsers();

        $w = '';
        foreach ( $activeUsers as $aUser )
        {
            if ( !trueIsset($aUser['id']) )
                continue;

            $w .= $aUser['id'] . ',';
        }

        if ( !empty($w) )
        {
            $w = rtrim($w,',');
            $this->worker = "user_id IN (". $w .")";
        }
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
            //$codes = AppCodes::init()->appCodes;
            //$codes->getCodeMessage($codes::SERVER_ERROR)
            return ['error'=> $e->getMessage() . " code: " . $e->getCode() ];
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
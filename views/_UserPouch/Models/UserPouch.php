<?php
namespace Views\_UserPouch\Models;
use Views\_Main\Models\Main;
use Views\vendor\core\Registry;

class UserPouch extends Main
{

    /**
     * $start - от пагинации, позиция с которой начать выборку 
     */
    public $start;
    /**
     * $perPage - кол-во выбираемых позиций для отобр. на одной странице
     */
    public $perPage;

    public $worker;
    public $paidTab;
    public $date;

    public $searchInput = '';

    /**
     * для каких моделей выбрать прайсы
    */
    protected $inModels;

    /**
     * нужня для статистики
     * @var array
     */
    public $stockData;

    public function __construct( string $paidTab='', int $worker = 0, int $month = 0, int $year = 0, string $searchInput='' )
    {
        parent::__construct();

        switch ( $paidTab )
        {
            case "all": $this->paidTab = ""; break;
            case "paid": $this->paidTab = "AND paid='1'"; break;
            case "notpaid": $this->paidTab = "AND paid='0' AND status='1'"; break;
            case "notCredited": $this->paidTab = "AND paid='0' AND status='0' "; break;
            default : $this->paidTab = ""; break;
        }

        $searchInput = htmlspecialchars( strip_tags( trim($searchInput) ), ENT_QUOTES );
        $searchInput = mysqli_real_escape_string($this->connection, $searchInput);
        if ( !empty($searchInput) )
        {
            $searchedFields = [
                'number_3d', 'vendor_code', 'collections', 'author', 'jewelerName',
                'modeller3D', 'model_type', 'labels', 'description',
            ];

            $queryStr = "";
            foreach ( $searchedFields as $sField )
                $queryStr .= "s." . $sField . " LIKE '%" . $searchInput . "%' OR ";

            $this->searchInput = "(" . trim($queryStr,' OR ') . ") AND ";
        }

        $this->worker = !$worker ? 1 : 'user_id=' . $worker; // WHERE 1 - все работники

        $this->addQueryByDate($month, $year);
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function totalModelsHasPrices() : int
    {
        $sql = " SELECT COUNT(1) as c FROM stock as s
                    WHERE $this->searchInput s.id IN 
                    (SELECT DISTINCT pos_id FROM model_prices WHERE $this->worker $this->paidTab $this->date)";

        return $this->findOne($sql)['c'];
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function totalPrices() : int
    {
        $sql = " SELECT COUNT(1) as c FROM model_prices";
        return $this->findOne($sql)['c'];
    }

    protected function addQueryByDate(int $month = 0, int $year = 0) : void
    {
        if ( $year === 0 ) $year = (int)date('Y');

        $month1 = 1; // январь
        $month2 = 12; // декабрь
        if ( $month !== 0 )
        {
            $month1 = $month;
            $month2 = $month; 
        }

        $date = new \DateTime();
        $date1 =$date->setDate($year, $month1, 1)->format('Y-m-d');
        $date2 =$date->setDate($year, $month2, 31)->format('Y-m-d');
        $this->date = "AND (date >= '$date1' AND date <= '$date2') ";
    }


    /**
     * @return array
     * @throws \Exception
     */
    public function getModelPrices() : array
    {
        $sql = "SELECT * FROM model_prices WHERE $this->worker $this->paidTab $this->date AND pos_id IN $this->inModels";
        //debug($sql,'$sql',1);
        $modelPricesQuery = $this->findAsArray($sql);
        //debug($modelPricesQuery,'$modelPricesQuery',1);

        $modelPrices = [];
        $grades3D = [];
        foreach ( $modelPricesQuery as &$mp )
        {
            if ( $mp['is3d_grade'] == 1 )
            {
                $grades3D[$mp['pos_id']][] = $mp;
                continue;
            }
            $modelPrices[$mp['pos_id']][] = $mp;
        }

        // сливаем массивы оценок 3д в один массив
        foreach ( $grades3D as $modelID => $grades )
        {
            $totalValue = 0;
            $ids = '';
            foreach ( $grades as $grade )
            {
                $totalValue += $grade['value'];
                $ids .= $grade['id'].';';
            }
            $grades[0]['cost_name'] = '3D Моделирование';
            $grades[0]['value'] = $totalValue;
            $grades[0]['ids_3d'] = $ids;

            $modelPrices[$modelID][] = $grades[0];
        }

        unset($grades3D);
        return $modelPrices;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getStockInfo() : array
    {
        /*
        in (
          SELECT * FROM (
                        SELECT id
                        FROM posts
                        ORDER BY timestamp desc limit 0, 15)
          as t
        );

          SELECT DISTINCT pos_id FROM model_prices
                                        WHERE $this->worker $this->paidTab $this->date
                                        LIMIT $this->start, $this->perPage)
        */
        $limit = "LIMIT $this->start, $this->perPage";
        $limitMP = $this->searchInput ? "" : $limit ;
        $sqlPosIDDist = "SELECT DISTINCT pos_id FROM model_prices 
                            WHERE $this->worker $this->paidTab $this->date $limitMP";

        $result = $this->findAsArray($sqlPosIDDist);
        //debug($sqlPosIDDist,'$sqlPosIDDist');

        $in= '';
        foreach ( $result as &$posID_MP ) $in .= $posID_MP['pos_id'].',';
        $this->inModels = $in = !trueIsset($in) ? '(0)' : '(' . rtrim($in, ',') . ')';
        //debug($in,'$in');
        $limitSt = $this->searchInput ? $limit : "" ;
        $sqlStock = " SELECT s.id, s.number_3d, img.pos_id, img.img_name, img.main, img.sketch, s.vendor_code, s.model_type, s.status FROM stock as s 
                      LEFT JOIN images as img ON ( s.id = img.pos_id )
                      WHERE $this->searchInput s.id IN $in ORDER BY s.id DESC $limitSt";
                                     /* AND img.main=1
                                        ( SELECT * FROM (
                                            SELECT DISTINCT pos_id FROM model_prices
                                                WHERE $this->worker $this->paidTab $this->date
                                                LIMIT $this->start, $this->perPage ) as temp )
                                    */
        $result = $this->findAsArray($sqlStock);

        return $this->stockData = $this->sortComplectedData($result, ['id','status','number_3d','model_type','vendor_code']);
    }

    /**
     * Статистика берет прайсы из табл model_prices, сортирует оплаченные, не опл. зачисленные
     * Проблема: если модель удалена, по какой-то причине, прайсы остаются. И статистика их просчитывает
     * но сама модель больше не выведется в Оплаченых/не оплаченых/всех. В итоге статистика может врать.
     * @return array
     * @throws \Exception
     */
    public function getStatistic() : array
    {
        $result = [
            'paid' => 0,
            'notpaid' => 0,
            'waiting' => 0,
        ];

        $sql = "SELECT * FROM model_prices WHERE $this->worker $this->paidTab $this->date ";
        $modelPricesQuery = $this->findAsArray( $sql );


        foreach ( $modelPricesQuery as $price )
        {
            if ( (int)$price['paid'] === 1 )
                $result['paid'] += $price['value'];

            // пропустим не зачисленные или не оплаченные прайсы,
            // если модели нет в стоке (была удалена а прайсы остались)
            if ( !in_array_recursive($price['pos_id'], $this->stockData) )
                continue;

            if ( (int)$price['status'] === 0 )
            {
                $result['waiting'] += $price['value'];
            } elseif ( (int)$price['paid'] === 0 ) {
                $result['notpaid'] += $price['value'];
            }
        }

        return $result;
    }

}
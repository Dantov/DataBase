<?php
namespace Views\_UserPouch\Models;
use Views\_Main\Models\Main;
use Views\vendor\core\Registry;

class UserPouch extends Main
{

    public $worker;
    public $paidTab;
    public $date;

    /**
     * нужня для статистики
     * @var array
     */
    public $stockData;

    public function __construct( string $paidTab='', int $worker = 0, int $month = 0, int $year = 0  )
    {
        parent::__construct();

        switch ( $paidTab )
        {
            case "all": $this->paidTab = ""; break;
            case "paid": $this->paidTab = "AND paid='1'"; break;
            case "notpaid": $this->paidTab = "AND paid='0' AND status='1'"; break;
            default : $this->paidTab = ""; break;
        }

        $this->worker = !$worker ? 1 : 'user_id=' . $worker; // WHERE 1 - все работники
        $this->addQueryByDate($month, $year);
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
        $sql = "SELECT * FROM model_prices WHERE $this->worker $this->paidTab $this->date ";
        $modelPricesQuery = $this->findAsArray($sql);

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
        $sqlStock = " SELECT s.id, s.number_3d, img.pos_id, img.img_name, s.vendor_code, s.model_type, s.status FROM stock as s 
                      LEFT JOIN images as img ON (s.id = img.pos_id AND img.main=1)
                      WHERE s.id IN (SELECT DISTINCT pos_id FROM model_prices WHERE $this->worker $this->paidTab $this->date )";

        $result = $this->findAsArray($sqlStock);
        foreach ( $result as &$stockModel )
        {
            $imgPath = $stockModel['number_3d'] . "/" .$stockModel['id'] . "/images/".$stockModel['img_name'];
            $imgSrc  = file_exists(_stockDIR_ . $imgPath);
            $stockModel['img_name']  =  $imgSrc ? _stockDIR_HTTP_ . $imgPath : _stockDIR_HTTP_."default.jpg";
        }
        return $this->stockData = $result;
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
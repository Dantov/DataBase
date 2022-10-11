<?php
namespace models;

use libs\classes\AppCodes;

class Kits extends Main 
{

    protected bool $toPdf = false;
    /**
     * Main constructor.
     * @param array $searchQuery
     * @throws \Exception
     */
    public function __construct( string $searchQuery='' )
    {
        parent::__construct(searchQuery: $searchQuery);

        if ( !isset($this->assist['page']) ) 
            $this->assist['page'] = 0;
    }

    public function totalKits() : int
    {
        $sql = "SELECT COUNT(DISTINCT `number_3d`) as c " . $this->prepareStockQuery(false);
        return $this->findOne($sql)['c'];
    }



    /**
     * Вывод по комплектам
     * @return array
     * @throws \Exception
     */
    public function getModels()
    {
        $result = [
            'showByRows' => '',
            'posIter' => 0,
            'ComplShown' => 0,
            'wholePos' => 0
        ];
        $result['posIter'] = count($this->row); // кол-во всех моделей
        if ( empty($result['posIter']) ) return $result;

        $posIds = [];
        foreach ($this->row as $pos)
            $posIds[] = $pos['id'];  

        $this->registerTable(['images'=>'i','stl_files'=>'stlf']);
        $rowImages = $this->IMAGES
            ->select(['pos_id','img_name','main','onbody','sketch'])
            ->where(['pos_id','IN',$posIds])
            ->exe();
        $rowStls = $this->STL_FILES
            ->select(['pos_id','stl_name'])
            ->where(['pos_id','IN',$posIds])
            ->exe();

        foreach ( $rowStls as $key => &$rowStl )
        {
            $rowStls[$rowStl['pos_id']] = $rowStl;
            unset($rowStls[$key]);
        }

        $kits = $this->countComplects();
        $this->wholePos = $result['wholePos'] = count($kits); // кол-во комплектов

        ob_start();
            $modelsKitCounters = [
                'newKitEnds' => false,
                'newKitStarts' => false,
            ];
            $result['iter'] = 0;
            foreach ( $kits as $singleKit )
            {
                if ( !isset($singleKit['id']) || empty($singleKit['id']) ) continue;
                $mCount = 0;
                $kitCount = count($singleKit['id'])-1;

                /** @param array $singleKit['id'] - list of models with one number_3d */
                foreach( $singleKit['id'] as &$model )
                {
                    if ( $mCount === 0 ) $modelsKitCounters['newKitStarts'] = true;
                    if ( $mCount === $kitCount ) $modelsKitCounters['newKitEnds'] = true;
                    $result['showByRows'] .= $this->drawModel( $model, $rowImages, $rowStls, true, $modelsKitCounters );

                    $mCount++;
                    $result['iter']++; // Models counter by one kit
                    $modelsKitCounters['newKitStarts'] = false;
                    $modelsKitCounters['newKitEnds'] = false;
                }
                $result['ComplShown']++; // счетчик отрисованных комплектов
            }

            $result['showByRows'] = ob_get_contents();
        ob_end_clean();
        return $result;
    }


    public function countComplects() 
    {
        $numRows = count($this->row);
        $savedrow = array();
        $complects = array();
        $cIt = 0;
        
        for ( $i = 0; $i < $numRows; $i++ )
        {
            if ( empty($this->row[$i]['number_3d']) ) continue;
            $number_3d = $this->row[$i]['number_3d'];
            
            foreach ( $savedrow as &$value ) {
            // проверяем есть ли этот номер в массиве. если есть то пропускаем все такие номера, они уже посчитаны
                if ( $value == $number_3d ) continue(2);
            }

            for ( $j = 0; $j < $numRows; $j++ ) {
                
                $model_type = $this->row[$j]['model_type'];
                
                // если совпадают - значит это комплект
                if ( $number_3d == $this->row[$j]['number_3d'] ) {
                    
                    $id = $this->row[$j]['id'];

                    
                    $complects[$cIt]['number_3d'] = $this->row[$j]['number_3d'];
                    $complects[$cIt]['vendor_code'] = $this->row[$j]['vendor_code'];
                    $complects[$cIt]['modeller3D'] = $this->row[$j]['modeller3D'];
                    $complects[$cIt]['collection'] = $this->row[$j]['collections'];
                    
                    $complects[$cIt]['id'][$id]['id'] = $this->row[$j]['id'];
                    $complects[$cIt]['id'][$id]['collection'] = $complects[$cIt]['collection'];
                    $complects[$cIt]['id'][$id]['vendor_code'] = $this->row[$j]['vendor_code'];
                    $complects[$cIt]['id'][$id]['number_3d'] = $this->row[$j]['number_3d'];
                    $complects[$cIt]['id'][$id]['author'] = $this->row[$j]['author'];
                    $complects[$cIt]['id'][$id]['modeller3D'] = $this->row[$j]['modeller3D'];
                    $complects[$cIt]['id'][$id]['model_type'] = $this->row[$j]['model_type'];
                    $complects[$cIt]['id'][$id]['labels'] = $this->row[$j]['labels'];
                    $complects[$cIt]['id'][$id]['status'] = $this->row[$j]['status'];
                    $complects[$cIt]['id'][$id]['date'] = $this->row[$j]['date'];
                    
                    if (  $this->toPdf === true ) {
                        $complects[$cIt]['model_type'][$id]['id'] = $id;
                        $complects[$cIt]['model_type'][$id]['model_type'] = $model_type;
                        $complects[$cIt]['model_type'][$id]['images'] = $this->get_Images_FromPos($id);
                        $complects[$cIt]['model_type'][$id]['dop_VC'] = $this->get_DopVC_FromPos($id);
                        $complects[$cIt]['model_type'][$id]['model_weight'] = $this->row[$j]['model_weight'];
                        $complects[$cIt]['model_type'][$id]['status'] = $this->row[$j]['status'];
                    }
                    
                    $savedrow[] = $number_3d; // сохранем номер в массив, как посчитанный
                    
                }
            }
            $cIt++;
        }
        return $complects;
    }


}
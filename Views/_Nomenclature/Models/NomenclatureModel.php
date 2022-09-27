<?php
namespace Views\_Nomenclature\Models;
use Views\_Globals\Models\General;
use Views\vendor\core\ActiveQuery;

class NomenclatureModel extends General 
{
    /**
     * NomenclatureModel constructor.
     * @throws \Exception
     */
    public function __construct()
	{
		parent::__construct();
	}

    /**
     * @return array
     * @throws \Exception
     */


    public function getData()
    {
        return $this->getServiceData();
	}


    /**
     * @param $row_id
     * @param $row_val
     * @param $row_tab
     * @return mixed
     * @throws \Exception
     */
    public function dell($row_id, $row_val, $row_tab)
    {
        $count = 0;
        if ( $row_tab == 'collections' )
        {
            $stock = $this->findAsArray(" SELECT id,collections FROM stock WHERE collections like '%$row_val%' ");
            $count = count($stock);
            if ( $count )
            {
                $newCollectionsStrArr = [];
                foreach ($stock as $stockRow)
                {

                    $collArr = explode(';',$stockRow['collections']);
                    foreach ( $collArr as &$coll )
                    {
                        if ( $coll == $row_val ) $coll = "";
                    }
                    $newCollectionsStrArr[$stockRow['id']] = implode(';',$collArr);
                }

                $queryUPDATEColl = $this->insertUpdateRows([$newCollectionsStrArr],'stock');
                if (!$this->affectedRows) 
                    printf( "Error insertUpdateRows in " . __METHOD__  );
            }
        }
        $this->deleteFromTable('service_data','id', $row_id);
		$arr['count'] = $this->affectedRows;
		$arr['dell'] = 1;
		return $arr;
    }

    /**
     * @param $row_id
     * @param $row_tab
     * @param $row_val
     * @return mixed
     * @throws \Exception
     */
    public function edit($row_id, $row_tab, $row_val)
    {
    	// изменяет в самой коллекции
    	$arr['status'] = 0;

    	if ( $row_tab == 'collections' )
		{
			$oldName = $this->findOne( " SELECT name FROM service_data WHERE id='$row_id' ");
			$oldName = $oldName['name'];

			$newCollectionName = $row_val;

			if ( $newCollectionName === $oldName ) return $arr;

			// поменяем коллекции в моделях
			$stock = $this->findAsArray(" SELECT id,collections FROM stock WHERE collections LIKE '%$oldName%' ");
			if ( empty($stock) ) return $arr;

            $newCollectionsStrArr = [];
            foreach ($stock as &$stockRow)
            {
            	$collArr = explode(';',$stockRow['collections']);
                foreach ( $collArr as &$coll )
                {
                    if ( $oldName == $coll )
                    {
                        $coll = $newCollectionName;
                        $stockRow['collections'] = implode(';',$collArr);
                        continue;
                    }
                }
            }
      
            /*
            $collIdStr = 'VALUES ';
            foreach ( $newCollectionsStrArr as $idModel=>$newCollStr ) 
                $collIdStr .= "('".$idModel."','".$newCollStr."'),";
            $collIdStr = trim($collIdStr,',');
            */
            /*
            //INSERT INTO table (id,Col1,Col2) VALUES (1,1,1),(2,2,3),(3,9,3),(4,10,12)
            //ON DUPLICATE KEY UPDATE Col1=VALUES(Col1),Col2=VALUES(Col2);
            $queryString = " INSERT INTO stock (id,collections) $collIdStr
            ON DUPLICATE KEY UPDATE collections=VALUES(collections)";
            $queryUPDATEColl = $this->baseSql($queryString);
            */
            //debugAjax($stock,'$stock2',END_AB);
            //if ( !$queryUPDATEColl ) printf( "Error: %s\n", mysqli_error($this->connection) );


            $resQuery = $this->insertUpdateRows(rows: $stock, table: 'stock');
            if ( !$this->affectedRows )
                printf( "Error: insertUpdateRows " . __METHOD__ );
		}

        $change = $this->update(table: 'service_data', row: ['name'=>$row_val], where: ['id','=',$row_id]);
        
        if ( $this->affectedRows ) 
            $arr['status'] = 1;

		return $arr;
    }

    /**
     * @param $row_value
     * @param $row_tab
     * @return mixed
     * @throws \Exception
     */
    public function add($row_value, $row_tab)
    {
        $found = (new ActiveQuery('service_data'))->service_data
        ->count('c','name')
        ->where(['name','=',$row_value])
        ->and(['tab','=',$row_tab])
        ->asOne()
        ->exe();
		
		// совпадение найдено т.е запись существует
		if ( $found['c'] > 0 ) 
		{
			$arr['status'] = -1;
			echo json_encode($arr);
			exit;
		}

		$date = date('Y-m-d');
        $query = $this->insert('service_data',['name'=>$row_value,'tab'=>$row_tab, 'date'=>$date]);
		if ( $this->lastInsertID )
		{
			$arr['add'] = 1;
			$arr['id'] = $this->lastInsertID;
			$arr['date'] = date_create( $date )->Format('d.m.Y');
			$arr['status'] = 1;

		} else {
			$arr['status'] = 0;
			printf("Error insert in ". __METHOD__ );
		}

		return $arr;
    }

}
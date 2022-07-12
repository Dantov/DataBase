<?php
namespace Views\_Nomenclature\Models;
use Views\_Globals\Models\General;

class NomenclatureModel extends General 
{
    /**
     * NomenclatureModel constructor.
     * @throws \Exception
     */
    public function __construct()
	{
		parent::__construct();
		$this->connectDBLite();
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

                $collIdStr = 'VALUES ';
                foreach ( $newCollectionsStrArr as $idModel=>$newCollStr ) $collIdStr .= "('".$idModel."','".$newCollStr."'),";
                $collIdStr = trim($collIdStr,',');

                $queryString = "INSERT INTO stock (id,collections) $collIdStr
                                  ON DUPLICATE KEY UPDATE collections=VALUES(collections)";

                $queryUPDATEColl = $this->baseSql($queryString);
                if (!$queryUPDATEColl) printf( "Error: %s\n", mysqli_error($this->connection) );
            }
        }

		$this->baseSql(" DELETE FROM service_data WHERE id='$row_id' ");
		
		$arr['count'] = $count;
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
            foreach ($stock as $stockRow)
            {
            	$collArr = explode(';',$stockRow['collections']);
                foreach ( $collArr as &$coll )
                {
                    if ( $oldName == $coll )
                    {
                        $coll = $newCollectionName;
                        $newCollectionsStrArr[$stockRow['id']] = implode(';',$collArr);
                        continue;
                    }
                }
            }
      
            $collIdStr = 'VALUES ';
            foreach ( $newCollectionsStrArr as $idModel=>$newCollStr ) $collIdStr .= "('".$idModel."','".$newCollStr."'),";
            $collIdStr = trim($collIdStr,',');

            //INSERT INTO table (id,Col1,Col2) VALUES (1,1,1),(2,2,3),(3,9,3),(4,10,12)
            //ON DUPLICATE KEY UPDATE Col1=VALUES(Col1),Col2=VALUES(Col2);
            $queryString = " INSERT INTO stock (id,collections) $collIdStr
            ON DUPLICATE KEY UPDATE collections=VALUES(collections)";

            $queryUPDATEColl = $this->baseSql($queryString);
            if (!$queryUPDATEColl) printf( "Error: %s\n", mysqli_error($this->connection) );
		}

        $change = $this->baseSql(" UPDATE service_data SET name='$row_val' WHERE id='$row_id' ");
        if ( $change ) $arr['status'] = 1;

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
    	$querFind = $this->baseSql(" SELECT name,tab FROM service_data WHERE name='$row_value' AND tab='$row_tab' ");
		
		// совпадение найдено т.е запись существует
		if ( $querFind->num_rows !== 0 ) 
		{
			$arr['status'] = -1;
			echo json_encode($arr);
			exit;
		}

		$date = date('Y-m-d');
		$query = $this->baseSql(" INSERT INTO service_data (name,tab,date) VALUES ('$row_value','$row_tab', '$date') ");
		if ( $query )
		{
			//$newId = $this->findOne(" SELECT id FROM service_data WHERE name='$row_value' ");
			//$newId = mysqli_fetch_assoc($querid);
			$arr['add'] = 1;
			$arr['id'] = mysqli_insert_id($this->connection);
			$arr['date'] = date_create( $date )->Format('d.m.Y');
			$arr['status'] = 1;

		} else {
			$arr['status'] = 0;
			printf("Error: %s\n", mysqli_error($this->connection) );
		}

		return $arr;
    }

}
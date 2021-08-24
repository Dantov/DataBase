<?php

namespace Views\vendor\core\db;
use Views\vendor\core\Config;


/**
 * реализует базовые SQL запросы
 */
class BaseSQL
{

    public static $connectObj;
    public $connection;


    /**
     * @param $sqlStr
     * @return bool|\mysqli_result
     * @throws \Exception
     */
    public function baseSql(string $sqlStr)
    {
        if ( empty($sqlStr) ) throw new \Exception('Query string not valid!', 555);
        $query = mysqli_query( $this->connection, $sqlStr );
        if ( !$query )
            throw new \Exception("Error in baseSql() --!! $sqlStr !!-- " . mysqli_error($this->connection), mysqli_errno($this->connection));

        return $query;
    }

    /**
     * @param $sqlStr
     * @return int
     * @throws \Exception
     */
    public function sql($sqlStr)
    {
        $query = $this->baseSql( $sqlStr );
        if ( !$query ) throw new \Exception(__METHOD__ . " Error: " . mysqli_error($this->connection), mysqli_errno($this->connection));

        return $this->connection->insert_id ? $this->connection->insert_id : -1;
    }


    /**
     * @param $tableName
     * @return array|bool
     * @throws \Exception
     */
    public function getTableSchema( string $tableName )
    {
        if ( empty($tableName) ) throw new \Exception('Table name not valid! In ' . __METHOD__, 555);

        $query = $this->baseSql('DESCRIBE ' . $tableName);
        if ( !$query ) return [ 'error' => mysqli_error($this->connection) ];

        $result = [];

        while($row = mysqli_fetch_assoc($query)) $result[] = $row['Field'];

        return $result;
    }



}
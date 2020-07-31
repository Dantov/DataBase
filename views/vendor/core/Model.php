<?php
/**
 * Date: 28.07.2020
 * Time: 15:33
 */

namespace Views\vendor\core;
use Views\vendor\core\db\Database;

/**
 * Class Model
 * Базовый класс для манипуляций с БД
 * @package Views\vendor\core
 */
class Model
{

    public static $connectObj;
    public $connection;

    /**
     * @param array $dbConfig
     * @return \mysqli
     * @throws \Exception
     */
    public function connectDB( array $dbConfig ) : \mysqli
    {
        if ( is_object($this->connection) ) return $this->connection;

        return $this->connection = self::$connectObj = (Database::instance($dbConfig))->getConnection();
    }
    /**
     * return bool
     * @throws \Exception
     */
    public function closeDB() : bool
    {
        return false;
    }



    /**
     *  ==========  CONSTRUCT QUERY  =============
     */

    /**
     *  Проверим на существование конкретной строки
     * @param int $id
     * @param string $table
     * @param string $column
     * @return bool
     * @throws \Exception
     */
    public function checkID( int $id, string $table='stock', string $column='id' ) : bool
    {
        if ( empty($id) || !is_int($id) ) return false;
        $sql = " select 1 from $table where $column='$id' limit 1 ";
        $query = $this->baseSql($sql);
        if ( $query->num_rows ) return true;
        return false;
    }

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
     * @return bool
     * @throws \Exception
     */
    public function sql($sqlStr)
    {
        $query = $this->baseSql( $sqlStr );
        if ( !$query ) throw new \Exception(__METHOD__ . " Error: " . mysqli_error($this->connection), mysqli_errno($this->connection));

        return $this->connection->insert_id ? $this->connection->insert_id : -1;
    }

    /**
     * @param $sqlStr
     * @return array
     * @throws \Exception
     */
    public function findAsArray($sqlStr)
    {
        $query = $this->baseSql( $sqlStr );

        if ( !$query ) throw new \Exception(__METHOD__ . " Error: " . mysqli_error($this->connection), mysqli_errno($this->connection));
        if ( !$query->num_rows ) return [];

        $result = [];
        while ( $data = mysqli_fetch_assoc($query) ) $result[] = $data;
        return $result;
    }

    /**
     * @param string $sqlStr
     * @param string $field
     * поле, элемент в массиве который надо венуть
     * @return array|mixed
     * @throws \Exception
     */
    public function findOne(string $sqlStr, string $field = '')
    {
        if ( !is_string($sqlStr) || empty($sqlStr) ) throw new \Exception('Query string not valid!', 555);

        $result = [];

        $query = $this->baseSql($sqlStr . " LIMIT 1");
        if ( !$query ) throw new \Exception(__METHOD__ . " Error: " . mysqli_error($this->connection), mysqli_errno($this->connection) );

        while ( $data = mysqli_fetch_assoc($query) ) $result[] = $data;

        if ( !empty($result) && empty($field) )
        {
            return $result[0];
        } elseif ( !empty($result) )
        {
            if ( array_key_exists($field, $result[0]) )
                return $result[0][$field];
        }

        return [];
    }

    /**
     * @param $tableName
     * @return array|bool
     * @throws \Exception
     */
    public function getTableSchema(string $tableName)
    {
        if ( empty($tableName) ) throw new \Exception('Table name not valid! In ' . __METHOD__, 555);

        $query = $this->baseSql('DESCRIBE ' . $tableName);
        if ( !$query ) return [ 'error' => mysqli_error($this->connection) ];

        $result = [];

        while($row = mysqli_fetch_assoc($query)) $result[] = $row['Field'];

        return $result;
    }

    /**
     * @param string $tableName
     * @return mixed
     * @throws \Exception
     */
    public function countRows(string $tableName) : int
    {
        if ( empty($tableName) ) throw new \Exception("Table name is empty!");
        return $this->findOne("SELECT COUNT(1) as r FROM $tableName")['r'];
    }

    /**
     * Удаление одной строки по условию
     * @param string $table
     * @param string $primaryKey
     * @param int $key
     * @return bool
     * @throws \Exception
     */
    public function deleteFromTable(string $table, string $primaryKey, int $key) : bool //string $primaryKey, int $key
    {
        if ( empty($table) || empty($primaryKey) || empty($key))
            throw new \Exception("Table, primary key name or id is empty! In " . __METHOD__ );

        $sql = " DELETE FROM $table WHERE $primaryKey='$key' ";
        if ( $this->baseSql($sql) ) return true;

        return false;
    }

    /**
     * Пакетное удаление строк по условию
     * @param $toRemove
     * @param $tableName
     * @param string $primaryKey
     * @return array|bool
     * @throws \Exception
     */
    public function removeRows(array $toRemove, string $tableName, string $primaryKey = 'id')
    {
        if ( empty($toRemove) ) return false;
        if ( empty($tableName) )
            throw new \Exception("Error removeRows() table name might be a string!", 1);

        $ids = '';
        foreach ( $toRemove as $id )
            if ( !empty($id) ) $ids .= $id . ',';

        if (empty($ids)) return false;
        $ids = '(' . trim($ids,',') . ')';

        $rem = $this->baseSql( "DELETE FROM $tableName WHERE $primaryKey IN $ids" );
        if ( !$rem ) return true;

        return false;
    }

    /**
     * Example:
     * INSERT INTO mytable (id, a, b, c)
     * VALUES  (1, 'a1', 'b1', 'c1'),
     * (2, 'a2', 'b2', 'c2'),
     * (3, 'a3', 'b3', 'c3'),
     * (4, 'a4', 'b4', 'c4'),
     * (5, 'a5', 'b5', 'c5'),
     * (6, 'a6', 'b6', 'c6')
     * ON DUPLICATE KEY UPDATE
     * id=VALUES(id),
     * a=VALUES(a),
     * b=VALUES(b),
     * c=VALUES(c)
     *
     * @param array $rows
     * массив строк
     *
     * @param string $table
     * имя таблицы
     *
     * @return bool|int
     * @throws \Exception
     */
    public function insertUpdateRows(array $rows, string $table)
    {
        if ( empty($rows) || empty($table) ) return false;
        $values = '';
        $fields = [];

        foreach ($rows as $row)
        {
            $val = '';
            foreach ($row as $field => $value)
            {
                $fields[$field] = $field;

                $val .= "'".$value."'" . ',';
            }
            $values  .= '(' . trim($val,',') . '),';
        }
        $values  =  trim($values,',');
        $columns = '';
        $update = [];
        foreach ($fields as $field)
        {
            $columns .= $field . ',';
            $update[] = $field . '=VALUES(' . $field . ')';
        }
        $columns = '(' . trim($columns,',') . ')';
        $update = implode(',', $update);

        $sqlStr = "INSERT INTO $table $columns VALUES $values ON DUPLICATE KEY UPDATE $update";

        return $this->sql($sqlStr);
    }



}
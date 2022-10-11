<?php
namespace soffit\db;

use libs\classes\AppCodes;

/**
 * Класс для манипуляций с БД
 * реализует базовые SQL запросы
 */
class BaseSQL implements BaseSQLInterface
{

    protected array $operators = ['=', '<', '>','<=','>=','<>','LIKE','NOT LIKE','IN','BETWEEN','IS NULL'];

    protected int $affectedRows = 0;
    protected int $numRows = 0;
    protected int $lastInsertID = 0;

    protected int $queryCount = 0;

    public function __get( string $varName ) 
    {
        switch ( $varName )
        {
            case 'affectedRows':
                return $this->affectedRows;
            case 'lastInsertID':
                return $this->lastInsertID;
            case 'numRows':
                return $this->numRows;
            case 'queryCount':
                return $this->queryCount;
        }
    }

    /**
     *  ==========  BASE QUERY METHODTS  =============
     */

    /**
     * FOR SELECT
     * @param $sqlStr
     * @return bool|\mysqli_result
     * @throws \Exception
     */
    private function baseSql( string $sqlStr, bool $count=false ) : \PDOStatement
    {
        if ( empty($sqlStr) ) {
            throw new \Exception('Query string not valid!', 555);
        }

        try {
            $pdo = Database::init()->getConnection();

            $query = $pdo->query( $sqlStr );
            if ( $count ) return $query;
            $query->execute();
            $this->affectedRows = $query->fetchColumn();
            $this->queryCount++;
            Database::$overallQuerys++;
        } catch (\Exception $ex) {
            throw new \Error($ex->getMessage(), (int)$ex->getCode());
        }

        return $query;
    }

    /**
     * FOR INSERT UPDATE DELETE
     * @param $sqlStr
     * @return int
     * @throws \Exception
     */
    public function sql( string $sqlStr ) : int
    {
        try {
            $pdo = Database::init()->getConnection();
            $this->affectedRows = (int)$pdo->exec( $sqlStr );    
            $this->lastInsertID = $pdo->lastInsertId();
            
            Database::$overallQuerys++;
            $this->queryCount++;
            
        } catch ( \Exception $ex) {
            throw new \Exception($ex->getMessage(), (int)$ex->getCode());
        }
        
        //$query->rowCount() // Кол-во затронутых строк коммандами INSERT UPDATE DELETE

        return $this->lastInsertID ?: -1;
    }

    /**
     * @param $tableName
     * @return array|bool
     * @throws \Exception
     */
    public function getTableSchema( string $tableName, bool $type=false ) : array
    {
        if (empty($tableName)) {
            throw new \Exception('Table name can\'t be empty in: ' . __METHOD__, 555);
        }
        try {
            $pdo = Database::init()->getConnection();
            $query = $pdo->query('DESCRIBE ' . $tableName, \PDO::FETCH_ASSOC)->fetchAll();
            $this->queryCount++;
            Database::$overallQuerys++;

        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage(), (int)$ex->getCode());
        }
        
        //$query - more detailed array
        $result = [];
        switch ( $type )
        {
            case false:
                foreach($query as $row) {
                    $result[] = $row['Field'];
                }
                break;
            case true:
                foreach($query as $key => $row) {
                    $index = $row['Field'];
                    unset($row['Field']);
                    $result[ $index ] = $row;
                }
                break;
        }
        
        return $result;
    }

    /**
     *  Проверим на существование конкретной строки
     * @throws \Exception
     */
    public function checkID( int $id, string $table='stock', string $column='id' ) : bool
    {
        if ( $id <= 0 || $id > PHP_INT_MAX ) {
            throw new \Exception(__METHOD__ . " Wrong ID comes!");
        }
        
        try {
            $pdo = Database::init()->getConnection();
            $q = $pdo->query( " select 1 from $table where $column='$id' limit 1 " );
            $this->queryCount++;
            Database::$overallQuerys++;
            if ( $q->fetchColumn() ) {
                return true;
            }
            
        } catch (Exception $ex) {
            throw new \Exception($ex->getMessage(), (int)$ex->getCode() );
        }
        
        return false;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function countRows( string $tableName, array $where = []  ) : int
    {
        if (empty($tableName)) {
            throw new \Exception(" Table name can not be empty in " . __METHOD__, 605);
        }
        $whereStr = $this->genWhereStr($where, $this->getTableSchema($tableName));
        if ( $whereStr ) $whereStr = "WHERE " . $whereStr;

        try {
            $pdo= Database::init()->getConnection();
            return $this->numRows = $pdo->query("SELECT COUNT(1) FROM $tableName $whereStr")->fetchColumn();
            $this->queryCount++;
            Database::$overallQuerys++;
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage(), (int)$ex->getCode() );
        }
    }
    /**
     * Generate Where string like:
     * `column`='value' 
     * 
     */
    protected function genWhereStr( array $where, array $schema ) : string
    {
        if (empty($where)) return '';

        if ( !array_is_list($where) || (count($where) !== 3) )
            throw new \Exception( "Where clause is not valid in " . __METHOD__, 607 );

        $column = $where[0];
        if ( !in_array($column, $schema) )
                throw new \Exception( "Wrong left operand in WHERE clause. " . __METHOD__, 552 );
        
        $oper = $where[1];
        if ( !in_array(mb_strtoupper($oper), $this->operators) )
                throw new \Exception( "Wrong operator in WHERE clause." . __METHOD__, 553 );
        
        $val = $where[2];
        if ( !$val )
            throw new \Error("Wrong right operand in WHERE clause. " . __METHOD__, 554 );
        if ( mb_strtoupper($oper) !== 'IN' )
            $val = "'" . $val . "'";
        return "`$column`$oper$val";
    }

    /**
     *  ==========  CONSTRUCT QUERY  =============
     */

    /**
     * @return array
     * @throws \Exception
     */
    public function findAsArray( string $sqlStr ) : array
    {
        try {
            $pdo = Database::init()->getConnection();

            $query = $pdo->query( $sqlStr, \PDO::FETCH_ASSOC )->fetchAll();
            $this->affectedRows = count($query);
            $this->numRows = $this->affectedRows;

            $this->queryCount++;
            Database::$overallQuerys++;

            return $query;
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage(), (int)$ex->getCode() );
        }
    }

    /**
     * @param string $sqlStr
     *
     * поле, элемент в массиве который надо венуть
     * @param string $field
     * @return mixed
     * @throws \Exception
     */
    public function findOne(string $sqlStr, string $field = '') : string|array
    {
        if (empty($sqlStr)) {
            throw new \Exception(__METHOD__ . " Query string is empty!");
        }

        try {
            $pdo = Database::init()->getConnection();
            $query = $pdo->query( $sqlStr . " LIMIT 1 ", \PDO::FETCH_ASSOC );
            $res = $query->fetch();
            if ( !$res ) $res = [];

            $this->numRows = count($res);
            $this->queryCount++;
            Database::$overallQuerys++;
            if ( array_key_exists($field, $res) ) {
                return $res[$field];
            }
            return $res;
            
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage(), (int)$ex->getCode() );
        }    
    }
    
    /**
     * Удаление по условию
     * @param string $table
     * @param string $primaryKey
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public function deleteFromTable(string $table, string $primaryKey, string $value, string $oper='=') : bool
    {
        if (empty($table) || empty($primaryKey) || empty($value) || ((int)$value <=0 ) || ( (int)$value > PHP_INT_MAX) ) {
            throw new \Exception("Table, primary key name or id is not valid! In " . __METHOD__);
        }
        if ( !in_array(mb_strtoupper($oper), $this->operators) )
                throw new \Exception( "Wrong operand in " . __METHOD__, 678 );
        
        $this->sql( "DELETE FROM $table WHERE $primaryKey$oper'$value'" );
        if ( $this->affectedRows ) {
            return true;
        }
        return false;
    }

    /**
     * Пакетное удаление строк по IN условию
     * @return array|bool
     * @throws \Exception
     */
    public function removeRows(array $ids, string $tableName, string $primaryKey = 'id') : bool
    {
        if (empty($ids)) {
            return false;
        }
        if (empty($tableName)) {
            throw new \Exception("Error removeRows() table name might be not empty!", 1);
        }
        
        $id_s = '';
        foreach ( $ids as $rows )
        {
            if ( is_array($rows) )
            {
                foreach ( $rows as $key => $id ) 
                {
                    if ( ($key == $primaryKey) && !empty($id) )
                        $id_s .= $id . ',';
                }
                continue;
            }
            if (!empty($rows)) {
                $id_s .= $rows . ',';
            }
        }
            
        if (empty($id_s)) return false;

        $id_s = '(' . trim($id_s,',') . ')';  
        $sql = "DELETE FROM $tableName WHERE $primaryKey IN $id_s";
        $rem = $this->sql($sql);
        if ( $rem === -1 ) {
            return true;
        }
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
     * a=VALUES(a),d
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
    public function insertUpdateRows( array $rows, string $table )
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
                if ( is_int($value) )
                {
                    $val .= $value . ',';    
                } else {
                    $val .= "'".$value."'" . ',';    
                }
                
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
        //debugAjax($sqlStr,'$sqlStr',END_AB);
        //debugAjax($sqlStr,'$sqlStr');

        return $this->sql($sqlStr);
    }

    
    /**
     * UPDATE `users_online` SET `id`='[value-1]',`session_id`='[value-2]',`user_id`='[value-3]',`user_ip`='[value-4]',`date_connect`='[value-5]',`date_disconnect`='[value-6]' WHERE 1
     * @param string $table
     * @param array $row
     * @param array $where
     * @return int
     * @throws \Exception
     */
    public function update( string $table, array $row, array $where=[] ) : int
    {
        $schema = $this->getTableSchema($table);
        foreach ( $row as $tField => $v )
        {
            if ( !in_array_recursive( $tField, $schema ) )
                throw new \Error("Field '". $tField ."' does not exist in table '" . $table . "'  " . __METHOD__, 444 );
        }

        $data = '';
        foreach ( $row as $field => $value )
            $data  .= $field . "="."'". $value ."'" . ",";

        $data  =  trim($data,',');

        $whereStr = '';
        if ( $where )
            $whereStr = 'WHERE ' . $this->genWhereStr($where,$schema);
        
        $sql = "UPDATE $table SET $data $whereStr";
        return $this->sql($sql);
    }
    
    /**
     * INSERT INTO pushnotice SET `pos_id`='2233', `number_3d`='0005677', `vendor_code`='XXX';
     * INSERT INTO pushnotice (`pos_id`, `number_3d`, `vendor_code`, `model_type`, `image`, `status`, `ip`, `name`, `addedit`, `date`) 
        VALUES ('2136','0005778','AFG-2','Kabol-7', 'img2.jpg', '26', '192.168.1.57', 'sergi-5', '1', '2022-11-10');
     * @param string $table
     * @param array $row
     * @return int inserted rows
     */
    public function insert( string $table, array $row, string $primaryKey='id' ) : int
    {
        if ( empty($row) ) {
            throw new \Exception("Insert Data is Empty in ". __METHOD__, 444);
        }
        $tableSchema = $this->getTableSchema($table);
        
        if ( !in_array($primaryKey, $tableSchema) ){
            throw new \Exception("Primary key not valid in ". __METHOD__, 444);
        }
        
        $queryStr = '';   
        if ( array_is_list($row) )
        {
            if ( isset($row[0]) && is_array($row[0]) ) {
                return $this->insertBatch($table, $row, $tableSchema, $primaryKey);
            }
            // comes like: 'value', 'value', 'value' ...
            $columns = $this->makeColumnsStr($row, $tableSchema, $primaryKey);
            $values = $this->makeValuesStr($row);
            
            $queryStr = "INSERT INTO `$table` $columns VALUES $values";
        } else {
            // comes like: 'pos_id' => 'value', 'status' => 'value' ...
            $data = $this->makeDataStr($row, $table, $tableSchema);
            $queryStr = "INSERT INTO `$table` SET $data ";
        }
        
        return $this->sql($queryStr);
    }
    
    /* NEED TO CHANGE FOR REAL PREPARED QUERY
     * EXAMPLE 1

     * Выполнение запроса с передачей ему массива параметров 
        $sql = 'SELECT name, colour, calories FROM fruit WHERE calories < :calories AND colour = :colour';
        
        $sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->execute(array('calories' => 150, 'colour' => 'red'));
        $red = $sth->fetchAll();
        // Ключи массива также могут начинаться с двоеточия ":" (необязательно) 
        $sth->execute(array(':calories' => 175, ':colour' => 'yellow'));
        $yellow = $sth->fetchAll();
    */
    protected function insertBatch( string $table, array $rows, array $tableSchema, string $primaryKey ) : int
    {
        $pdo = Database::init()->getConnection();

        $execs = [];
        foreach ( $rows as $row )
        {
            $queryStr = '';
            if ( array_is_list($row) )
            {
                $columns = $this->makeColumnsStr($row, $tableSchema, $primaryKey);
                $values = $this->makeValuesStr($row);
                $queryStr = "INSERT INTO `$table` $columns VALUES $values";    
            } else {
                $data = $this->makeDataStr($row, $table, $tableSchema);
                $queryStr = "INSERT INTO `$table` SET $data ";
            }
            
            try {
                $execs[] = $pdo->prepare($queryStr);
            } catch (\Exception $ex) {
                throw new \Exception( $ex->getMessage(), $ex->getCode());
            }
        }
        
        try 
        {
            foreach ( $execs as $query ) 
            {
                if ( !$query === false ) {
                    if ($query->execute()) {
                        $this->queryCount++;
                        Database::$overallQuerys++;
                        $this->affectedRows++;
                    }
                }
            }
            return $this->lastInsertID = $pdo->lastInsertId();
            
        } catch (\Exception $ex) {
            throw new \Exception( $ex->getMessage(), $ex->getCode());
        }
    }
    
    
    /*
     * EXAMPELE 2
        $array = [];
        foreach ($dataArr as $k=>$v) {
        // $x = 2020, the variable is predetermined in advance, does not change the essence
        $array[] = [$x, $k, $v];
        }
        $sql = ("INSERT INTO `table` (`field`,`field`,`field`) VALUES (?,?,?)");

        $db->queryBindInsert($sql,$array);
    */
    private function queryBindInsert($sql,$bind) 
    {
        $pdo = Database::init()->getConnection();
        $stmt = $pdo->prepare($sql);

        if(count($bind)) {
            foreach($bind as $param => $value) {
                $c = 1;
                for ($i=0; $i<count($value); $i++) {
                    $stmt->bindValue($c++, $value[$i]);
                }
                $stmt->execute();
            }
        }
    }
    
    /**
     * @param array $row
     * @return string
     * @throws \Exception
     */
    protected function makeValuesStr( array $row ) : string 
    {
        if ( empty($row) ) {
            throw new \Exception("Insert Data is Empty in ". __METHOD__, 444);
        }

        $values = '';
        foreach ( $row as $colValue ) {
            $values .= "'". $colValue ."'" . ",";
        }
        return '(' . rtrim($values,',') . ')';
    }
    protected function makeColumnsStr( array $row, array $tableSchema, string $primaryKey ) : string 
    {
        $columns = '';
        $c = count($row);
        for ( $i=0; $i < $c; $i++ ) {
            if ( $tableSchema[$i] === $primaryKey ){ $c++; continue;} // We dont want prim Key becouse its auto increment
            $columns .= '`'. $tableSchema[$i] .'`' . ',';
        }
        return '(' . rtrim($columns,',') . ')';
    }
    protected function makeDataStr( array $row, string $table, array $tableSchema ) : string 
    {
        if ( empty($row) ) {
            throw new \Exception("Error Data in ". __METHOD__, 444);
        }
        
        $data = '';
        foreach ( $row as $column => $value ) {
            if ( !in_array($column, $tableSchema) ) {
                throw new \Exception('Cant find ' . $column . " in table " . $table . __METHOD__, 555);
            }
            $data .= "`". $column ."`=" ."'". $value ."',";
        }
        return rtrim($data,',');
    }

}
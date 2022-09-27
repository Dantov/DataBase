<?php

namespace Views\vendor\core\db;

/**
 *
 * @author dantov
 */
interface BaseSQLInterface 
{
    /**
     * For any query 
     * @return affected rows count or last insert ID
     */
    public function sql( string $sqlStr ) : int;

    /**
     * @return array of DB table
     * @param type bool - TRUE: full schema with field types, FALSE: just field names 
     */
    public function getTableSchema( string $tableName, bool $type=false ) : array;
    
}
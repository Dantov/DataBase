<?php

namespace soffit\db;

/**
 *
 * @author dantov
 */
interface DatabaseInterface {
    
    public static function setConfig( array $db ) : void;    
    public static function init( array $dbConfig = [] ) : Database;
    
    //public function connect() : void;
    public function ping( bool $reconnect=false ) : bool;
    public function getConnection() : \PDO;
    public function isConnected() : bool;
    public function close() : bool;
    public function destroy() : bool;
    
}

<?php
/**
 * Date: 23.08.2021
 * Time: 21:21
 */

namespace Views\vendor\core\db;

use Views\vendor\core\Config;
use Views\vendor\core\Model;


/**
 * Реализует общий функционал создания SQL запросов, для поиска/записи в БД
 */
class QueryBuilder extends Model
{

    /**
     * массив имен таблиц из бд
     * @var array
     */
    private $tables = [];

    /**
     * Массив объектов таблиц Table
     * @var array
     */
    private $TABLES = [];


    private $badChars = ['\'','"', ',', '\\','/', '|', '<', '>','+','-','?','&'];


    /**
     * QueryBuilder constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->connectDB( Config::get() );
    }

    protected function checkStr( string $str ) : bool
    {
        // проверить каждый символ поля
        $symbols = preg_split('//u',$str,-1,PREG_SPLIT_NO_EMPTY);
        foreach ( $symbols as $symbol )
        {
            if ( in_array($symbol, $this->badChars) )
                return false;
        }

        return true;
    }

    /**
     * @param $tables
     * @return bool
     * @throws \Exception
     */
    public function registerTable( $tables ) : bool
    {
        if ( empty($tables) )
            return false;

        if ( is_string($tables) )
        {
            if ( !$this->checkStr($tables) ) return false;

            $this->tables[$tables] = $this->getTableSchema($tables);
            $this->TABLES[$tables] = new Table( $tables, $this->tables[$tables] );

            return true;
        }

        if ( is_array($tables) )
        {
            foreach ( $tables as $tableName )
            {
                if ( !$this->checkStr($tableName) ) return false;

                $schema = $this->getTableSchema($tableName);
                $this->tables[$tableName] = $schema;
                $this->TABLES[$tableName] = new Table( $tableName, $schema );
            }

            return true;
        }

        return false;
    }

    /**
     * @param array $tables
     * @return array
     * @throws \Exception
     */
    public function getRegisteredTables( $tables = [] ) : array
    {
        if ( is_string($tables) && !empty($tables) )
        {
            if ( array_key_exists($tables,$this->tables) )
                return $this->tables[$tables];

            throw new \Error("No table registered under name: " . $tables, 500 );
        }

        if ( is_array($tables) && !empty($tables) )
        {
            $result = [];
            foreach ( $tables as $tableName )
            {
                if ( array_key_exists($tableName,$this->tables) )
                {
                    $result[$tableName] = $this->tables[$tableName];
                } else {
                    throw new \Error("No table registered under name: " . $tableName, 500 );
                }
            }
            return $result;
        }

        return $this->tables;
    }

    /**
     * @param string $tableName
     * @return Table
     */
    public function __get(string $tableName )
    {
        if ( array_key_exists($tableName,$this->TABLES) )
        {
            return $this->TABLES[$tableName];
        } else {
            throw new \Error("No table registered under name: " . $tableName, 500 );
        }
    }

    public function select( array $fields ) : QueryBuilder
    {


        return $this;
    }

    public function from( string $table ) : QueryBuilder
    {



        return $this;
    }

    public function where() : QueryBuilder
    {



        return $this;
    }

}
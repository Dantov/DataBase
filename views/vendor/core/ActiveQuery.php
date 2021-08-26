<?php
/**
 * Created by User: BVA
 * Date: 25.08.2021
 * Time: 13:20
 */

namespace Views\vendor\core;

use Views\vendor\core\db\Table;
use Views\vendor\libs\classes\Validator;

/**
 * Функционал взаимодействия с таблицами БД
 * Class ActiveQuery
 * @package Views\vendor\core
 */
class ActiveQuery extends Model
{




    /**
     * список имен таблиц из бд
     * @var array
     */
    private $tables = [];

    /**
     * Массив объектов таблиц Table
     * @var array
     */
    private $TABLES = [];

    /**
     * @var Validator
     */
    private $validator;


    /**
     * ActiveQuery constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->validator = new Validator();

        $this->connectDB();
    }


    /**
     * @param string $tableName
     * @return Table
     */
    public function __get( string $tableName )
    {
        if ( array_key_exists($tableName,$this->TABLES) )
        {
            return $this->TABLES[$tableName];
        } else {
            throw new \Error("No table registered under name: " . $tableName, 500 );
        }
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
            if ( !$this->validator->validateTableName($tables) ) return false;

            $this->tables[] = $tables;
            $this->TABLES[$tables] = new Table( $tables );

            return true;
        }

        if ( is_array($tables) )
        {
            foreach ( $tables as $tableName )
            {
                if ( !$this->validator->validateTableName($tableName) ) return false;

                $this->tables[] = $tableName;
                $this->TABLES[$tableName] = new Table( $tableName );
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
        $result = [];

        if ( is_string($tables) && !empty($tables) )
        {
            if ( in_array($tables,$this->tables) )
                return $result[$tables] = $this->TABLES[$tables]->getSchema();

            throw new \Error("No table registered under name: " . $tables, 500 );
        }

        if ( is_array($tables) && !empty($tables) )
        {

            foreach ( $tables as $tableName )
            {
                if ( in_array($tableName,$this->tables) )
                {
                    $result[$tableName] = $this->TABLES[$tableName]->getSchema();
                } else {
                    throw new \Error("No table registered under name: " . $tableName, 500 );
                }
            }
            return $result;
        }

        return $result;
    }


    /**Свяжем таблицы по полям
     * @param array $keyFirst
     * @param array $keySecond
     * @return array
     */
    public function link( array $keyFirst, array $keySecond )
    {
        // проверку ключей и таблиц!!!!!!!!!!!!

        $tNameFirst = array_key_first($keyFirst);
        $tFieldFirst = $keyFirst[$tNameFirst];

        $tNameSecond = array_key_first($keySecond);
        $tFieldSecond = $keySecond[$tNameSecond];

        $res = [];

        if ( array_key_exists( $tNameFirst, $this->TABLES ) )
        {

            $res[] = $this->TABLES[ $tNameFirst ]->link($tNameSecond, $tFieldSecond, $tFieldFirst );
        }


        if ( array_key_exists( $tNameSecond, $this->TABLES ) )
        {
            $res[] = $this->TABLES[ $tNameSecond ]->link($tNameFirst, $tFieldFirst, $tFieldSecond );
        }

        return $res;
    }


}
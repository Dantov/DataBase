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
     * @param string $alias
     * @return Table
     * @throws \Exception
     */
    public function registerTable( $tables, string $alias='' ) : Table
    {

        if ( !empty($tables) && is_string($tables) )
        {
            if ( !$this->validator->validateTableName($tables) )
                throw new \Error("Table name '" . $tables . "' not valid.", 500 );

            $this->tables[] = $tables;
            $TName = new Table( $tables );
            if ( $alias )
                $TName->alias = $alias;
            return $this->TABLES[$tables] = $TName;
        }

        if ( !empty($tables) && is_array($tables) )
        {
            foreach ( $tables as $tableName => $alias )
            {
                if ( !$this->validator->validateTableName($tableName) )
                    throw new \Error("Table name '" . $tableName . "' not valid.", 500 );

                $this->tables[] = $tableName;
                $TName = new Table( $tableName );

                $TName->alias = $alias;

                $this->TABLES[$tableName] = $TName;
            }

            // вернет последнюю добавленную табл.
            return $this->TABLES[array_key_last($this->TABLES)];
        }

        throw new \Error("Table name '" . $tables . "' not valid.", 500 );
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


    /**
     * Свяжем таблицы по полям
     *
     * @param array $key_table_first ---- [ 'id'=>$stock ]
     * @param string $operator
     * @param array $key_table_second ---- [ 'pos_id'=>$images ]
     * @return array
     */
    public function link( array $key_table_first, string $operator ,array $key_table_second )
    {

        $nameKeyFirst = array_key_first($key_table_first); // имя ключа 1
        $tableFirst = $key_table_first[$nameKeyFirst]; // таблица 1

        $nameKeySecond = array_key_first($key_table_second); // имя ключа 2
        $tableSecond = $key_table_second[$nameKeySecond]; // таблица 2

        if ( !($tableFirst instanceof Table) || !($tableSecond instanceof Table) )
            throw new \Error("Wrong tables objects given in: " . __METHOD__, 500 );

        if ( !in_array( $tableFirst, $this->TABLES ) && !in_array( $tableSecond, $this->TABLES ) )
            throw new \Error("Some tables not register in : " . __CLASS__, 500 );

        $tNameFirst = $tableFirst->getName();
        $tNameSecond = $tableSecond->getName();

        if ( !in_array($nameKeyFirst,$tableFirst->showSchema()) )
            throw new \Error("Table '" . $tNameFirst ."' don't contain field '" . $nameKeyFirst . "'" . __CLASS__, 500 );

        if ( !in_array($nameKeySecond,$tableSecond->showSchema()) )
            throw new \Error("Table '" . $tNameSecond ."' don't contain field '" . $nameKeySecond . "'" . __CLASS__, 500 );

        $res[] = $this->TABLES[ $tNameFirst ]->link($tNameSecond, $nameKeySecond, $operator, $nameKeyFirst );
        $res[] = $this->TABLES[ $tNameSecond ]->link($tNameFirst, $nameKeyFirst, $operator, $nameKeySecond );

        return $res;
    }


}
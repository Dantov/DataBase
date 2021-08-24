<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 24.08.2021
 * Time: 0:36
 */

namespace Views\vendor\core\db;

use Views\vendor\core\Model;


class Table extends Model
{


    protected $tableName = '';
    /**
     * список полей таблицы
     * @var array
     */
    protected $fields = [];

    /**
     * текущий запрос Select | Insert | Delete ...
     * @var string
     */
    protected $statementQuery = '';


    protected $asIs = 'array';



    public function __construct( string $tableName, array $schema )
    {
        $this->fields = $schema;
        $this->tableName = $tableName;
    }

    protected function checkCurrentQuery() : bool
    {
        if ( !empty($this->statementQuery) )
            throw new \Error("Only one query statement can be in one time.", 500);

        return true;
    }


    public function getSchema()
    {
        return $this->fields;
    }

    public function getStatementQuery() : string
    {
        return $this->statementQuery;
    }




    public function select( array $fields ) : Table
    {
        $this->checkCurrentQuery();

        $this->statementQuery = "SELECT ";

        foreach ( $fields as $field )
        {
            if ( !in_array($field, $this->fields) )
                throw new \Error("Field " . $field . " not found in table " . $this->tableName, 500);

            $this->statementQuery .= $field . ',';
        }

        $this->statementQuery = trim($this->statementQuery,',') . ' FROM ' . $this->tableName;


        return $this;
    }


    public function where( array $statement ) : Table
    {
        if ( empty($this->statementQuery) )
            throw new \Error("Query statement must be select before.", 500);

        $field = $statement[0];
        $operator = $statement[1];
        $needle = $statement[2];

        if ( !in_array($field, $this->fields) )
            throw new \Error("Field " . $field . " not found in table " . $this->tableName, 500);

        $trustedOp = ['=', '<', '>', '<>'];
        if ( !in_array($operator, $trustedOp) )
            throw new \Error("Wrong operator in " . __METHOD__, 500);

        // $needle надо проверять?

        $this->statementQuery .= " WHERE " . $field . $operator . "'" . $needle . "'";

        return $this;
    }

    public function one()
    {
        $this->asIs = 'single';
        return $this;
    }

    public function array()
    {
        $this->asIs = 'array';
        return $this;
    }

    /**
     * Выполнить запрос
     * @throws \Exception
     */
    public function exe()
    {
        if ( empty($this->statementQuery) )
            throw new \Error("Query statement is empty.", 500);

        $this->connectDB();

        switch ($this->asIs)
        {
            case "array":
                return $this->findAsArray($this->statementQuery);
                break;
            case "single":
                return $this->findOne($this->statementQuery);
                break;
        }

        return false;
    }

}
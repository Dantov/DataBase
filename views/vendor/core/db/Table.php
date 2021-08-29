<?php
/**
 * Created by User: BVA
 * Date: 24.08.2021
 * Time: 0:36
 */

namespace Views\vendor\core\db;


/**
 *
 * Class Table
 * @package Views\vendor\core\db
 */
class Table extends QueryBuilder
{

    protected $tableName = '';
    protected $linkedTables = [];



    public $alias = '';
    /**
     * список полей таблицы
     * @var array
     */
    protected $fields = [];


    public function __construct( string $tableName )
    {
        parent::__construct();

        $this->fields = $this->getTableSchema($tableName);
        $this->tableName = $tableName;
    }

    public function showSchema()
    {
        return $this->fields;
    }

    public function getStatementQuery() : string
    {
        return $this->statementQuery;
    }


    public function getName() : string
    {
        return $this->tableName;
    }


    public function link( string $tName, string $tField, string $operator, string $tSelfField )
    {
        $this->linkedTables[$tName] = [];
        $this->linkedTables[$tName][$tSelfField] = $tField;
        $this->linkedTables[$tName]['operator'] = $operator;

        return $this->linkedTables;
    }

}
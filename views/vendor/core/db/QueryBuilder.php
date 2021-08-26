<?php
/**
 * Created by User: BVA
 * Date: 23.08.2021
 * Time: 21:21
 */

namespace Views\vendor\core\db;

use Views\vendor\core\Model;
use Views\vendor\libs\classes\Validator;


/**
 * Реализует общий функционал построения SQL запросов, для поиска в БД
 */
class QueryBuilder extends Model
{

    /**
     * текущий запрос Select | Insert | Delete ...
     * @var string
     */
    protected $statement_SELECT = '';

    protected $statement_FROM = '';
    /**
     * mun3d,id,img_name ...
     * @var array
     */
    protected $statement_FIELDS = [];
    
    protected $statement_JOIN = [];

    protected $statement_WHERE = [];

    protected $statement_LIMIT = '';

    protected $statement_ORDER_BY = '';

    protected $statement_GROUP_BY = '';

    public $buildedQuery = '';

    protected $asIs = 'array';
    protected $asOneField = '';

    /**
     * Trusted Operators
     * @var array
     */
    protected $operators = [];


    /**
     * @var Validator
     */
    private $validator;

    /**
     * QueryBuilder constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->connectDB();

        $this->validator = new Validator();

        $this->operators = ['=', '<', '>', '<>','LIKE','IN','BETWEEN','IS NULL'];
    }


    protected function currentQueryIs( bool $empty=false ) : bool
    {

        if ( $empty === true && !empty($this->statement_SELECT) )
            throw new \Error("Only one query statement can be in one time.", 500);

        if ( $empty === false && empty($this->statement_SELECT) )
            throw new \Error("Query statement is empty.", 500);

        return true;
    }

    protected function checkField( string $field ) : bool
    {
        if ( !in_array($field, $this->fields) )
            throw new \Error("Field " . $field . " not found in table " . $this->tableName, 500);

        return true;
    }



    public function select( array $fields ) : QueryBuilder
    {
        $this->currentQueryIs(true);

        $this->statement_SELECT = "SELECT";

        foreach ( $fields as $field )
        {
            $this->checkField($field);

            $this->statement_FIELDS[] =  ($this->alias ? $this->alias . "." :"") . $field;
        }

        $this->statement_FROM = 'FROM ' . $this->tableName . ($this->alias ? " as " . $this->alias :"");

        return $this;
    }
    
    
    public function join( Table $table, array $select, string $on ) : QueryBuilder
    {
        $joined_TName = $table->getName();
        $joined_TFields = $table->showSchema();

        if ( !$table->alias )
            throw new \Error("For Join operations table alias needed in " . $joined_TName, 500);

        /*
        $leftField = $on[0]??'';
        $operator = $on[1]??'';
        $rightField = $on[2]??'';
        */

        foreach ( $select as $fieldName )
        {
            if ( !in_array( $fieldName, $joined_TFields ) )
                throw new \Error("Field " . $fieldName . " not found in table " . $joined_TName, 500);

            $this->statement_FIELDS[] = $table->alias . "." . $fieldName;
        }

        $operator = $on;
        $operator = mb_strtoupper($operator);
        if ( !in_array($operator, $this->operators) )
            throw new \Error("Wrong operator in " . __METHOD__, 500);



        if ( !$this->linkedTables[$joined_TName] )
            throw new \Error("Tables ". $joined_TName ." and ". $this->tableName ." need to be linked before by using ActiveQuery link() method " . __METHOD__, 500);

            $joined_self_Field = array_key_first($this->linkedTables[$joined_TName]);
            $joined_t_Field = $this->linkedTables[$joined_TName][$joined_self_Field];

            $leftField = $this->alias .".". $joined_self_Field;
            $rightField = $table->alias .".". $joined_t_Field;


        /*
        // Alias для левого поля
        if ( in_array( $leftField, $joined_TFields ) )
        {
            $leftField = $table->alias . $leftField;
        }
        if ( in_array( $leftField, $this->fields ) )
        {
            $leftField = $this->alias . $leftField;
        } else {
            throw new \Error("Wrong field in ON clause " . __METHOD__, 500);
        }

        // Alias для Правого поля
        if ( in_array( $rightField, $joined_TFields ) )
        {
            $rightField = $table->alias . $rightField;
        }
        if ( in_array( $rightField, $this->fields ) )
        {
            $rightField = $this->alias . $rightField;
        } else {
            throw new \Error("Wrong field in ON clause " . __METHOD__, 500);
        }
        */

        $this->statement_JOIN[] = " LEFT JOIN " . $joined_TName . " as " . $table->alias . " ON " . $leftField . $operator .  $rightField;

        return $this;
    }


    public function where( $statementL, string $operator='', $statementR = '', string $andStatementR = null ) : QueryBuilder
    {
        $this->currentQueryIs(false);

        if ( is_array($statementL) && !empty($statementL) )
        {
            $operator = $statementL[1]??'';
            $statementR = $statementL[2]??'';
            $andStatementR = $statementL[3]??'';
            $statementL = $statementL[0]??'';
        }

        $andWhere = [];

        $this->checkField($statementL);

        $andWhere['left'] = $statementL;

        $andWhere['op'] = mb_strtoupper($operator);
        if ( !in_array($andWhere['op'], $this->operators) )
            throw new \Error("Wrong operator in " . __METHOD__, 500);

        // Обработка операторов
        switch ( $andWhere['op'] )
        {
            case "IN":
                $andWhere['op'] = " " . $andWhere['op'] . " ";
                if ( is_array($statementR) && !empty($statementR) )
                {
                    $statementRStr = '';
                    foreach ( $statementR as $ids )
                        $statementRStr .= $ids . ",";
                    $statementRStr = '(' . trim($statementRStr,',') . ')';
                    $andWhere['right'] = $statementRStr;
                }
                if ( is_string($statementR) && !empty($statementR) )
                    $andWhere['right'] = '(' . $statementR . ')';

                break;

            case "BETWEEN":

                $andWhere['op'] = " " . $andWhere['op'] . " ";
                $andWhere['right'] = "'" . $statementR . "' AND '" . $andStatementR . "'";

                break;

            case "LIKE":

                $andWhere['op'] = " " . $andWhere['op'] . " ";
                $andWhere['right'] = "'" . $statementR . "'";

                break;

            case "IS NULL":

                $andWhere['op'] = " " . $andWhere['op'];
                $andWhere['right'] = '';

                break;

            default:
                $andWhere['right'] = "'" . $statementR . "'";
                break;
        }

        $this->statement_WHERE[] = $andWhere;

        return $this;
    }

    public function and( $statementL, string $operator='', $statementR = '', string $andStatementR = null ) : QueryBuilder
    {
        $this->statement_WHERE[] = 'and';
        return $this->where($statementL,$operator,$statementR,$andStatementR);
    }

    public function or( $statementL, string $operator='', $statementR = '', string $andStatementR = null ) : QueryBuilder
    {
        $this->statement_WHERE[] = 'or';
        return $this->where($statementL,$operator,$statementR,$andStatementR);
    }

    /**
     * @param int $count
     * @param int $offset
     * @return QueryBuilder
     * @throws \Exception
     */
    public function limit(int $count, int $offset = 0 ) : QueryBuilder
    {
        if ( $this->validator->validateField('limit',$count,['int'=>'','min'=>1, 'max'=>PHP_INT_MAX]) )
            $this->statement_LIMIT = "LIMIT " . $count;

        if ( $offset )
            if ( $this->validator->validateField('limit',$offset,['int'=>'','min'=>1, 'max'=>PHP_INT_MAX]) )
                $this->statement_LIMIT .= ", " . $offset;

        return $this;
    }

    public function orderBy( string $field, string $direct = 'DESC' ) : QueryBuilder
    {
        $this->checkField($field);

        $trBy = ['DESC','ASC'];
        if ( !in_array($direct, $trBy) )
            throw new \Error("Order BY direction can be DESC or ASC only! ", 500);

        $this->statement_ORDER_BY = "ORDER BY " . $field . " " . $direct;

        return $this;
    }

    public function groupBy( string $field ) : QueryBuilder
    {
        $this->checkField($field);

        $this->statement_GROUP_BY = "GROUP BY " . $field;

        return $this;
    }


    public function asOne( string $field='' )
    {
        if ( $field && !in_array($field,$this->fields) )
            throw new \Error("Field '" . $field . "' not found in table " . $this->tableName, 500);

        $this->asOneField = $field;
        $this->asIs = 'single';
        return $this;
    }

    public function asArray()
    {
        $this->asIs = 'array';
        return $this;
    }

    public function build() : string
    {
        $BuildedQuery = $this->statement_SELECT . " ";

        $statement_FIELDS = '';
        foreach ( $this->statement_FIELDS as $statementField )
            $statement_FIELDS .= $statementField . ", ";

        $statement_FIELDS = trim($statement_FIELDS,', ');

        $BuildedQuery .= $statement_FIELDS ." " . $this->statement_FROM;

        // JOIN CLAUSE
        if ( !empty($this->statement_JOIN) )
        {
            foreach ( $this->statement_JOIN as $statementJoin )
                $BuildedQuery .= $statementJoin;
        }

        // WHERE CLAUSE
        if ( !empty($this->statement_WHERE) )
        {
            //debug($this->statement_WHERE,"where");
            $whereStr = '';
            $haveOR = false;
            foreach ( $this->statement_WHERE as $statWhere )
            {
                if ( $statWhere === 'and' )
                {
                    $whereStr .= " AND ";
                }

                if ( $statWhere === 'or' )
                {
                    $haveOR = true;
                    $whereStr .= ") OR (";
                }

                if ( is_array($statWhere) )
                {
                    $whereStr .= ($this->alias ? $this->alias . "." :"") . $statWhere['left'] . $statWhere['op'] . $statWhere['right'];
                }

            }
            $whereStr .= $haveOR ?  ")" :"";
            $BuildedQuery .= " WHERE " . ($haveOR ?  "(" :"") . $whereStr;


            //ORDER BY
            if ( $this->statement_ORDER_BY )
                $BuildedQuery .= " " . $this->statement_ORDER_BY;
            //GROUP BY
            if ( $this->statement_GROUP_BY )
                $BuildedQuery .= " " . $this->statement_GROUP_BY;
            //LIMIT
            if ( $this->statement_LIMIT )
                $BuildedQuery .= " " . $this->statement_LIMIT;
        }

        return $this->buildedQuery = $BuildedQuery;
    }

    /**
     * Выполнить запрос
     * @throws \Exception
     */
    public function exe()
    {
        $this->currentQueryIs(false);

        $this->connectDB();

        $this->build();

        switch ($this->asIs)
        {
            case "array":
                return $this->findAsArray($this->buildedQuery);
                break;
            case "single":

                $res = $this->findOne($this->buildedQuery,  $this->asOneField);
                return $res;
                break;
        }

        return false;
    }

}
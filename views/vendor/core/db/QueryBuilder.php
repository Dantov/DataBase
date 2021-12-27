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
 * Сделать:
 * 1) Назначать алисы искомым полям
 * 2) Добавить в QueryBuilder подклассы, QBWhere, QBJoin
 * 3) Функции MySql типа DATE_FORMAT(d.date, '%d.%m.%Y') as date
 * 4) Добавить метод with() - подставит даггые из др. табл в результирующий массив ( 2й запрос б бд )
 * 5) в методе asArray() - сделать шруппировку данных из присоед. таблиц Join
 *
 * Реализует общий функционал построения SQL запросов, для поиска в БД
 */
class QueryBuilder extends Model
{

    /**
     * текущий запрос Select
     * @var string
     */
    protected $statement_COUNT = false;

    protected $statement_SELECT = '';

    protected $statement_FROM = '';

    protected $statement_FIELDS = [];
    
    protected $statement_JOIN = [];

    protected $statement_WHERE = [];

    protected $statement_LIMIT = '';

    protected $statement_ORDER_BY = '';

    protected $statement_GROUP_BY = '';

    protected $buildedQuery = '';

    protected $asIs = 'array';
    protected $asOneField = '';
    protected $haveOR_AND = false;

    protected $joinedTName = '';
    protected $join_haveOR_AND = false;

    /**
     * Trusted Operators
     * @var array
     */
    protected $operators = [];

    /**
     * Tables Data
     */
    protected $tableName = '';
    protected $linkedTables = [];
    protected $fields = [];
    public $alias = '';

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

    protected function reset()
    {
        $this->statement_COUNT = '';
        $this->statement_SELECT = '';
        $this->statement_FROM = '';
        $this->statement_FIELDS = [];
        $this->statement_JOIN = [];
        $this->statement_WHERE = [];
        $this->statement_LIMIT = '';
        $this->statement_ORDER_BY = '';
        $this->statement_GROUP_BY = '';
        //$this->buildedQuery = '';
        $this->asIs = 'array';
        $this->asOneField = '';
        $this->haveOR_AND = false;
        $this->joinedTName = '';
        $this->join_haveOR_AND = false;
    }

    protected function currentQueryIs( bool $empty=false ) : bool
    {

        if ( $empty === true && !empty($this->statement_SELECT) )
            throw new \Error("Only one query statement can be in one time.", 500);

        if ( $empty === false && empty($this->statement_SELECT) )
            throw new \Error("Query statement is empty.", 500);

        return true;
    }


    /**
     * @param string $field
     *
     * список полей в которых проверять. Например из др. табл.
     * @param Table|null $table
     * @return bool
     */
    protected function checkField( string $field, Table $table = NULL ) : bool
    {
        if ( $table )
        {
            $tableFields = $table->showSchema();
            $tableName = $table->getName();
        } else {
            $tableFields = $this->fields;
            $tableName = $this->tableName;
        }

        if ( !in_array($field, $tableFields) )
            throw new \Error("Field " . $field . " not found in table " . $tableName, 500);

        return true;
    }

    /**
     * пример передаваемой функции:
     * $imgConcat = function()
        {
        $fNames = ['a'=>'img_name','b'=>'pos_id'];
        return ['fieldNames'=>$fNames, 'function'=>"CONCAT(%a%, '-', %b%)"];
        };
     * @param $function
     * @param $table
     * @return mixed
     */
    protected function callSQLFunction( $function, $table )
    {
        //debug($function,'$function');
        //debug($table->showSchema(),'$table',1);
        $result = $function();
        if ( !is_array($result) )
            throw new \Error("Result of called function must be array in " . __METHOD__, 500);

        if ( !array_key_exists('function',$result) || empty($result['function']) )
            throw new \Error("Function string need to be passed in " . __METHOD__, 500);


        $functStr = $result['function'];
        if ( array_key_exists('fieldNames',$result) )
        {
            $fieldNames = $result['fieldNames'];
            if ( is_array($fieldNames) )
            {
                foreach ( $result['fieldNames'] as $anchor => $field )
                {
                    $this->checkField($field, $table);
                    $functStr = str_ireplace("%$anchor%", $table->alias.'.'.$field, $functStr);
                }
            }
        }

        return $functStr;
    }

    /**
     * Агрегатные функции
     */
    /**
     * Бедет считеть строки
     * @param string $field
     * @param string $alias
     * @return QueryBuilder
     */
    public function count( string $alias = '', string $field='' ) : QueryBuilder
    {
        if ( $this->statement_SELECT )
            throw new \Error("You cannot do SELECT and COUNT statements at once!");

        $this->statement_SELECT = "SELECT COUNT";
        $this->statement_COUNT = true;

        if ( $alias )
            $alias = " as " . $alias;

        if ( $field )
        {
            $this->checkField($field);
            $this->statement_SELECT .= "(" . ($this->alias ? $this->alias . "." :"") . $field . ")" . $alias;
        } else {
            $this->statement_SELECT .= "(1)" . $alias;
        }

        $this->statement_FROM = 'FROM ' . $this->tableName . ($this->alias ? " as " . $this->alias :"");

        return $this;
    }

    public function select( array $fields ) : QueryBuilder
    {
        if ( $this->statement_COUNT )
            throw new \Error("You cannot do SELECT and COUNT statements at once!");

        $this->statement_SELECT = "SELECT";
        $allFields = false;

        foreach ( $fields as $alias => $field )
        {
            // все поля
            if ( $field === '*' )
            {
                $allFields = true;
                break;
            }

            $as = "";
            if ( is_string($alias) ) $as = " as " . $alias;

            // определял строку как функцию, если она соотв. назв. встроенной функ. PHP
            if ( is_callable($field) && is_object($field) )
            {
                $fieldFunct = $this->callSQLFunction($field, $this);
                $this->statement_FIELDS[] =  $fieldFunct . $as;
            } else {
                $this->checkField($field);
                $this->statement_FIELDS[] =  ($this->alias ? $this->alias . "." :"") . $field . $as;
            }
        }

        if ( $allFields )
        {
            foreach ( $this->fields as $mField )
                $this->statement_FIELDS[] =  ($this->alias ? $this->alias . "." :"") . $mField;
        }

        $this->statement_FROM = 'FROM ' . $this->tableName . ($this->alias ? " as " . $this->alias :"");

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

    public function andWhere($statementL, string $operator='', $statementR = '', string $andStatementR = null)
    {
        $this->statement_WHERE[] = ') AND (';

        $this->haveOR_AND = true;

        return $this->where($statementL,$operator,$statementR,$andStatementR);
    }

    public function orWhere($statementL, string $operator='', $statementR = '', string $andStatementR = null)
    {
        $this->statement_WHERE[] = ') OR (';

        $this->haveOR_AND = true;

        return $this->where($statementL,$operator,$statementR,$andStatementR);
    }

    public function and( $statementL, string $operator='', $statementR = '', string $andStatementR = null ) : QueryBuilder
    {
        $this->statement_WHERE[] = ' AND ';
        return $this->where($statementL,$operator,$statementR,$andStatementR);
    }

    public function or( $statementL, string $operator='', $statementR = '', string $andStatementR = null ) : QueryBuilder
    {
        $this->statement_WHERE[] = ' OR ';
        return $this->where($statementL,$operator,$statementR,$andStatementR);
    }



    /**
     * JOIN CLAUSE
     */

    /**
     * @param Table $table
     * @param $select
     * @return bool
     */
    protected function is_join_table_valid( Table $table, $select ) : bool
    {
        $joined_TName = $table->getName();
        $joined_TFields = $table->showSchema();

        if ( !$this->linkedTables[$joined_TName] )
            throw new \Error("Tables ". $joined_TName ." and ". $this->tableName ." need to be linked before by using ActiveQuery link() method " . __METHOD__, 500);

        if ( !$table->alias )
            throw new \Error("For Join operations table alias needed in " . $joined_TName, 500);

        if ( !is_array($select) )
        {
            $str = $select;
            $select = [];
            $select[] = $str;
        }

        foreach ( $select as $fieldName )
        {
            if ( $fieldName === '*' )
                break;

            if ( is_callable( $fieldName ) )
                continue;

            if ( !in_array( $fieldName, $joined_TFields ) )
                throw new \Error("Field " . $fieldName . " not found in table " . $joined_TName, 500);
        }

        return true;
    }

    protected function is_on_operator_valid(string $onOperator) : bool
    {
        $operator = $onOperator;
        $operator = mb_strtoupper($operator);
        if ( !in_array($operator, $this->operators) )
            throw new \Error("Wrong operator in " . __METHOD__, 500);

        return true;
    }

    /**
     * @param Table $table
     * @param array $select
     *
     * Условный оператор по которому соединить столбцы связанных таблиц, указанных в методе ActiveQuery -> Link()
     * @return QueryBuilder
     */
    public function join( Table $table, array $select ) : QueryBuilder //, string $onOperator = '='
    {
        $this->is_join_table_valid($table, $select);

        $joined_TName = $table->getName();
        $onOperator = $this->linkedTables[$joined_TName]['operator'];

        $this->is_on_operator_valid($onOperator);

        /** Условие выполнится когда стартует новый join() */
        /** Сверки имен таблиц, если они не совпадают и открыты скобки с andON()/orON(), закроем их в рамках текущей Join табл */
        if ( $this->joinedTName !== $joined_TName )
            if ( $this->join_haveOR_AND )
            {
                $this->statement_JOIN[$this->joinedTName][] = ')';
                $this->join_haveOR_AND = false;
            }

        /** теперь условия Join будет писать в новую табл */
        $this->joinedTName = $joined_TName;

        $allFields = false;
        foreach ( $select as $fAlias => $fieldName )
        {
            // все поля
            if ( $fieldName === '*' )
            {
                $allFields = true;
                break;
            }

            $as = "";
            if ( is_string($fAlias) ) $as = " as " . $fAlias;

            if (is_callable($fieldName))
            {
                $fieldFunct = $this->callSQLFunction($fieldName, $table);
                $this->statement_FIELDS[] =  $fieldFunct . $as;
            } else {
                $this->statement_FIELDS[] = $table->alias . "." . $fieldName . $as;
            }

        }

        if ( $allFields )
        {
            foreach ( $table->showSchema() as $mField )
                $this->statement_FIELDS[] =  ($table->alias ? $table->alias . "." :"") . $mField;
        }


        $joined_self_Field = array_key_first($this->linkedTables[$joined_TName]);
        $joined_t_Field = $this->linkedTables[$joined_TName][$joined_self_Field];

        $leftField = $this->alias .".". $joined_self_Field;
        $rightField = $table->alias .".". $joined_t_Field;

        $operator = mb_strtoupper($onOperator);
        $this->statement_JOIN[$joined_TName][] = " LEFT JOIN " . $joined_TName . " as " . $table->alias;
        $this->statement_JOIN[$joined_TName][] = "ON";
        $this->statement_JOIN[$joined_TName][] = $leftField . $operator .  $rightField;

        return $this;
    }

    /**
     * Просто добавит AND в условие к текущему статементу без скобок
     * @param Table $table
     * @param string $field
     * @param string $onOperator
     * @param $value
     * @return $this
     */
    public function joinAnd( Table $table, string $field, string $onOperator, $value )
    {
        $this->is_join_table_valid($table, $field);
        $this->is_on_operator_valid($onOperator);

        $operator = mb_strtoupper($onOperator);


        $this->statement_JOIN[$table->getName()][] = " AND " . $table->alias .".". $field . $operator . "'".$value."'";

        return $this;
    }

    /**
     * Просто добавит OR в условие к текущему статементу без скобок
     * @param Table $table
     * @param string $field
     * @param string $onOperator
     * @param $value
     * @return $this
     */
    public function joinOr( Table $table, string $field, string $onOperator, $value )
    {
        $this->is_join_table_valid($table, $field);
        $this->is_on_operator_valid($onOperator);

        $operator = mb_strtoupper($onOperator);
        $this->statement_JOIN[$table->getName()][] = " OR " . $table->alias .".". $field . $operator . "'".$value."'";

        return $this;
    }

    /**
     * Оборачивает с скобки текущие условия и следующие вызовы  joinAnd() и joinOr()
     * @param Table $table
     * @param string $field
     * @param string $onOperator
     * @param $value
     * @return $this
     */
    public function andON( Table $table, string $field, string $onOperator, $value )
    {
        $this->is_join_table_valid($table, $field);
        $this->is_on_operator_valid($onOperator);

        $this->join_haveOR_AND = true;

        $arrayJoinTable = &$this->statement_JOIN[$table->getName()];
        /** Откроет скобку для первого условия ON */
        foreach ( $arrayJoinTable as &$statement )
        {
            if ( $statement === 'ON' )
                $statement = " " . $statement . ' (';
        }

        $operator = mb_strtoupper($onOperator);
        $this->statement_JOIN[$table->getName()][] = ") AND (" . $table->alias .".". $field . $operator . "'".$value."'";

        return $this;
    }


    /**
     * Оборачивает с скобки текущие условия и следующие вызовы  joinAnd() и joinOr()
     * @param Table $table
     * @param string $field
     * @param string $onOperator
     * @param $value
     * @return $this
     */
    public function orON( Table $table, string $field, string $onOperator, $value )
    {
        $this->is_join_table_valid($table, $field);
        $this->is_on_operator_valid($onOperator);

        $this->join_haveOR_AND = true;

        $arrayJoinTable = &$this->statement_JOIN[$table->getName()];
        /** Откроет скобку для первого условия ON */
        foreach ( $arrayJoinTable as &$statement )
        {
            if ( $statement === 'ON' )
                $statement = " " . $statement . ' (';
        }

        $operator = mb_strtoupper($onOperator);
        $this->statement_JOIN[$table->getName()][] = ") OR (" . $table->alias .".". $field . $operator . "'".$value."'";

        return $this;
    }



    /**
     * REST
     */

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

        $this->statement_ORDER_BY = "ORDER BY " . $this->alias . '.' . $field . " " . $direct;

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
        /*
        if ( $field && !in_array($field, $this->fields) )
            throw new \Error("Field '" . $field . "' not found in table " . $this->tableName, 500);
        */

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

        /**  FIELDS  */
        $statement_FIELDS = '';
        foreach ( $this->statement_FIELDS as $statementField )
            $statement_FIELDS .= $statementField . ", ";
        $statement_FIELDS = trim($statement_FIELDS,', ');
        $BuildedQuery .= $statement_FIELDS ." " . $this->statement_FROM;


        /**  JOIN CLAUSE */
        if ( !empty($this->statement_JOIN) )
        {
            //debug($this->statement_JOIN,"join");

            $joinStr = '';
            foreach ( $this->statement_JOIN as $tableName => $statementsJoin )
            {
                foreach ( $statementsJoin as $statementJoin )
                {
                    if ( $statementJoin === 'ON' )
                    {
                        // ни разу не были вызваны orON() / andON()
                        $joinStr .= " " . $statementJoin . " ";
                        continue;
                    }
                    $joinStr .= $statementJoin;
                }
            }

            // ставит в конец ) если были вызваны orON() / andON()
            $joinStr .= $this->join_haveOR_AND ? ")" : "";

            $BuildedQuery .= $joinStr;
            //debug($joinStr,"BuildedQuery",1);
        }


        /** WHERE CLAUSE */
        if ( !empty($this->statement_WHERE) )
        {
            $whereStr = '';
            foreach ( $this->statement_WHERE as $statWhere )
            {
                if ( is_array($statWhere) )
                {
                    $whereStr .= ($this->alias ? $this->alias . "." :"") . $statWhere['left'] . $statWhere['op'] . $statWhere['right'];
                } else {
                    $whereStr .= $statWhere;
                }

            }
            $BuildedQuery .= ' WHERE '.($this->haveOR_AND ?  "(" :"") . $whereStr . ($this->haveOR_AND ?  ")" :"");
        }


        //ORDER BY
        if ( $this->statement_ORDER_BY )
            $BuildedQuery .= " " . $this->statement_ORDER_BY;
        //GROUP BY
        if ( $this->statement_GROUP_BY )
            $BuildedQuery .= " " . $this->statement_GROUP_BY;
        //LIMIT
        if ( $this->statement_LIMIT )
            $BuildedQuery .= " " . $this->statement_LIMIT;


        $this->reset();

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

        switch ($this->asIs)
        {
            case "array":
                $res = $this->findAsArray( $this->build() );
                break;

            case "single":
                $asOneField = $this->asOneField; // $this->build() - стирает поле $this->asOneField!!
                $res = $this->findOne( $this->build(),  $asOneField);
                break;

            case "aggregateFuncts":

                break;

            default :
                $res = $this->baseSql( $this->build() );
                break;
        }

        return $res;
    }

    protected function aggregateFuncts()
    {

    }

}
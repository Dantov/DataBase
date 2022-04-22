<?php
namespace Views\vendor\core\db;
use Views\vendor\libs\classes\AppCodes;


/**
 * Базовый класс БД
 * соединяет, разъединяет, проверяет подключение.
 * @package Views\vendor\core\db
 */
class Database
{
    //private static $mysqli;
    protected $connection = null;
    public $count = 0;

    protected static $instance;
    protected static $dbConfig;

    private static $host;
    private static $dbname;
    private static $username;
    private static $password;
    private static $charset;

    /**
     * @param $db
     */
    protected static function getDBConfig( array $db )
    {

        if ( isset($db['host']) )     self::$host     = $db['host'];
        if ( isset($db['dbname']) )   self::$dbname   = $db['dbname'];
        if ( isset($db['username']) ) self::$username = $db['username'];
        if ( isset($db['password']) ) self::$password = $db['password'];
        if ( isset($db['charset']) )  self::$charset  = $db['charset'];
    }

    /**
     * Database constructor.
     * @param array $dbConfig
     * @throws \Exception
     */
    protected function __construct( array $dbConfig )
    {
        self::getDBConfig($dbConfig);
        $this->connect();
//        $d = new \DateTime();
//        debug(__CLASS__ . " created in " . $d->format("d.m.Y - H:i:s u") );
    }

    public function __destruct()
    {
//        $d = new \DateTime();
//        debug(__CLASS__ . " destroyed in " . $d->format("d.m.Y - H:i:s u") );
    }

    /**
     * @param array $dbConfig
     * @return Database
     * @throws \Exception
     */
    public static function instance( array $dbConfig = [] ) : Database
    {
        if ( !is_object(self::$instance) || !(self::$instance instanceof Database) )
        {
            if ( empty($dbConfig) ) {
                if ( _DEV_MODE_ )
                    throw new \Exception(AppCodes::getMessage(AppCodes::DB_CONFIG_EMPTY)['message'],AppCodes::DB_CONFIG_EMPTY);
                if ( !_DEV_MODE_ )
                    throw new \Exception("Ошибка на сервере!",AppCodes::DB_CONFIG_EMPTY);
            }
            self::$instance = new self($dbConfig);
            return self::$instance;
        }

        return self::$instance;
    }

    /**
     * попытка подключения к БД
     */
    protected function connect()
    {
        try {
            $this->connection = @mysqli_connect(self::$host, self::$username, self::$password, self::$dbname);
        } catch (\Error | \Exception $e )
        {
            if ( mysqli_connect_errno() )
            {
                $errno = mysqli_connect_errno();
                $errtext = mysqli_connect_error();
                if ( !_DEV_MODE_ )
                    $errtext = "Ошибка при подключении к базе данных на сервере.";

                header("location: " . _views_HTTP_ . "errors/errMysqlConn.php?errno=$errno&errtext=$errtext");
                exit;
            }
        }
        mysqli_set_charset($this->connection, self::$charset);

        //$this->connection = $connection;
        /*
        self::$mysqli = new \mysqli(self::$host, self::$username, self::$password, self::$dbname);
        // проверка соединения
        if (self::$mysqli->connect_errno)
        {
            return [
                'message' => "Не удалось подключиться!",
                'error' => self::$mysqli->error,
                'errno' => self::$mysqli->errno,
            ];
        }
        if (!self::$mysqli->set_charset(self::$charset)) {
            return [
                'message' => "Ошибка при загрузке набора символов!",
                'error' => self::$mysqli->error,
                'errno' => self::$mysqli->errno,
            ];
        }

        return self::$mysqli;
        */
    }

    public function getConnection()
    {
        if ( $this->isConnected() )
            return $this->connection;
        $this->connect();
        return $this->connection;
    }

    /**
     * проверяем есть ли соединение с БД
     * @return bool
     */
    public function isConnected() : bool
    {
        //if ( !is_object($this->connection) && $this->connection instanceof \mysqli  ) return false;
        if ( !($this->connection instanceof \mysqli)  )
        {
            return false;
        }

        if ( \mysqli_ping($this->connection) )
        {
            return true;
        } else {
            return false;
        }
        /*
        if (self::$mysqli->ping())
        {
            return true;
        } else {
           return false;
        }
        */
    }

    /**
     * @return bool
     */
    public function close()
    {
        if ( !self::isConnected() ) return false;
        //debug(debug_backtrace(),'1',1);
        if ( mysqli_close($this->connection) ) {
            $this->connection = null;
            return true;
        } else {
            return false;
        }
        /*
        if ( self::$mysqli->close() ) {
            self::$mysqli = null;
            return true;
        } else {
            return false;
        }
        */
    }

    public function destroy()
    {
        if ( is_object(self::$instance) && ( self::$instance instanceof Database ) )
        {
            $this->close();

            self::$instance = null;
            return true;
        }
        return false;
    }

}
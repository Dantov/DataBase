<?php
namespace Views\vendor\core\db;
use Views\vendor\libs\classes\AppCodes;
use Views\vendor\core\Errors\exceptions\DBConnectException;
use Views\vendor\core\Config;


/**
 * Базовый класс БД
 * соединяет, разъединяет, проверяет подключение.
 * @package Views\vendor\core\db
 */
class Database implements DatabaseInterface
{
    protected \PDO $pdo;
    public static int $count = 0;
    public static int $overallQuerys = 0;

    protected static $instance;
    protected static array $dbConfig;

    private static string $driver;
    private static string $host;
    private static string $dbname;
    private static string $username;
    private static string $password;
    private static string $charset;
    private static string $connStr;

    /**
     * @param $db
     */
    public static function setConfig( array $db ) : void
    {
        if (isset($db['driver'])) {
            self::$driver = $db['driver'];
        } else {
            throw new DBConnectException(AppCodes::DB_CONFIG_DRIVER_ERROR);
        }
        if (trueIsset($db['host'])) {
            self::$host = $db['host'];
        } else {
            throw new DBConnectException(AppCodes::DB_CONFIG_HOST_ERROR);
        }
        if (isset($db['dbname'])) {
            self::$dbname = $db['dbname'];
        } else {
            throw new DBConnectException(AppCodes::DB_CONFIG_DBNAME_ERROR);
        }
        if (isset($db['username'])) {
            self::$username = $db['username'];
        } else {
            throw new DBConnectException(AppCodes::DB_CONFIG_USERNAME_ERROR);
        }
        if (isset($db['password'])) {
            self::$password = $db['password'];
        } else {
            throw new DBConnectException( AppCodes::DB_CONFIG_PASSWORD_ERROR);
        }
        if (isset($db['charset'])) {
            self::$charset = $db['charset'];
        } else {
            throw new DBConnectException(AppCodes::DB_CONFIG_CHARSET_ERROR);
        }

        //'mysql:host=localhost;dbname=test;charset=utf8mb4'
        self::$connStr = self::$driver.':host=' . self::$host . ';dbname=' . self::$dbname . ';charset=' . self::$charset;
    }

    /**
     * Database constructor.
     * @param array $dbConfig
     * @throws \Exception
     */
    protected function __construct( array $dbConfig )
    {
        self::setConfig($dbConfig);
        
        $this->connect();
        $d = new \DateTime();
//        debug(__CLASS__ . " created in " . $d->format("d.m.Y - H:i:s u") );
        self::$count++;
    }

    public function __destruct()
    {
        $d = new \DateTime();
//        debug(__CLASS__ . " destroyed in " . $d->format("d.m.Y - H:i:s u") );
    }

    /**
     * @param array $dbConfig
     * @return Database
     * @throws \Exception
     */
    public static function init( array $dbConfig = [] ) : Database
    {
        if ( !isset(self::$instance) || !(self::$instance instanceof Database) )
        {
            if ( empty($dbConfig) ) {
                if ( !trueIsset( $dbConfig = Config::get('db') ) )
                    throw new DBConnectException(AppCodes::DB_CONFIG_EMPTY);  
            }
            
            self::$instance = new self($dbConfig);
            return self::$instance;
        }

        return self::$instance;
    }

    /**
     * попытка подключения к БД
     */
    protected function connect() : void
    {
        /*
        debug(self::$dbConfig);
        debug(self::$host);
        debug(self::$username);
        debug(self::$password);
        debug(self::$dbname);
        exit;
        */
        
        try {
            //$this->pdo = @mysqli_connect(self::$host, self::$username, self::$password, self::$dbname);
            $this->pdo = new \PDO(self::$connStr, self::$username, self::$password);
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
        } catch (\Error | \Exception | \PDOException $e ) {
            $errtext = $e->getMessage();
            $errno = $e->getCode();
            header("location: " . _views_HTTP_ . "errors/errMysqlConn.php?errno=$errno&errtext=$errtext");
            exit;
        }
    }

    public function getConnection() : \PDO
    {
        if ($this->isConnected()) {
            return $this->pdo;
        }
        $this->connect();
        return $this->pdo;
    }

    public function ping( bool $reconnect=false ) : bool 
    {    
        if ( !($this->pdo instanceof \PDO)  ) {
            return false;
        }
        
        try {
            $this->pdo->query('SELECT 1');
        } catch ( \PDOException ) {
            if ( $reconnect )
            {
                $this->connect();            // Don't catch exception here, so that re-connect fail will throw exception
                return $this->ping();
            }
            
            return false;
        }
        return true;
    }
    
    /**
     * проверяем есть ли соединение с БД
     * @return bool
     */
    public function isConnected() : bool
    {
        return $this->ping();
    }

    /**
     * @return bool
     */
    public function close() : bool
    {
        if (!$this->isConnected()) {
            return false;
        }
        //debug(debug_backtrace(),'1',1);
        
        $this->pdo = null;
        return $this->isConnected();
    }

    public function destroy() : bool
    {
        if ( self::$instance instanceof Database )
        {
            $this->close();

            self::$instance = null;
            return true;
        }
        return false;
    }

}
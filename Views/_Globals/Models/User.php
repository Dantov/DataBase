<?php
namespace Views\_Globals\Models;

use Views\vendor\core\Registry;

class User
{
    /**
     * true если юзер не залогинился
     * @var bool
     */
    protected static bool $isGuest;

    protected static $userInstance;

    /**
     * ID юзера из таблицы
     * @var integer
     */
    protected static string $userSurname;
    protected static int $userID;
    protected static string $userFIO;
    protected static string $userFullFIO;

    /**
     * ID участков к которым принадлежит пользователь
     * @var array
     */
    protected static array $userLocations;

    /**
     * уровень доступа
     * @var integer
     */
    protected static int $userAccess;

    /**
     * Список разрешений для конкретного пользователя
     * @var
     */
    protected static array $permissions = [];

    /**
     * экземпляр General для доступа к не статик методам
     * @var $instance
     */
    protected static $instance;

    /**
     * @throws \Exception
     */
    protected static function instance() : object
    {        
        if ( !isset(self::$instance) || !(self::$instance instanceof Model) )
        {
            //self::$instance = new General();
            /*
            self::$instance = new Model();
            self::$instance->connectDB();
            return self::$instance;
             * 
             */
        }
        return self::$instance;
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected static function userInstance() : array
    {
        if ( isset(self::$userInstance) && is_array(self::$userInstance) ) 
            return self::$userInstance;

        return self::init();
    }

    /**
     * PUBLIC METHODS
     */
    
    
    public static function init( int $userID=null, array $user=[] ) : array
    {
        if ( isset(self::$userInstance) && is_array(self::$userInstance) ) 
            return self::$userInstance;
        
        if ( $user ) {
            return self::$userInstance = $user;
        }
        if ( $userID < 0 || $userID > PHP_INT_MAX )
            throw new \Exception("We got no user sorry!", 510);
        
        if ( !$userID )
        {
            // Try to get user by id from Session
            $reg = Registry::init();
            $user = $reg->sessions->getKey('user');
            if ( !isset($user['id']) )
                throw new \Exception("We got no user sorry!", 511);
            
            $userID = $user['id'];
        }
        
        // Query new user by id from DB
        $aq = new \Views\vendor\core\ActiveQuery(['users']);
        self::$userInstance = $aq->users->select(['id','fio','fullFio','location','access'])->where(['id','=',$userID])->asOne()->exe();
        
        return self::$userInstance;
    }


    /**
     * @return array
     * @throws \Exception
     */
    public static function permissions() : array
    {
        if ( trueIsset(self::$permissions) ) return self::$permissions;

        $aq = new \Views\vendor\core\ActiveQuery(['permissions','user_permissions']);
        //$permissions = self::instance()->findAsArray("SELECT id,name,description FROM permissions");
        $permissions = $aq->permissions->select(['id','name','description'])->exe();
        
        //$userPermissions = self::instance()->findAsArray("SELECT permission_id FROM user_permissions WHERE user_id='$userID' ");
        $userPermissions = $aq->user_permissions->select(['permission_id'])->where(['user_id','=',self::getID()])->exe();
        
        foreach ( $userPermissions as $key => &$userPF ) 
            $userPermissions[$key] = $userPF['permission_id'];

        $permittedFields = [];
        foreach ( $permissions as $permittedField )
        {
            $pfID = $permittedField['id'];
            if ( in_array( $pfID, $userPermissions ) )
            {
                $permittedFields[$permittedField['name']] = true;
            } else {
                $permittedFields[$permittedField['name']] = false;
            }
        }

        return self::$permissions = $permittedFields;
    }

    /**
     * @param string $permission
     * @return bool
     * @throws \Exception
     */
    public static function permission( string $permission = '') : bool
    {
        if ( array_key_exists($permission, self::permissions()) ) return self::$permissions[$permission];
        return false;
    }

    /**
     * @throws \Exception
     */
    public static function isGuest() : bool
    {
        if ( isset(self::$isGuest) ) return self::$isGuest;
        return self::$isGuest = !(int)self::userInstance()['access'] ? true : false;
    }

    /**
     * @return int
     * @throws \Exception
     */
    public static function getID() : int
    {
        if ( isset( self::$userID ) ) return self::$userID;
        return self::$userID = (int) ( (self::userInstance()['id'])??0 );
    }

    /**
     * @return string
     * @throws \Exception
     */
    public static function getSurname() : string
    {
        if ( isset( self::$userSurname ) ) return self::$userSurname;

        self::$userSurname = explode(' ', self::getFIO())[0];
        return self::$userSurname;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public static function getFIO() : string
    {
        if ( isset( self::$userFIO ) ) return self::$userFIO;
        return self::$userFIO = self::userInstance()['fio'];
    }

    /**
     * @return string
     * @throws \Exception
     */
    public static function getFullFIO() : string
    {
        if ( isset( self::$userFullFIO ) ) return self::$userFullFIO;
        return self::$userFullFIO = self::userInstance()['fullFio'];
    }

    /**
     * @return array
     * @throws \Exception
     */
    public static function getLocations() : array
    {
        if ( isset( self::$userLocations ) ) return self::$userLocations;
        $user = self::userInstance();

        return self::$userLocations = explode(',',$user['location']);
    }

    /**
     * @return int
     * @throws \Exception
     */
    public static function getAccess() : int
    {
        if ( isset( self::$userAccess ) ) 
            return self::$userAccess;
                
        return self::$userAccess = (int)self::userInstance()['access'];
    }

    /**
     * @return string
     */
    public static function getIp() : string
    {
        if ( filter_has_var(INPUT_SERVER, 'REMOTE_ADDR') )
        {
            return filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
        }
        return '';
    }

}
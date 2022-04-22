<?php
namespace Views\_Globals\Models;

class User
{

    /**
     * true если юзер не залогинился
     * @var bool
     */
    protected static $isGuest;

    protected static $userInstance;

    /**
     * ID юзера из таблицы
     * @var integer
     */
    protected static $userSurname;
    protected static $userID;
    protected static $userFIO;
    protected static $userFullFIO;

    /**
     * ID участков к которым принадлежит пользователь
     * @var array
     */
    protected static $userLocations;

    /**
     * уровень доступа
     * @var integer
     */
    protected static $userAccess;

    /**
     * Список разрешений для конкретного пользователя
     * @var
     */
    protected static $permissions;

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
        if ( !is_object(self::$instance) )
        {
            self::$instance = new General();
            self::$instance->connectDBLite();
            return self::$instance;
        }
        return self::$instance;
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected static function userInstance() : array
    {
        if ( is_array(self::$userInstance) ) return self::$userInstance;

        if ( !is_object(self::$instance) ) self::instance();

        if ( method_exists(self::$instance,'getUser') )
        {
            return self::$userInstance = self::$instance->getUser();
        }


        throw new \Exception(__METHOD__ . "can't get any user!");
    }






    /**
     * PUBLIC METHODS
     */

    /**
     * @return array
     * @throws \Exception
     */
    public static function permissions() : array
    {
        if ( trueIsset(self::$permissions) ) return self::$permissions;

        $permissions = self::instance()->findAsArray("SELECT id,name,description FROM permissions");
        $userID = self::getID();
        $userPermissions = self::instance()->findAsArray("SELECT permission_id FROM user_permissions WHERE user_id='$userID' ");
        foreach ( $userPermissions as $key => &$userPF ) $userPermissions[$key] = $userPF['permission_id'];

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
    public static function isGuest()
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
        if ( trueIsset( self::$userID ) ) return self::$userID;
        return self::$userID = (int)self::userInstance()['id'];
    }

    /**
     * @return string
     * @throws \Exception
     */
    public static function getSurname() : string
    {
        if ( trueIsset( self::$userSurname ) ) return self::$userSurname;

        self::$userSurname = explode(' ', self::getFIO())[0];
        return self::$userSurname;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public static function getFIO() : string
    {
        if ( trueIsset( self::$userFIO ) ) return self::$userFIO;
        return self::$userFIO = self::userInstance()['fio'];
    }

    /**
     * @return string
     * @throws \Exception
     */
    public static function getFullFIO() : string
    {
        if ( trueIsset( self::$userFullFIO ) ) return self::$userFullFIO;
        return self::$userFullFIO = self::userInstance()['fullFio'];
    }

    /**
     * @return array
     * @throws \Exception
     */
    public static function getLocations()
    {
        if ( trueIsset( self::$userLocations ) ) return self::$userLocations;
        $user = self::userInstance();

        return self::$userLocations = explode(',',$user['location']);
    }

    /**
     * @return int
     * @throws \Exception
     */
    public static function getAccess() : int
    {
        if ( trueIsset( self::$userAccess ) ) return self::$userAccess;
        return self::$userAccess = (int)self::userInstance()['access'];
    }

    /**
     * @return string
     */
    public static function getIp() : string
    {
        return $_SERVER['REMOTE_ADDR'];
    }

}
<?php
namespace soffit;


/**
 * Реестр
 * Содержит в себе список объектов
 */
class Registry implements \Stringable
{
    private static $dateCreate = null;
    private static $calledInClass = null;
    private static $instance = null;
    private static $Objects = [];

    // Пути, где находятся классы
    private static $ObjectsPath = [];

    /**
     * Registry constructor.
     * @throws \Exception
     */
    protected function __construct()
    {
        $classes = Config::get('classes');
        foreach ( $classes as $class => $path )
        {
            self::$ObjectsPath[$class] = $path;
            self::$Objects[$class] = new $path;
        }
    }

    /**
     * @return bool|Registry
     * @throws \Exception
     */
    public static function init() : Registry
    {
        if ( is_object(self::$instance) && (self::$instance instanceof self) )
        {
            return self::$instance;
        } else {
            self::$instance = new self;
            self::$dateCreate = date("Y-m-d");
            // записать переменые даты создания, где вызван 
            // self::$calledInClass; $dateCreate
            return self::$instance;
        }
    }

    public function __toString() : string
    {
        $str = "";
        foreach ( self::$ObjectsPath as $class => $path )
        {
            $str .= $class . " - ". $path .';'. PHP_EOL;
        }
        return $str;
    }

    public function __set(string $name, string $path)
    {
        if ( isset(self::$Objects[$name]) && is_object(self::$Objects[$name]) )
            throw \Exception('Object '. $name .' allready exist on this path - ' . $path);

        self::$ObjectsPath[$name] = $path;
        self::$Objects[$name] = new $path;
    }

    public function __get( string $name ) : object
    {
        return self::get($name);
        /*
        if ( isset(self::$Objects[$name]) && is_object(self::$Objects[$name]) )
        {
            return self::$Objects[$name];
        } else {
            throw new \Exception('No object - '. $name .' - found in Regestry!', 444);
        }
        */
    }

    public function showAll() : string
    {
        return self::$ObjectsPath;
    }

    /**
     * Создать новый объект от пути, и добавить в реестр
     * @param $name
     * @param $path
     */
    public function setObj(string $name, string $path) : object
    {
        if ( !file_exists($path) ) {
            throw \Exception('No object '. $name .' located in this path - ' . $path);
        }
        
        self::$ObjectsPath[$name] = $path;
        return self::$Objects[$name] = new $path;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function getObj($name) : object
    {
        if ( isset(self::$Objects[$name]) && is_object(self::$Objects[$name]) )
            return self::$Objects[$name];
    }

    /**
     * Добавить готовый объект в реестр
     * @param string $name
     * @param object $obj
     */
    public function addObj(string $name, object $obj)
    {
        self::$ObjectsPath[$name] = '/';
        self::$Objects[$name] = $obj;
    }

    public static function get( string $name='') : object
    {
        if ( isset(self::$Objects[$name]) && is_object(self::$Objects[$name]) )
        {
            return self::$Objects[$name];
        } else {
            throw new \Exception('No object - '. $name .' - found in Regestry!', 444);
        }
    }

}
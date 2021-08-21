<?php
namespace Views\vendor\core;


/**
 * Реестр
 * Содержит в себе список объектов
 */
class Registry
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
    public static function init()
    {
        if ( is_object(self::$instance) )
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

    public function __toString()
    {
        $str = "";
        foreach ( self::$calledInClass as $class => $path )
        {
            $str .= $class . "; ";
        }
        return $str;
    }

    public function __set(string $name, string $path) : bool
    {
        if ( isset(self::$Objects[$name]) && is_object(self::$Objects[$name]) )
            return false;

        self::$Objects[$name] = new $path;
        return true;
    }

    public function __get($name)
    {
        if ( isset(self::$Objects[$name]) && is_object(self::$Objects[$name]) )
            return self::$Objects[$name];

        return null;
    }

    public function showAll()
    {
        return self::$ObjectsPath;
    }

    /**
     * Создать новый объект от пути, и добавить в реестр
     * @param $name
     * @param $path
     * @return mixed
     */
    public function setObj(string $name, string $path)
    {
        if ( !empty($name) && !empty($path) )
            return self::$Objects[$name] = new $path;

        return null;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function getObj($name) : object
    {
        if ( isset(self::$Objects[$name]) && is_object(self::$Objects[$name]) )
            return self::$Objects[$name];

        return null;
    }

    /**
     * Добавить готовый объект в реестр
     * @param $name
     * @param $obj
     */
    public function addObj($name, $obj)
    {
        if ( is_object($obj) && is_string($name) ) self::$Objects[$name] = $obj;
    }

}
<?php

namespace Views\vendor\core;


class Autoloader
{

    protected static $aliases = [];

    public function __construct($aliases=[])
    {
        if ( is_array($aliases) )
        {
            foreach ( $aliases as $alias => $path )
            {
                static::$aliases[$alias] = $path;
            }
        }
        spl_autoload_register([$this,'autoload']);
    }

    /**
     * @param $class
     * @throws \Exception
     */
    protected function autoload($class)
    {
        //заменяет обратный слешь на прямой для unix систем
        $class = str_replace('\\','/', $class);

        $e = explode('/',$class);
        $alias = $e[0];
        //$restPath = $e[1];

        if ( array_key_exists($alias,static::$aliases) )
        {
            $class = str_replace($alias, static::$aliases[$alias], $class);
            //$class = static::$aliases[$alias] .'/'. $restPath;
        }

        $class = _rootDIR_ . $class . '.php';

        //debug($class,'$class');

        if ( !file_exists($class) ) throw new \Exception( "Autoloader Exception: class <i>" . $class . "</i> not found!", 777 );

        require_once $class;
    }

    public static function setAlias($alias, $path)
    {
        if ( is_string($alias) && is_string($path) )
        {
            self::$aliases[$alias] = $path;
            return true;
        }
        return false;
    }

    public static function getAlias($alias)
    {
        if ( array_key_exists(self::$aliases,$alias) )
        {
            return self::$aliases[$alias];
        }
        return false;
    }


}
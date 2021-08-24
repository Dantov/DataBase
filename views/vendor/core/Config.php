<?php
/**
 * Date: 30.06.2020
 * Time: 23:13
 */

namespace Views\vendor\core;


class Config
{

    protected static $config = [];

    protected static $defaultConfig = [];
    protected static $instance = false;

    /**
     * @param $config
     * @throws \Exception
     */
    public static function initConfig( $config )
    {
        if ( self::$instance ) return;
        if ( !is_array($config) || empty($config) ) throw new \Exception("Can't find config array! in " . __METHOD__ );

        self::$config = $config;

        /*
         * здесь нужно раскидать масив конфига по статик свойствам
         * если не хватает каких-то свойств, взять их из дефолтного конфига
         * */

        self::$instance = true;
    }

    /**
     * @param string $key
     * @return array|mixed
     * @throws \Exception
     */
    public static function get( string $key='')
    {
        if ( !empty($key) )
        {
            if ( array_key_exists($key,self::$config) )
            {
                return self::$config[$key];
            } elseif ( array_key_exists($key,self::$defaultConfig) ) {
                return self::$defaultConfig[$key];
            } else {
                throw new \Exception("Can't find '" . $key ."' in config array!");
            }
        }

        return self::$config ?? self::$defaultConfig;
    }

    /**
     * @param string $key
     * @param $value
     * @return bool
     */
    public static function set(string $key, $value )
    {
        if ( empty($key) || empty($value) ) return false;

        self::$config[$key] = $value;
        return true;
    }

}
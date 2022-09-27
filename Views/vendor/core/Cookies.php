<?php

namespace Views\vendor\core;


class Cookies
{

    /*
     * активные куки здесь
     */
    protected static array $cookies = [];

    public static function set(string $name, string $value, int $time=null, string $path=null, string $domain=null) : bool
    {
        if (!$time) {
            $time = time() + 3600; // час
        }
        filter_input(INPUT_SERVER,'HTTP_HOST');
        if ( setcookie($name, $value, $time, $path?:'/', $domain?:filter_input(INPUT_SERVER,'HTTP_HOST') ) )
        {
            self::$cookies[$name] = $value;
            return true;
        }
        return false;
    }


    public static function dellAllCookies( $cookies=false, $cookieArrName=false )
    {
        $result = [];
        $cookiesArr = $cookies ?: self::getAll();
        
        foreach( $cookiesArr as $cookie => $value )
        {
            if (is_array($value) ) self::dellAllCookies($value, $cookie);

            $cookieName = $cookie;
            if ( is_string($cookieArrName) ) $cookieName = $cookieArrName . "[$cookie]";

            if ( setcookie($cookieName, '', 1, '/', $_SERVER['HTTP_HOST']) )
            {
                $result[] = true;
            } else {
                $result[] = false;
            }

        }
        self::$cookies = [];
        foreach($result as $res)
        {
            if ( $res === false ) return false;
        }

        return true;
    }

    /**
     * @param string $cookieName // user[id]
     * @return bool
     */
    public static function dellOne($cookieName)
    {   
        if ( is_string($cookieName) )
        {
            if ( setcookie($cookieName, '', 1, '/', $_SERVER['HTTP_HOST']) ) return true;
            return false;
        }
        return false;
    }

    public static function getAll()
    {
        if ( !empty($_COOKIE) ) return $_COOKIE;
        return [];
    }

    public static function getOne($name)
    {
        if ( isset($_COOKIE[$name]) ) return $_COOKIE[$name];
        return false;
    }

}
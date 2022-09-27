<?php

namespace Views\vendor\core;


class Router
{
    /**
     * Текущий маршрут
     * @var string
     */
    protected static $rout = '';

    /**
     * @array $params
     * разобранный массив параметров, пришедших из строки запроса
     */
    protected static $params=[];

    /**
     * @string $controllerName
     * Имя текущего контроллера
     *
     */
    protected static $controllerName = '';

    /**
     * оригинальное имя контроллера из url, нужен для сооздания URL
     */
    protected static $controllerNameOrigin = '';


    /**
     * @param $uri
     * @throws \Exception
     */
    public static function parseRout($uri)
    {
        if ( empty($uri) ) throw new \Exception("REQUEST_URI is empty",444);
        $routs = explode('?', trim($uri,'/') );
        //debug($routs,'$routs');

        self::$controllerNameOrigin = $controller = explode('/', trim($routs[0],'/') )[0];
        //debug($controller,'$controller');
        if ( !empty($controller) ) {
            self::$controllerName = str_ireplace(' ','', ucwords(str_ireplace('-',' ', $controller)) );
        } else {
            self::$controllerName = 'Main';
        }
        //debug(self::$controllerName,'self::$controllerName');
        // _ добавляем для имени папки
        self::$rout = "Views\\" . "_" . self::$controllerName . "\Controllers\\" .  self::$controllerName . "Controller";
        //debug(self::$rout,'self::$rout',1);

        if ( isset( $routs[1] ) ) self::parseParams($routs[1]);
    }


    /**
     * @param $paramsStr
     */
    protected static function parseParams($paramsStr)
    {
        if ( empty($paramsStr) ) return;
        //debug($paramsStr,'$paramsStr');
        $params = explode('&',$paramsStr);
        //debug($params,'$params');
        foreach ( $params as $param )
        {
            $pArr = explode('=',$param);
            if ( !empty($pArr[0]) ) self::$params[$pArr[0]] = isset($pArr[1])?$pArr[1]:null;
        }
        //debug(self::$params,'self::$params',1);
    }


    public static function getRout()
    {
        return self::$rout;
    }
    public static function getParams()
    {
        return self::$params;
    }
    public static function getControllerName()
    {
        return self::$controllerName;
    }
    public static function getControllerNameOrigin()
    {
        return self::$controllerNameOrigin;
    }
}
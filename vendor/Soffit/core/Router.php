<?php
namespace soffit;


class Router
{
    /**
     * Current phisical rout to file
     * @var string
     */
    protected static string $rout = '';

    /**
     * Current rout for new class (with aliases) 
     * @var string
     */
    protected static string $routFile = '';

    /**
     * @array $params
     * разобранный массив параметров, пришедших из строки запроса
     */
    protected static array $params=[];

    /**
     * @string $controllerName
     * Имя текущего контроллера
     *
     */
    protected static string $controllerName = '';

    /**
     * оригинальное имя контроллера из url, нужен для сооздания URL
     */
    protected static string $controllerNameOrigin = '';


    /**
     * @param $uri
     * @throws \Exception
     */
    public static function parseRout($uri)
    {
        if ( empty($uri) ) throw new \Exception("REQUEST_URI is empty",444);
        $routs = explode('?', trim($uri,'/') );
        //debug($routs,'$routs');

        $controller = explode('/', trim($routs[0],'/') )[0];
        self::$controllerNameOrigin = $controller = self::filterControllerName( $controller );

        if ( !empty($controller) ) {
            self::$controllerName = str_ireplace(' ','', ucwords(str_ireplace('-',' ', $controller)) );
        } else {
            self::$controllerName = ucwords(Config::get('defaultController'));
        }
        
        //debug(self::$controllerNameOrigin,'controllerNameOrigin');
        //debug(self::$controllerName,'self::$controllerName');

        // _ добавляем для имени папки
        //self::$rout = "Views\\" . "_" . self::$controllerName . "\Controllers\\" .  self::$controllerName . "Controller";
        self::$routFile = "Pages\Controllers\\" .  self::$controllerName . "Controller";
        self::$rout = "controllers\\" .  self::$controllerName . "Controller";
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


    public static function getRout() : string
    {
        return self::$rout;
    }
    public static function getRoutFile() : string
    {
        return self::$routFile;
    }
    public static function getParams() : array
    {
        return self::$params;
    }
    public static function getControllerName() : string
    {
        return self::$controllerName;
    }
    public static function getControllerNameOrigin() : string
    {
        return self::$controllerNameOrigin;
    }
    protected static function filterControllerName( string $name ) : string
    {
        $contrChars = self::controllerChars();
        $symbols = mb_str_split($name, 1,"utf-8");
        foreach($symbols as $k => $symbol)
        {
            if ( !in_array($symbol, $contrChars) )
                unset($symbols[$k]);
        }

        return strtolower(implode('',$symbols));
    }

    protected static function controllerChars()
    {
        $res[] = chr(45);
        for ( $i = 48; $i <= 122; $i++ )
        {
            if ( $i === 58 ) $i = 65;
            if ( $i === 91 ) $i = 97;
            $res[] = chr($i);
        }
        return $res;
    }
}
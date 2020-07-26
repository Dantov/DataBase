<?php

namespace Views\vendor\libs\classes;

use Views\vendor\core\Crypt;
use Views\vendor\libs\classes\AppCodes;

class URLCrypt extends Crypt
{

    protected static $secretKey = "s";
    protected static $algo1 = "crc32";
    protected static $algo2 = "crc32";

    public static $queryVar = '';
    public static $gerVars = [];


    protected function __construct()
    {
    }

    /**
     * 
     * @param string $queryVar
     * @param array $getVars
     * @return string
     */
    public static function encode( string $queryVar, string $url ) : string
    {   
        if ( empty($queryVar) ) 
            throw new \Exception(AppCodes::getMessage(AppCodes::QUERY_VAR_EMPTY)['message'], AppCodes::QUERY_VAR_EMPTY);
        if ( empty($url) ) 
            throw new \Exception(AppCodes::getMessage(AppCodes::URL_EMPTY)['message'], AppCodes::URL_EMPTY);

        $exp = explode('?', $url);
        
        if ( !trueIsset($exp[1]) ) 
            throw new \Exception(AppCodes::getMessage(AppCodes::URL_PARAMS_EMPTY)['message'], AppCodes::URL_PARAMS_EMPTY);


        $params = self::strEncode($exp[1], 's', "crc32","crc32");
        //$params = base64_encode($exp[1]);
        //$r = hash($v, $data, false);

        return "?" . $queryVar . "=" .$params;
    }

    public static function decode( $cipherText ) : string
    {
        //return base64_decode($cipherText);
        return self::strDecode($cipherText, 's', "crc32","crc32");
    }

}
<?php

namespace Views\_Globals\Models;

class FSConverter
{

    private static function _Lower()
    {
        return array(
            'q','w','e','r','t','y','u','i','o','p','a','s','d','f','g','h','j','k','l','z','x','c','v','b','n','m', 'ё','й','ц','у','к','е','н','г','ш','щ','з','х','ъ','ф','ы','в','а','п','р','о','л','д','ж','э','я','ч','с','м','и','т','ь','б','ю');
    }

    private static function _Upper()
    {
        return array(
            'Q','W','E','R','T','Y','U','I','O','P','A','S','D','F','G','H','J','K','L','Z','X','C','V','B','N','M', 'Ё','Й','Ц','У','К','Е','Н','Г','Ш','Щ','З','Х','Ъ','Ф','Ы','В','А','П','Р','О','Л','Д','Ж','Э','Я','Ч','С','М','И','Т','Ь','Б','Ю');
    }

    public static function ToUpper($string)
    {
        return str_replace(self::_Lower(), self::_Upper(), $string);
    }

    public static function ToLower($string)
    {
        return str_replace(self::_Upper(), self::_Lower(), $string);
    }

    public static function firstToUpper($string)
    {
        return str_replace(self::_Lower(), self::_Upper(), $string,1);
    }

}
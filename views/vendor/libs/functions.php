<?php
if ( !defined('END_AB') ) define('END_AB','end_ajax_buffer');

if ( _DEV_MODE_ )
{
    function debug($arr, $str='', $die=false, $encode=false)
    {
        $result = [];
        if ( !empty($str) ) $str = $str . " = ";

        if ( $encode )
            ob_start();

        echo '<pre style="display: inline-block !important; vertical-align: top; margin-left: 5px; padding: 5px; border-bottom: 1px solid #0f0f0f; border-left: 1px solid #0f0f0f">';
        echo $str;
        print_r($arr);
        echo '</pre>';

        if ( $encode )
        {
            $result = ['debug'=>ob_get_contents()];
            ob_end_clean();
        }
        if ($die)
        {
            if ($encode)
                exit(json_encode($result));
            exit;
        }

        if ($encode)
            echo json_encode($result);
    }


    function debugAjax( $arr, $str='', $ob = null )
    {
        // что бы не открыть несколько буферов при получении START_AB
        static $ajaxBufferCondition = false;

        if ( !empty($str) )
            $str = $str . " = ";

        if ( !$ajaxBufferCondition )
        {
            $ajaxBufferCondition = true;
            ob_start();
        }


        echo '<pre style="display: inline-block !important; vertical-align: top; margin-left: 5px; padding: 5px; border-bottom: 1px solid #0f0f0f; border-left: 1px solid #0f0f0f">';
        echo $str;
        print_r($arr);
        echo '</pre>';

        if ( $ob === END_AB )
        {
            $result = ['debug'=>ob_get_contents()];
            ob_end_clean();
            exit( json_encode( $result ) );
        }
    }

} else {
    function debug($arr, $str='', $die=false)
    {}
    function debugAjax( $arr, $str='', $ob = null )
    {}
}

if (!function_exists('array_key_first'))
{
    function array_key_first(array $array)
    {
        if (count($array))
        {
            reset($array);
            return key($array);
        }
        return null;
    }
}

if (!function_exists('array_key_last')) {
// вернет ключ последенго элемента массива
    function array_key_last(array $arr)
    {
        if (empty($arr))
            return '';

        $keys = array_keys($arr);
        return $keys[count($keys) - 1];
    }
}

function validateDate($date, $format = 'Y-m-d') //Y-m-d H:i:s
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}
function formatDate($date)
{
    $fdate = is_int($date) ? '@'.$date : $date;
    return date_create( $fdate )->Format('d.m.Y');
}

define('_NUMBERS_', '0123456789' );
define('_SYMBOLS_', '!@#№$%^&?_=+,-^:;{}[]' );
define('_CHARS_RU_', 'абвгдеёжзийклмнопрстуфхцчшщъыьэюя'. strtoupper('абвгдеёжзийклмнопрстуфхцчшщъыьэюя') );
define('_CHARS_EN_', 'abcdefghijklmnopqrstuvwxyz'. strtoupper('abcdefghijklmnopqrstuvwxyz') );
/*
 * генератор случайной строки
 * @var string $language - ru|en
 * @var number $length - желаемая длинна
 *
 * @var string $method - метод генерации символов
 * all - все доступные символы;
 * symbols - буквы и цифры;
 * chars - только буквы;
 * numbers - только цифры;
 * return string
 */
function randomStringChars( $length=null, $language='en', $method='chars' )
{
    $mixedCharsEn = '';
    $mixedCharsRu = '';

    /* если в параметрах передали только число - то это будет длинна строки */
    /*
    if ( func_num_args() == 1 )
    {
        $arg = func_get_args();
        if ( is_int($arg[0]) ) $length = $arg[0];
    }
    */
    if ( $length === null ) $length = mt_rand(1,10);

    switch ($method)
    {
        case "all":
            $mixedCharsEn = _CHARS_EN_._NUMBERS_._SYMBOLS_;
            $mixedCharsRu = _CHARS_RU_._NUMBERS_._SYMBOLS_;
            break;
        case "symbols":
            $mixedCharsEn = _CHARS_EN_._NUMBERS_;
            $mixedCharsRu = _CHARS_RU_._NUMBERS_;
            break;
        case "chars":
            $mixedCharsEn = _CHARS_EN_;
            $mixedCharsRu = _CHARS_RU_;
            break;
        case "numbers":
            $mixedCharsEn = _NUMBERS_;
            $mixedCharsRu = _NUMBERS_;
            break;
    }
    if (!function_exists('setChars')) {
        function setChars( $chars )
        {
            $characters = $chars;
            $characters = preg_split( '//u', $characters, -1, PREG_SPLIT_NO_EMPTY );
            shuffle( $characters );
            return implode( $characters );
        }
    }
    switch ($language)
    {
        case 'ru':
            $characters = setChars($mixedCharsRu);
            break;
        case 'en':
            $characters = setChars($mixedCharsEn);
            break;
        default:
            $characters = setChars($mixedCharsEn);
            break;
    }
    $str = '';
    if ( !$length ) $length = mt_rand(2, iconv_strlen($characters));
    for ($i = 0; $i < $length; $i++) {
        $str .= mb_substr( $characters, mt_rand(0, iconv_strlen($characters)), 1);
    }
    return $str;
}

/**
 * Проверяет наличие переменной, и какого-либо значения в ней
 * @param $var
 * @return bool
 */
function trueIsset($var)
{
    if ( isset($var) && !empty($var) ) return true;
    return false;
}
function alphabet() : array
{
    return array(
        "а"=>"a",
        "б"=>"b",
        "в"=>"v",
        "г"=>"g",
        "д"=>"d",
        "е"=>"e",
        "ё"=>"e",
        "ж"=>"j",
        "з"=>"z",
        "и"=>"i",
        "й"=>"i",
        "к"=>"k",
        "л"=>"l",
        "м"=>"m",
        "н"=>"n",
        "о"=>"o",
        "п"=>"p",
        "р"=>"r",
        "с"=>"s",
        "т"=>"t",
        "у"=>"y",
        "ф"=>"f",
        "х"=>"h",
        "ц"=>"c",
        "ч"=>"ch",
        "ш"=>"sh",
        "щ"=>"w",
        "ъ"=>"",
        "ы"=>"u",
        "ь"=>"",
        "э"=>"e",
        "ю"=>"u",
        "я"=>"ia",
        " "=>"_",
        "°"=>"_"
    );
};
function getMonthRu( int $num ) : string
{
    $_monthsList = array(
    "1"=>"Январь","2"=>"Февраль","3"=>"Март",
    "4"=>"Апрель","5"=>"Май", "6"=>"Июнь",
    "7"=>"Июль","8"=>"Август","9"=>"Сентябрь",
    "10"=>"Октябрь","11"=>"Ноябрь","12"=>"Декабрь"
    );

    return $_monthsList[(string)$num];
}

function in_array_recursive( $needle, array &$array, bool $strict = false ) : bool
{
    foreach ( $array as $key => $value )
    {
        $found = $strict ? $needle === $value : $needle == $value;
        if($found) return true;

        if ( is_array($value) )
            $found = in_array_recursive($needle,$value);

        if($found) return true;
    }

    return isset($found) ? $found : false;
}

function timeElapsed($secs)
{
    $ret = [];
    $bit = array(
        'г' => $secs / 31556926 % 12,
        'нед.' => $secs / 604800 % 52,
        'дней' => $secs / 86400 % 7,
        'час.' => $secs / 3600 % 24,
        'мин.' => $secs / 60 % 60,
        'сек.' => $secs % 60
    );

    foreach($bit as $k => $v)
        if($v > 0)$ret[] = $v . $k;

    return join(' ', $ret);
}
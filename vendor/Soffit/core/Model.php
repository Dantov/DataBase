<?php
/**
 * Date: 28.07.2020
 * Time: 15:33
 */
namespace soffit;

use soffit\db\BaseSQL;
use libs\classes\AppCodes;

/**
 * 
 * @package Views\vendor\core
 */
class Model extends BaseSQL
{
    
    /**
     * @param array $dbConfig
     * @throws \Exception
     */
    public function connectDB( array $dbConfig=[] ) : void
    {
    }


    public function formatDate($date)
    {
        $fdate = is_int($date) ? '@'.$date : $date;
        return date_create( $fdate )->Format('d.m.Y');
    }


    /**
     * возвращает строку в транслите.
     * @param $str
     * @return string
     */
    public function translit($str)
    {
        $str = mb_strtolower($str,'UTF-8');
        $chars = preg_split('//u',$str,-1,PREG_SPLIT_NO_EMPTY);

        foreach ($chars as $key => $value) {
            $ff = false;
            foreach ($this->alphabet as $alph_key => $alph_value) {
                if ( $value == $alph_key ) {
                    $eng_arrmt[] = $alph_value;
                    $ff = true;
                    continue;
                }
            }
            if ( !$ff ) $eng_arrmt[] = $value;
        }
        return implode($eng_arrmt?:[]);
    }
    
    public function rrmdir($src) {
        $dir = opendir($src);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                $full = $src . '/' . $file;
                if ( is_dir($full) ) {
                    $this->rrmdir($full);
                }
                else {
                    unlink($full);
                }
            }
        }
        closedir($dir);
        rmdir($src);
    }

     /**
     *  From:
        [name] => Array
            (
                [0] => строка
            )
        [value] => Array
            (
                [0] => 150
            )

        [id] => Array
            (
                [0] => 2196
            )
     *  To:
        [0] => Array
            (
                [name] => строка
                [value] => 150
                [id] => 2196
            )
     * @param $records
     * @return array
     */
    public function parseRecords(array $records) : array
    {
        if ( !is_array($records) ) return [];
        $parsedRecords = [];

        foreach ( $records as $field => $record )
        {
            foreach ( $record as $key => $value )
            {
                $parsedRecords[$key][$field] = $value;
            }
        }
        return $parsedRecords;
    }

}
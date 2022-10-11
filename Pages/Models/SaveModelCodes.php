<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 02.12.2020
 * Time: 23:16
 */
namespace models;

use libs\classes\AppCodes;

class SaveModelCodes extends AppCodes
{

    /**
     * Errors
     */
    const WRONG_CONDITION = 221;
    const WRONG_SET_CONDITION = 222;

    const WRONG_STATUS = 196;
    const ERROR_UPLOAD_FILE = 195;


    /** MESSAGES ARRAY **/
    protected static $MESSAGES = [
        self::WRONG_CONDITION => [
            'code' => self::WRONG_CONDITION,
            'message'=>'Wrong save condition.'
        ],
        self::WRONG_SET_CONDITION => [
            'code' => self::WRONG_SET_CONDITION,
            'message'=>'Cant set condition twice.'
        ],
        self::WRONG_STATUS => [
            'code' => self::WRONG_STATUS,
            'message'=>'Передан не верный статус!'
        ],
        self::ERROR_UPLOAD_FILE => [
            'code' => self::ERROR_UPLOAD_FILE,
            'message'=>'Ошибка при передаче файла!'
        ],
    ];


    /**
     * @param int $code
     * @param bool $text
     * @return
     * @throws \Exception
     */
    public static function message( int $code, bool $text = false )
    {
        if ( $text )
            return self::getMessage($code)['message'];

        return self::getMessage($code)['message'];
    }

}
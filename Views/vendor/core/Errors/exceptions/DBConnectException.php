<?php

namespace Views\vendor\core\Errors\exceptions;
use Views\vendor\libs\classes\AppCodes;

/**
 * Class DBConnectException
 * Исключения связанные с БД
 * @package Views\vendor\core\Errors\Exceptions
 */
class DBConnectException extends \Exception
{
    /**
     * DBConnectException constructor.
     * @param string $message
     * @param int $code
     * @throws \Exception
     */
    public function __construct( $code )
    {
        $message = [];
        switch ( $code )
        {
            case AppCodes::DB_CONNECTION_ERROR:
                {
                    $message = AppCodes::getMessage(AppCodes::DB_CONNECTION_ERROR);
                } break;
            case AppCodes::DB_CONFIG_EMPTY:
                {
                    $message = AppCodes::getMessage(AppCodes::DB_CONFIG_EMPTY);
                } break;
            case AppCodes::USER_DB_CONFIG_EMPTY:
                {
                    $message = AppCodes::getMessage(AppCodes::USER_DB_CONFIG_EMPTY);
                } break;
            case AppCodes::DB_CONFIG_ACCESS_FIELD_EMPTY:
                {
                    $message = AppCodes::getMessage(AppCodes::DB_CONFIG_ACCESS_FIELD_EMPTY);
                } break;
            
            case AppCodes::DB_CONFIG_DRIVER_ERROR:
                {
                    $message = AppCodes::getMessage(AppCodes::DB_CONFIG_DRIVER_ERROR);
                } break;
            case AppCodes::DB_CONFIG_HOST_ERROR:
                {
                    $message = AppCodes::getMessage(AppCodes::DB_CONFIG_HOST_ERROR);
                } break;
            case AppCodes::DB_CONFIG_DBNAME_ERROR:
                {
                    $message = AppCodes::getMessage(AppCodes::DB_CONFIG_DBNAME_ERROR);
                } break;
            case AppCodes::DB_CONFIG_USERNAME_ERROR:
                {
                    $message = AppCodes::getMessage(AppCodes::DB_CONFIG_USERNAME_ERROR);
                } break;
            case AppCodes::DB_CONFIG_PASSWORD_ERROR:
                {
                    $message = AppCodes::getMessage(AppCodes::DB_CONFIG_PASSWORD_ERROR);
                } break;
            case AppCodes::DB_CONFIG_CHARSET_ERROR:
                {
                    $message = AppCodes::getMessage(AppCodes::DB_CONFIG_CHARSET_ERROR);
                } break;
        }

        parent::__construct( $message['message'], $message['code'] );
    }

}
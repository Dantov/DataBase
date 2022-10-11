<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 02.12.2020
 * Time: 23:08
 */
namespace models;


class Condition
{

    protected static $NEW = false;
    protected static $EDIT = false;
    protected static $INCLUDE = false;

    private static $isSet = false;


    /**
     * @param int $condNumber
     * @throws \Exception
     */
    public static function set( int $condNumber )
    {
        if ( self::$isSet )
            throw new \Exception( SaveModelCodes::getMessage(SaveModelCodes::WRONG_SET_CONDITION)['message'], SaveModelCodes::WRONG_SET_CONDITION);

        switch ( $condNumber )
        {
            case 1:
                self::$NEW = true;
                break;
            case 2:
                self::$EDIT = true;
                break;
            case 3:
                self::$INCLUDE = true;
                break;
            default : throw new \Exception( SaveModelCodes::getMessage(SaveModelCodes::WRONG_CONDITION)['message'], SaveModelCodes::WRONG_CONDITION);
        }

        self::$isSet = true;
    }

    /**
     * Новая модель
     */
    public static function isNew()
    {
        return self::$NEW;
    }

    /**
     * Редактируем модель
     */
    public static function isEdit()
    {
        return self::$EDIT;
    }

    /**
     * Новая модель, но это комплект к другой модели
     */
    public static function isInclude()
    {
        return self::$INCLUDE;
    }

}
<?php
namespace Views\_Main\Models;

/**
 * User: BVA
 * Date: 03.10.2019
 * Time: 12:51
 */
class LastDateFinder
{
    /*
     * $dateStart array - все даты поступления ( в рамках одного участка )
     */
    private static $dateStart = [];

    /*
     * $dateEnd array - все даты сдачи ( в рамках одного участка )
     */
    private static $dateEnd = [];

    public static function setDatesStart($status)
    {
        if ( !validateDate($status['date'],'Y-m-d H:i:s') ) return;
        self::$dateStart[] = $status;
    }

    public static function setDatesEnd($status)
    {
        if ( !validateDate($status['date'],'Y-m-d H:i:s')) return;
        self::$dateEnd[] = $status;
    }

    public static function getDateStart()
    {
        return self::getMax('start');
    }

    public static function getDateEnd()
    {
        return self::getMax('end');
    }

    /**
     * @param string $str
     * @return array
     * Вернет пустой массив если не было дат статусов
     */
    protected static function getMax($str='' )
    {
        //поиск макс
        $max = 0;
        $needleStatus = [];
        foreach ( $str == 'start' ? self::$dateStart : self::$dateEnd as $status )
        {
            $thisDate = strtotime($status['date']);
            if ( $thisDate > $max )
            {
                $max = $thisDate;
                $needleStatus = $status;
            }
        }
        return $needleStatus;
    }

    public static function clear()
    {
        self::$dateStart = [];
        self::$dateEnd = [];
    }

}
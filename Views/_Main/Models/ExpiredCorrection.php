<?php
namespace Views\_Main\Models;

class ExpiredCorrection
{

    /**
     * @expiredCenter array - все данные последнего участка на котором есть "просрочено"
     * или на котором установлена последняя дата
     */
    public static array $expiredCenter = [];
	
	/**
	* @var int $plusDays
	* Дни в миллисекундах. 
	*/
	private static int $plusDays = 0;
	
	
	public static function run(array &$wCenters, $lastStatus)
	{
		// Если на пред. участках даты свежее чем последняя дата
		// сотрет старые даты
		self::freshDateCorrection($wCenters);
		
		// если есть дата сдачи но нет даты принятия в течении 2х суток - поставим просрочено!
		self::adjustExpiredAdmission($wCenters, $lastStatus);
		
		// на последок проверим просроченные
		self::adjustExpiredFinish($wCenters, $lastStatus);

		// почистим для след. модели
		self::clear();
	}

    /**
     * найдет последнюю поставленную дату
     * @param array $wCenters
     * @return array
     */
    protected static function findLastMove(&$wCenters = [])
    {
        foreach ( array_reverse($wCenters) as &$wCenter )
        {
            if ( isset($wCenter['end']['date']) && is_string($wCenter['end']['date']) )
            {
				return self::$expiredCenter = $wCenter;
            }
            if ( isset($wCenter['start']['date']) && is_string($wCenter['start']['date']) )
            {
				return self::$expiredCenter = $wCenter;
            }
        }
        return [];
    }
    
    /**
	* среднее отведенное время для работы над каждой моделью
	* 
	*/
	private static function allowedWorkTime($lastCheckedCenterDate)
    {
		$plusDays = 2 * 24 * 60 * 60; // +сутки в раб. день // 1 дней; 24 часа; 60 минут; 60 секунд
		if ( date("w", $lastCheckedCenterDate) == 5 )
			$plusDays = 4 * 24 * 60 * 60; // +4 суток с пятницы
		if ( date("w", $lastCheckedCenterDate) == 6 )
			$plusDays = 3 * 24 * 60 * 60; // +3 суток с субботы	
		self::$plusDays = $plusDays;
		return $plusDays;
    }


    /**
     * Не актуально
	 * Убираем Просрочено если дальше, на участках, есть даты
     * @param array $wCenters - массив участков
     * проверим наличие дат, после "просрочено"
     * изменяет оригинальный массив
     */
    public static function adjustOLD(&$wCenters = [])
    {

        /* @var &$wCenterEndDate
         * указатель! на ['end']['date'] элемента массива
         * ставим ему пустую строку, если дальше есть даты
         */
        $wCenterEndDate = '';

        /* @var boolean $flag
         *  флаг просроченной даты
         */
        $flag = false;

        /**
         * @var $centerName
         * имя каждого участка
         *
         * @var &$wCenter
         * ссылка на массив участка
         */
        foreach ( $wCenters as $centerName => &$wCenter )
        {
            if ( $flag )
            {
                // если есть даты на след. участках
                // то сотрем "просрочено"
                if ( is_string($wCenter['start']['date']) || is_string($wCenter['end']['date']) )
                {
                    $wCenterEndDate = '';
                    $flag = false;
                }
            }

            if ( $wCenter['end']['date'] == -1 )
            {
                $flag = true;
                $wCenterEndDate = &$wCenter['end']['date'];
                self::$expiredCenter = $wCenter;
                continue;
            }

        }
    }

    /**
	 * Если на пред. участках даты свежее чем последняя дата
     * Сотрем старые даты
     * @param array $wCenters
     */
	public static function freshDateCorrection(array &$wCenters = [])
    {
		$expiredCenter = self::$expiredCenter; // участок на котором висит просрочено
		if ( empty($expiredCenter) )
			$expiredCenter = self::findLastMove($wCenters); // нет просроченных - найдем последнюю дату
		//debug($expiredCenter,'$expiredCenter');
		
		
        
        $lastStatusDate = 0;
        if ( isset( $expiredCenter['end']['date'] ) && is_string($expiredCenter['end']['date']) )
        {
			$lastStatusDate = strtotime($expiredCenter['end']['date']);
			//debug($expiredCenter['end'],'enddate');
        } elseif ( isset( $expiredCenter['start']['date'] ) && is_string($expiredCenter['start']['date']) )
        {
			$lastStatusDate = strtotime($expiredCenter['start']['date']);
			//debug($expiredCenter['start'],'startdate');
        }
        


        $wCenterStartDate = 0;
        $wCenterEndDate = 0;
        $arrayNeedle['start']['date'] = '';
        $arrayNeedle['end']['date'] = '';

        // начинаем смотреть с конца, для того что бы найти первую(с конца) свежую дату
        // на данный момент ищем самую новую
        foreach ( array_reverse($wCenters) as &$wCenter )
        {
			$endDate = $wCenter['end']['date']??'';
			$startDate = $wCenter['start']['date']??'';
			
			// этот же просроченный участок пропустим
			if ( $endDate === -1 || $startDate === -1 ) continue;
				
			if ( is_string($startDate) )
				$wCenterStartDate = strtotime($startDate);
			if ( is_string($endDate) )
				$wCenterEndDate = strtotime($endDate);

			if ( $wCenterEndDate > $lastStatusDate || $wCenterStartDate > $lastStatusDate )
            {
                // если нашли такую дату - запомним этот участок
				if ($wCenterEndDate > strtotime($arrayNeedle['end']['date']??'') )
				{
					$arrayNeedle = $wCenter;
				}
				if ($wCenterStartDate > strtotime($arrayNeedle['start']['date']??'') ) {
					$arrayNeedle = $wCenter;
				}
				
                //$arrayNeedle = $wCenter;
				//debug($arrayNeedle,'$arrayNeedle');
            }
        }

        // вычислим ID участка с которого надо начинать стирать даты
        $id = 987;
        foreach ( $wCenters as $arrID => $wCenter ) if ( $wCenter === $arrayNeedle ) { $id = $arrID; break; }
		
        // сотрем дату принятия на этом участке, если она старая
        if ( isset($wCenters[$id]['end']['date']) )
        {
			if ( strtotime($wCenters[$id]['end']['date']) <= $lastStatusDate )
            {
                $wCenters[$id]['end'] = [];
            }
        }
        $id++;

        // стираем все даты на след. участках (они старые)
        for ($i = $id; $i < count($wCenters); $i++)
        {
            $wCenter = &$wCenters[$i];
            $wCenter['end'] = $wCenter['start'] = [];
        }

    }

    /**
     * если есть дата сдачи но нет даты принятия в течении 2х суток - поставим просрочено!
     * @param array $wCenters
     * @param $lastStatus
     * Последний статус у этой модели из таблице Stock
     */
	public static function adjustExpiredAdmission(array &$wCenters, $lastStatus)
    {
    	// Уходим если модель Отложена/Снята с пр.
		if ( $lastStatus == 11 || $lastStatus == 88) return;
			
        $lastCheckedCenter = [];

        // Если нашли дату сдачи - запомним этот участок.
        // Чтоб посмотреть дальше есть ли даты поступления
        foreach ( array_reverse($wCenters) as &$wCenter )
        {
            if ( isset($wCenter['end']['date']) && is_string($wCenter['end']['date']) )
            {
                $lastCheckedCenter = $wCenter;
                break;
            }
        }

        $id = 987; //ID этого участка в массиве
        foreach ( $wCenters as $arrID => $wCenter ) if ( $wCenter === $lastCheckedCenter ) { $id = $arrID; break; }

        $id++; // перейдем на след участок
        for ($j = $id; $j < count($wCenters); $j++)
        {
            // если дальше по участкам есть даты принятия, то здесь делать больше нечего.
            if ( isset( $wCenters[$j]['start']['date'] ) && is_string($wCenters[$j]['start']['date']) ) return;
        }

        // Если мы еще здесь, после всех манипуляций!!!
        // значит след. участки пусты.
        // проверим сколько прошло времени и поставим просрочено.

        $lastCheckedCenterDate = strtotime($lastCheckedCenter['end']['date']??'');
		$runningOutIn = $lastCheckedCenterDate + self::allowedWorkTime($lastCheckedCenterDate);

		if ( time() > $runningOutIn && isset($wCenters[$id]) )
        {
            $wCenters[$id]['start']['date'] = -1;
        }
    }
    
    /**
	* Последгий раз ищем просроченную дату
	* после всех манипуляций
	* 
	* @return
	*/
	public static function adjustExpiredFinish(array &$wCenters, $lastStatus)
    {
		if ( $lastStatus == 11 || $lastStatus == 88) return;
		
		// последний участок с датой
		$lastCenter = self::findLastMove($wCenters);
		if ( isset($lastCenter['end']['date']) )
			return;
		
		if ( isset($lastCenter['start']['date']) && is_string($lastCenter['start']['date']) ) 
		{
			$lastCheckedCenterDate = strtotime($lastCenter['start']['date']);
			$plusDays = self::allowedWorkTime($lastCheckedCenterDate);
			$runningOutIn = $lastCheckedCenterDate + $plusDays;
			
			if ( time() > $runningOutIn ) {
				
				$id = 987; //ID этого участка в массиве
				foreach ( $wCenters as $arrID => $wCenter )
					if ( $wCenter === $lastCenter ) {
					    $id = $arrID; 
					    break; 
					}
				$wCenters[$id]['end']['date'] = -1;
			}
		}
    }

    /**
     * стираем всё для след. модели
     */
    public static function clear()
    {
        self::$expiredCenter = [];
        self::$plusDays = 0;
    }

}
<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 15.07.2022
 * Time: 13:47
 */
namespace models;

use soffit\ActiveQuery;
use soffit\db\Table;

class SelectBy extends ActiveQuery
{

    public static function modelType( string $type )
    {

    }

    /**
     * @param string $material
     * @return string
     * @throws \Exception
     */
    public static function modelMaterial( string $material, Table $MetalCovering ) : string
    {
        if ( empty($material) )
            throw new \Exception("Empty modelType in " . __METHOD__, 1001);
        $select = $MetalCovering
            ->select(['pos_id'],true)
            ->where('type','like',"%$material%")
            ->exe();

        $res = "";
        foreach ( $select as $posID )
        {
            if ( !$posID['pos_id'] ) continue;
            $res .= $posID['pos_id'] . ",";
        }

        return trim($res,',');
    }

    /**
     * @param string $gemType
     * @return string
     * @throws \Exception
     */
    public static function gemType( string $gemType, Table $Gems ) : string
    {
        if ( empty($gemType) )
            throw new \Exception("Empty gem type in " . __METHOD__, 1002);
        $select = $Gems
            ->select(['pos_id'],true)
            ->where('gems_names','like',"%$gemType%")
            ->exe();

        $res = "";
        foreach ( $select as $posID )
        {
            if ( !$posID['pos_id'] ) continue;
            $res .= $posID['pos_id'] . ",";
        }

        return trim($res,',');
    }


    /**
     * Производит дополнительный поиск в таблице статусов
     * @param @number $statusID - ID статуса по которому ищем
     * @param $dates - массив с датами От и До, если они были заданы
     */
    public static function byStatusesHistory(int $statusID, array $dates, Table $Statuses) : string
    {
        if ( !$statusID ) return '';

        $Statuses->select(['pos_id'])->where('status','=',$statusID); // distinct?
        
        if ( !empty($dates) )
        {
            if (!empty($dates['from'])) {
                $from = $dates['from'];
                $Statuses->and('date','>=',$from); // От
            }
            if (!empty($dates['to'])) {
                $to = $dates['to'];
                $Statuses->and('date','<=',$to);   // До
            }
        }
        $searchedModels = $Statuses->asArray()->exe(); //andWhere('pos_id','in',$in)

        $res = '';
        foreach ( $searchedModels as $model )
        {
            if ( !$model['pos_id'] ) continue;
            $res .= $model['pos_id'] . ",";
        }
        return trim($res,',');
    }

}
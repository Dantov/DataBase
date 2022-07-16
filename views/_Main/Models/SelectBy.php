<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 15.07.2022
 * Time: 13:47
 */

namespace Views\_Main\Models;


use Views\vendor\core\ActiveQuery;

class SelectBy
{

    public static function modelType( string $type )
    {

    }

    /**
     * @param string $material
     * @return string
     * @throws \Exception
     */
    public static function modelMaterial( string $material ) : string
    {
        if ( empty($material) )
            throw new \Exception("Empty modelType in " . __METHOD__, 1001);

        //$select = "SELECT DISTINCT pos_id FROM metal_covering WHERE type LIKE '%$modelType%'";

        $aq = new ActiveQuery('metal_covering');
        $select = $aq->metal_covering->select(['pos_id'],true)
            ->where('type','like',"%$material%")->asArray()->exe();

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
    public static function gemType( string $gemType )
    {
        if ( empty($gemType) )
            throw new \Exception("Empty gem type in " . __METHOD__, 1002);

        $select = (new ActiveQuery('gems'))->gems->select(['pos_id'],true)
            ->where('gems_names','like',"%$gemType%")->asArray()->exe();

        //debug($select,'$select',1);
        $res = "";
        foreach ( $select as $posID )
        {
            if ( !$posID['pos_id'] ) continue;
            $res .= $posID['pos_id'] . ",";
        }

        //debug($res,'$res',1);

        return trim($res,',');
    }

}
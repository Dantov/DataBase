<?php
/**
 * Date: 16.08.2021
 * Time: 21:18
 */
namespace Views\_TestTest\Controllers;

use Views\_Globals\Controllers\GeneralController;
use Views\vendor\core\ActiveQuery;


class TestTestController extends GeneralController
{

    public $title = 'ХЮФ :: тестовая';


    /**
     * @throws \Exception
     */
    public function beforeAction()
    {

        if ( !_DEV_MODE_ )
            $this->redirect('/main/');

        $request = $this->request;

        if ( $request->isAjax() )
        {
            exit;
        }
    }

    /**
     * @throws \Exception
     */
    public function action()
    {
        $hello = "Preview Test Area!";


        $aq = new ActiveQuery();
        $aq->registerTable('stock');
        $aq->registerTable('images');

        $images = $aq->images??'';
        $images->alias = 'img';
        $stock = $aq->stock??'';
        $stock->alias = 'st';

        $red = $aq->link(['images'=>'pos_id'], ['stock'=>'id']);


        //debug($red,'$red',1);

        $res = $stock->select(['id','model_type','number_3d','model_weight'])

                        ->join($images,['img_name','main'],'=') //

                        ->where('model_type','=','Кольцо')
                        ->and(['model_weight','>',5])
                        ->or(['model_type','LIKE','%Серьги%'])
                        ->and(['model_weight','IN','5,6,7'])
                        ->limit(20)->orderBy('model_type','ASC')->asArray()
            //->build();
            ->exe();

        $build = $stock->buildedQuery;

        //        $stock->setFieldsAlias([
//            'model_type'=>'ModType',
//            'number_3d'=>'Num3D',
//        ]);
        /*
        $stock = $qb->stock;

        // строка запроса
        $stock->select(['number_3d','id','model_type'])->where('a','>','b')->and('b','<','4')->orderBy('model_type')->limit(200)->build();

        // одна запись
        $stock->select(['number_3d','id','model_type'])->where('a','>','b')->and('b','<','4')->orderBy('model_type')->limit(200)->findOne();

        // массив
        $stock->select(['number_3d','id','model_type'])->where('a','>','b')->and('b','<','4')->orderBy('model_type')->limit(200)->findAsArray();

        $this->row  = $queryBuilder->findOne( " SELECT * FROM stock    WHERE     id='$this->id' ");
        $this->img  = $this->findAsArray( " SELECT * FROM images   WHERE pos_id='$this->id' ");
        */


        //$testform1 = $stock->findAll()->where(['id','>',70],['email','like','dant'])->with('files')->orderby('name')->limit(2)->go();
        //$testform1 = $stock->findAll()->limit(1)->go();
        //$testform1 = $stock->findOne()->where(['id','<',72])->with('files')->go();


        $compacted = compact(['hello','res', 'build']);
        return $this->render('test', $compacted);
    }



}
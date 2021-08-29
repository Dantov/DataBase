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

        $thisID = 2145;
        $number_3d = '0008000';



        $aq = new ActiveQuery();
        $stock = $aq->registerTable('stock','st');
        $images = $aq->registerTable(['images'=>'img']);
        $aq->link(['id'=>$stock], '=' ,['pos_id'=>$images]);
        $res = $stock
            ->select(['id','model_type','number_3d'])
            ->join($images,['pos_id','img_name','main','sketch'])
            //->joinAnd($images,'sketch', '=', 1)
            ->where('number_3d','=',$number_3d)->and('id','<>',$thisID)
            ->asArray()
            ->exe();




        $sql = " SELECT st.id, st.model_type, st.number_3d, img.pos_id, img.img_name, img.main, img.sketch
				FROM stock st 
					LEFT JOIN images img ON ( st.id = img.pos_id ) AND img.sketch=1
				WHERE st.number_3d='{$number_3d}' 
				AND st.id<>'{$thisID}' ";
        $old_style  = $aq->findAsArray( $sql );





        //$build = $stock->buildedQuery;
        //$where = $stock->statement_WHERE;

        //        $stock->setFieldsAlias([
//            'model_type'=>'ModType',
//            'number_3d'=>'Num3D',
//        ]);
        /*
        $stock = $qb->stock;


        $this->row  = $queryBuilder->findOne( " SELECT * FROM stock    WHERE     id='$this->id' ");
        $this->img  = $this->findAsArray( " SELECT * FROM images   WHERE pos_id='$this->id' ");
        */


        //$testform1 = $stock->findAll()->where(['id','>',70],['email','like','dant'])->with('files')->orderby('name')->limit(2)->go();
        //$testform1 = $stock->findAll()->limit(1)->go();
        //$testform1 = $stock->findOne()->where(['id','<',72])->with('files')->go();


        $compacted = compact(['hello','res', 'build', 'where','old_style']);
        return $this->render('test', $compacted);
    }



}
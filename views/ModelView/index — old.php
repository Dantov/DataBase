<?php
require('../Glob_Controllers/sessions.php');
require('controllers/ModelView_Controller.php');
?>
<!DOCTYPE HTML>
<html>
<head>
	<?php include('../Glob_Controllers/head_adm.php');?>
	<link rel="stylesheet" href="../../css/style.css?ver=114">
</head>
<body id="body" class="<?=$_SESSION['assist']['bodyImg'];?>">
	<div id="content"> <!-- нужен что бы скрывать все для показа 3Д background: url('../picts/backgrounds/atmosphere.jpg') no-repeat no-scroll 0 0 rgba(0, 0, 0, 0); -->
	<?php include('../NavigationBar/NavBar.php');?>
	<div class="container" id="id_<?=$id;?>">
		<div class="row topName" id="topRow">

			<div class="col-xs-12 col-sm-4 noPaddingLR">
				<h4 class="text-primary">
					<span class="pull-left">
						<strong>Номер 3D: </strong>
						<span id="num3d" class="text-warning"><?=$row['number_3d'];?></span>
						<i> - <u id="modelType"><?=$row['model_type'];?></u></i>
						<br/>
						<strong> Фабричный Артикул: </strong>
						<span id="articl" class="text-warning"><?=$stillNo;?></span>
						<?=$vcEditbtn;?>
					</span>
				</h4>
			</div>
			<div class="col-xs-12 col-sm-4 noPaddingLR">
				<center>
					<a class="btn btn-sm btn-info" onclick="getPDF(<?=$id;?>,'passport');" role="button">
						<span class="glyphicon glyphicon-print"></span>
						<span> Пасспорт</span>
					</a>
					<a class="btn btn-sm btn-info" onclick="getPDF(<?=$id;?>,'runner');" role="button">
						<span class="glyphicon glyphicon-print"></span>
						<span> Бегунок</span>
					</a>
				</center>
			</div>
			<div class="col-xs-12 col-sm-4 noPaddingLR">
				<h4 class="text-primary">
					<span class="pull-right" id="complects">
						<strong>В Комплекте: </strong>
						<?=$complStr;?>
					</span>
				</h4>
		  </div>
		</div><!-- END top row-->

        <ul class="nav nav-tabs">
            <li role="presentation" class="active" title="Общая информация о изделии"><a href="#info" role="tab" data-toggle="tab">Информация</a></li>
            <li role="presentation" title="История статусов"><a href="#history" role="tab" data-toggle="tab">История</a></li>
        </ul>
        <div class="tab-content">
            <div role="tabpanel" class="tab-pane active in fade pt-1" id="info">
                <!-- images block start-->
                <div class="row" id="middleRow">
                    <div class="col-xs-12 col-sm-6" id="images_block" style="border-bottom: 1px solid #ded9d9;">
                        <div class="row">
                            <div class="col-xs-12">
                                <center class="image-zoom responsive mainImg">
                                    <div class="<?=$stat_class;?>" title="<?=$stat_title;?>">
                                        <span class="pull-left">&nbsp;&nbsp;</span>
                                        <span class="<?=$stat_glyphi;?> pull-left"></span>
                                        <span><?=$stat_name;?></span>
                                        <small class="pull-right" title="Дата последнего изменения статуса" style="cursor:default;"><?=$stat_date;?></small>
                                    </div>
                                    <img src="<?=$images['mainSrcImg'];?>" class="img-responsive image">
                                </center>
                                <?php for ( $i = 0; $i < count($labels); $i++ ) :?>
                                    <span title="<?=$labels[$i]['info']?>" class="label <?=$labels[$i]['class']?> lables-bottom pull-right">
								        <span class="glyphicon glyphicon-tag"></span>
								        <span><?=$labels[$i]['name'];?></span>
							        </span>
                                <?php endfor; ?>
                                <?=$button3D;?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12">
                            <?php for ( $i = 0; $i < count($images['dopImg']); $i++ ) :?>
                                <div class="col-xs-6 col-sm-4 image">
                                    <div class="ratio">
                                        <div class="ratio-inner ratio-4-3">
                                            <div class="ratio-content imageSmall">
                                                <img src="<?=$images['dopImg'][$i];?>" class="img-responsive image">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endfor; ?>
                            </div>
                        </div>
                    </div><!-- END images block-->

                    <div class="col-xs-12 col-sm-6" id="descr" style="border-bottom: 1px solid #ded9d9;">
                        <p style="padding-top:10px;" title="Список коллекций к которым относится изделие">
                            <i class="fas fa-gem"></i> Коллекции:
                            <strong>
                                <i>
                                    <?php $collLength = count($coll_id); $c = 1; ?>
                                    <?php foreach ( $coll_id as $coll ) : ?>
                                        <?php $c++; ?>
                                        <a href="../Main/controllers/setSort.php?sCollId=<?=$coll['id']?>" id="collection"><?=$coll['name']?></a>
                                        <?=$c <= $collLength ? "," : ""?>
                                    <?php endforeach;?>
                                </i>
                            </strong>
                        </p>
                        <div class="row">
                            <div class="col-xs-6">
                                <span class="pull-left"><span class="glyphicon glyphicon-user"></span> Автор: <strong><span><?=$row['author']?></span></span></strong>
                            </div>
                            <div class="col-xs-6">
                                <span class="pull-right"><span class="glyphicon glyphicon-user"></span> 3D модельер: <strong><span><?=$row['modeller3D']?></span></span></strong>
                            </div>
                        </div>
                        <hr />
                        <dl class="dl-horizontal">
                            <dt>Вид модели &#160;<span class="glyphicon glyphicon-eye-open"></span></dt>
                            <dd><?=$row['model_type'];?></dd>
                            <?=$srDt;?>
                            <dt>Металл &#160;<span class="glyphicons-cube" style="width:17px;height:17px;"></span></dt>
                            <dd><?=$str_mat;?></dd>
                            <dt>Покрытие &#160;<i class="fas fa-cube fasL" style="font-size: 100%;"></i></dt>
                            <dd><?=$str_Covering;?></dd>
                            <dt>Вес в 3D &#160;<span class="glyphicon glyphicon-scale"></span></dt>
                            <dd id="modelWeight"><?=$row['model_weight']." гр.";?></dd>
                            <?=$print_costDD;?>
                            <?php if ($ai_file) : ?>
                            <dt title="загрузить файл накладки">Накладка &#160;<span class="glyphicon glyphicon-floppy-save"></span></dt>
                            <dd>
                                <?php if ( is_string($ai_file) ) : ?>
                                    Нет
                                <?php endif;?>
                                <?php if ( is_array($ai_file) ) : ?>
                                    <a href="<?= _rootDIR_HTTP_.'Stock/'.$modelView->number_3d.'/'.$id.'/ai/'.$ai_file['name'] ?>">Скачать</a>
                                <?php endif;?>
                            </dd>
                            <?php endif; ?>
                        </dl>
                        <hr />
                        <h4 class="bg-info butt-inf Nit_gems">
                            <i class="far fa-gem"></i>
                            <span>Вставки 3D:</span>
                        </h4>
                        <table class="table text-muted table_gems">
                            <thead>
                            <tr>
                                <th>№</th><th>Размер</th><th>Кол-во</th><th>Огранка</th><th>Сырьё</th><th>Цвет</th>
                            </tr>
                            </thead>
                            <tbody class="tbody_gems">
                            <?php for ( $i = 0; $i < count($gemsTR); $i++ ) :?>
                                <tr>
                                    <td><?=$gemsTR[$i]['gem_num'];?></td>
                                    <td><?=$gemsTR[$i]['gem_size'];?></td>
                                    <td><?=$gemsTR[$i]['gem_value'];?></td>
                                    <td><?=$gemsTR[$i]['gem_cut'];?></td>
                                    <td><?=$gemsTR[$i]['gem_name'];?></td>
                                    <td><?=$gemsTR[$i]['gem_color'];?></td>
                                </tr>
                            <?php endfor; ?>
                            </tbody>
                        </table>
                        <h4 class="bg-info butt-inf Nit_vc_links">
                            <span class="glyphicon glyphicon-link"></span>
                            <span>Ссылки на другие артикулы:</span>
                        </h4>
                        <table class="table text-muted table_vc_links">
                            <thead>
                            <tr>
                                <th>№</th><th>Название</th><th>№3D/Арт.</th><th>Описание</th>
                            </tr>
                            </thead>
                            <tbody class="tbody_vc_links">
                            <?php for ( $i = 0; $i < count($dopVCTr); $i++ ) :?>
                                <tr>
                                    <td><?=$dopVCTr[$i]['vc_num'];?></td>
                                    <td><?=$dopVCTr[$i]['vc_names'];?></td>
                                    <td><?=$dopVCTr[$i]['vc_link'];?></td>
                                    <td><?=$dopVCTr[$i]['vc_descript'];?></td>
                                </tr>
                            <?php endfor; ?>
                            </tbody>
                        </table>
                    </div>
                </div><!-- end Middle Row-->
            </div>
            <div role="tabpanel" class="tab-pane in fade pt-1" id="history">
                <div class="row">
                    <div class="col-xs-6">
                        <div class="list-group">
                            <?php for ( $i = 0; $i < count($statuses); $i++ ) :?>
                                <a href="#" class="list-group-item" title="<?=$statuses[$i]['title']?>">
                                    <?=$statuses[$i]['date']." &#160;&#160;"?>
                                    <span class="glyphicon glyphicon-<?=$statuses[$i]['glyphi']?>"></span>
                                    <?=$statuses[$i]['status']." &#160;&#160; ( ".$statuses[$i]['name']." )"?>
                                </a>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div class="col-xs-6">
                    </div>
                </div>
            </div>
        </div>

        <div class="row" id="bottomRow">
			<?php if ( !empty($row['description']) ) :?>
              <div class="panel panel-default" style="margin-bottom:0px !important;">
                <div class="panel-heading">
                    <span class="glyphicon glyphicon-comment"></span>
                    <strong> Примечания:</strong>
                </div>
                <div style="margin:15px;"><?=$row['description'];?></div>
              </div><!-- panel end -->
			<?php endif; ?>

			<?php if ( !empty($row['mounting_descr']) ) :?>
                <br/>
                <div class="panel panel-default" style="margin-bottom:0px !important;">
                    <div class="panel-heading">
                        <span class="glyphicon glyphicon-comment"></span>
                        <strong style="color:red;"> Монтировщик отправил в ремонт по причине:</strong>
                        <span class="pull-right"><?=date_create( $row['status_date'] )->Format('d.m.Y');?></span>
                    </div>
                    <div style="margin:15px;"><?=$row['mounting_descr'];?></div>
                </div><!-- panel end -->
			<?php endif; ?>

			<?php while($repRow = mysqli_fetch_assoc($modelView->rep_Query)) :?>
                <br/>
                <div class="panel panel-danger repairs" style="margin-bottom: 0 !important;">
                    <div class="panel-heading">
                        <span class="glyphicon glyphicon-wrench" style="color:green;"></span>
                        <strong>
                            Ремонт №<span class="repairs_number"> <?=$repRow['rep_num'];?></span>
                            от - <span class="repairs_date"><?=date_create( $repRow['date'] )->Format('d.m.Y');?></span>
                        </strong>
                    </div>
                    <textarea readonly style="resize: none;" class="form-control repairs_descr" rows="3"><?=$repRow['repair_descr'];?></textarea>
                </div>
			<?php endwhile; ?>
			<div class="bg-info butt-inf">
				<small class="pull-left">
					<span title="Создатель">
						Добавил:&#160;<i><?=$row['creator_name'];?></i>
					</span>
					&mdash;
					<span title="Дата создания">
						<span class="glyphicon glyphicon-calendar"></span>
						<i><?=date_create( $row['date'] )->Format('d.m.Y');?></i>
					</span>
				</small>
				<span class="pull-right" title="">
					<span title="Понравилось">
						<button type="button" class="btn btn-link <?=$btnlikes;?> likeBtn"><span class="glyphicon glyphicon-thumbs-up"></span></button>
						<span>&#160;</span>
						<span id="numLikes"><?=$modelView->row['likes'];?></span>
					</span>
					<span>&#160;&#160;</span>
					<span title="Не понравилось">
						<button type="button" class="btn btn-link <?=$btnlikes;?> disLikeBtn"><span class="glyphicon glyphicon-thumbs-down"></span></button>
						<span>&#160;</span>
						<span id="numDisLikes"><?=$modelView->row['dislikes'];?></span>
					</span>
				</span> 
				<div class="clearfix"></div>
			</div>
			<div class="butt-inf">
				<a href="<?=$_SESSION['prevPage'];?>" class="btn btn-default">
					<span class="glyphicon glyphicon-triangle-left"></span> 
					Назад
				</a>
			   <?=$bottomBtns;?>
			</div>
        </div><!--END bottomRow-->
		
		<script defer src="js/imageViewer.js?ver=114"></script>
		<script defer src="js/show_pos_scrpt.js?ver=<?=time()?>"></script>
		<script defer src="../Main/js/main.js?ver=<?=time()?>"></script>
		<?=$dopBottomScripts;?>
		<?php include('../Glob_Controllers/bottomScripts_adm.php');?>
        
		<!-- lond cut div -->
		<div id="longTD" class="longTD hidden"></div>

		<?php include_once "includes/imageWrapper.php"; ?>
		<?php include_once "includes/mounting.php"; ?>
		<?php include_once "includes/forms.php"; ?>
		<?php include('../Glob_Controllers/includes/pushNotice.php');?>
		
		<?=$scriptsPN;?>

	</div><!--container-->
</div><!--content-->

		<?php include_once "includes/3DWievPanels.php"; ?>
		<?php include_once "includes/progressBar.php"; ?>
		<!-- image Box prev-->
		<img id="imageBoxPrev" style="max-height:250px; max-width:200px;" class="img-thumbnail hidden"/>

</body>
</html>
<?php
use models\User;

$session = $this->session->getAll();
$isView = true;
?>
<div class="row" id="middleRow">
    <!-- images block start-->
    <div class="col-xs-12 col-sm-6 pl-0 pr-1" id="images_block">

        <ul class="nav nav-tabs">
            <li role="presentation" class="active" title="Картинки 3Д модели"><a href="#images3d" role="tab" data-toggle="tab">Рендеры 3Д</a></li>
            <?php if ($button3D): ?>
            <li role="presentation" title="Доступен 3D просмотр">
                <a href="#" role="tab" id="butt3D" data-toggle="tab" ><span class="button-3D-pict"></span> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Просмотр</a>
                <form method="POST" id="extractForm" class="hidden">
                    <input type="hidden" name="zip_name" value="<?=$button3D?>" />
                    <input type="hidden" name="zip_id" value="<?=$id?>" />
                    <input type="hidden" name="zip_num3d" value="<?=$row['number_3d']?>" />
                    <input type="hidden" name="zipExtract" value="1" />
                </form>
                <form method="post" id="dellStlForm" class="hidden">
                    <input type="hidden" name="zipDelete" value="1" />
                </form>
            </li>
            <?php endif; ?>
        </ul>

        <div class="tab-content">
            <div role="tabpanel" class="tab-pane active in fade pt-1" id="images3d">
                <div class="row ml-1">
                    <div class="col-xs-12 pl-0">
                        <div class="panel mb-1">
                            <a id="saveMainIMG" title="Сохранить картинку" href="<?= $mainImg['src'] ?>" target="_blank" download="<?= $row['number_3d'] .'-'. $row['model_type'] ?>" class="btn btn-default absolute btnImgSave">
                                <span class="glyphicon glyphicon-floppy-disk"></span>
                            </a>
                            <div class="mainImage cursorLoupe" data-id="<?=$mainImg['id']?>" style="background-image: url(<?=$mainImg['src']?>);"></div>
                        </div>
                    </div>
                </div>
                <div class="row dopImages ml-1">
                    <?php foreach ( isset($images)?$images:[] as $image ) :?>
                        <?php $borderDopImg = isset($image['active']) ? 'border-primary-1': 'border-secondary-1' ?>
                        <div class="col-xs-4 col-sm-3 pl-0 pr-2 mb-1">
                            <div class="imageSmall cursorPointer border-radius-1 <?=$borderDopImg?> <?= isset($image['active']) ? 'activeImage':''?>" data-id="<?=$image['id']?>" style="background-image: url(<?= $setPrevImg($image) ?>); height: 10rem;"></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    </div>
    <!-- END images block-->


    <div class="col-xs-12 col-sm-6 pl-1 pr-0" id="descr">

        <ul class="nav nav-tabs">
            <li role="presentation" class="active" title="Общая информация о изделии"><a href="#info" role="tab" data-toggle="tab">Информация</a></li>
            <li role="presentation" title="История статусов"><a href="#history" role="tab" data-toggle="tab">История</a></li>
        </ul>

        <div class="tab-content">
            <div role="tabpanel" class="tab-pane active in fade pt-1" id="info">

                <div class="panel mb-1 descriptionPanel">
                    <?php if ( User::permission('paymentManager') && User::permission('artCouncil') && (int)$currentStatus['id'] === 35 ): // эскиз?>
                         <?php if ( !$isStatusPresentDesign ):?>
                            <button type="button" id="approveSketchBtn" data-toggle="modal" data-target="#approveModal" class="btn btn-primary border-secondary-2 textSizeMiddle btn-lg btn-block pt-6 pb-6 mt-1 mb-1">
                                <i class="fas fa-magic"></i>
                                <b>Утвердить эскиз</b>
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php // Стоит На проверке и есть утвержд. эскиз ?>
                    <?php if ( User::permission('MA_techJew') && in_array(39,User::getLocations()) && (int)$currentStatus['id'] === 1 ): ?>
                        <?php if ( !$isStatusPresentTechJew && $isStatusPresentDesign ): ?>
                        <button type="button" id="approve3DTechBtn" data-toggle="modal" data-target="#approveModal" class="btn btn-primary border-secondary-2 textSizeMiddle btn-lg btn-block pt-6 pb-6 mt-1 mb-1">
                            <span class="glyphicon glyphicon-education"></span>
                            <b>Подпись технолога</b>
                        </button>
                        <?php endif; ?>
                    <?php endif; ?>
                    <div class="panel-heading <?=$stat_class;?> cursorArrow mb-2" title="<?=$stat_title;?>"><span class="<?=$stat_glyphi?>"></span> <?=$stat_name;?><span title="Дата последнего изменения статуса"><?=$stat_date?" - " . $stat_date:''?></span></div>
                    <ul class="list-group">
                        <li class="list-group-item">
                            <span class="badge badge-lg" style="background-color: #a28d1a!important;" id="modelType"><?=$row['model_type']?></span>
                            <?php if (isset($row['vendor_code']) && !empty($row['vendor_code'])): ?>
                                <span class="badge badge-lg cursorPointer" id="articl" title="Скопировать" onclick="copyInnerHTMLToClipboard(this)"><?=$row['vendor_code']?></span>
                            <?php endif; ?>
                            <span class="badge badge-lg cursorPointer" id="num3d" title="Скопировать" onclick="copyInnerHTMLToClipboard(this)"><?=$row['number_3d']?></span>
                            <i class="fas fa-hashtag"></i> Номер 3D / Арт. / Вид:
                        </li>
                        <li class="list-group-item">
                            <?php foreach ( isset($coll_id)?$coll_id:[] as $coll ) : ?>
                                <span class="badge badge-lg" style="margin-bottom:2px; background-color: #41aa76!important;"><i><a style="color:white;" href="/main/?coll_show=<?=$coll['id']?>" id="collection"><?=$coll['name']?></a></i></span>
                            <?php endforeach;?>
                            <i class="fas fa-gem"></i> Коллекции:
							<div class="clearfix"></div>
                        </li>
                        <?php if ( !empty($complectes) ): ?>
                            <li class="list-group-item" id="complects">
                                <?php foreach ($complectes as $complect) : ?>
                                <span class="badge badge-lg" style="background-color: #4eb5b2!important;font-size: small!important;">
                                    <a style="color:white!important;" imgtoshow="<?= $complect['img_name'] ?>" href="/model-view/?id=<?=$complect['pos_id']?>"><?=$complect['model_type']?></a>
                                </span>
                                <?php endforeach;?>
                                <i class="fas fa-object-group"></i> В Комплекте:
                            </li>
                        <?php endif; ?>
                        <li class="list-group-item">
                            <span class="badge badge-lg"><?=$row['author']?></span>
                            <i class="fas fa-user-tie"></i> Автор:
                        </li>
                        <li class="list-group-item">
                            <span class="badge badge-lg"><?=$row['modeller3D']?></span>
                            <i class="fas fa-user-edit"></i> 3D модельер:
                        </li>
                        <?php if ( $row['jewelerName'] ): ?>
                        <li class="list-group-item">
                            <span class="badge badge-lg"><?=$row['jewelerName']?></span>
                            <i class="fas fa-user-cog"></i> Модельер-доработчик:
                        </li>
                        <?php endif;?>
                        <li class="list-group-item">
                            <span class="badge badge-lg"><?=$row['model_weight']." гр."?></span>
                            <span class="glyphicon glyphicon-scale"></span> Вес в 3D:
                        </li>
                        <?php if ( isset($row['size_range']) && !empty($row['size_range']) ) : ?>
                            <li class="list-group-item">
                                <span class="badge badge-lg"><?=$row['size_range']?></span>
                                <i class="fab fa-quinscape"></i> Размерный Ряд:
                            </li>
                        <?php endif; ?>
                        <?php if ( !empty($labels) ) : ?>
                            <li class="list-group-item text-right">
                                <span class="pull-left"><span class="glyphicon glyphicon-tags"></span>&nbsp;&nbsp;Метки:</span>
                                <?php foreach ( $labels as $label ) : ?>
                                    <span class="label <?=$label['class']?>" style="font-size: 100%!important;"><span class="glyphicon glyphicon-tag"></span> <?=$label['name']?></span>
                                <?php endforeach; ?>
                            </li>
                        <?php endif; ?>
                        <?php if ( trueIsset($descriptions??[]) ): ?>
                            <?php foreach ( $descriptions??[] as $description ) : ?>
                                <li class="list-group-item">
                                    <i class="far fa-comment-alt"></i><strong> Описание №<?=$description['num']?>: </strong>
                                    <textarea readonly="" class="br-0 cursorArrow" style="width: 100%; overflow: hidden; resize: none;"><?=$description['text'];?></textarea>
                                    <span class="badge" style="background-color: #a39e6d!important;">Добавлено: <?=$description['date']?> - <?=$description['userName']?></span>
                                    <p></p>
                                </li>
                            <?php endforeach;?>
                        <?php endif; ?>
                        <?php if ( !empty($row['description']) ) :?>
                            <li class="list-group-item">
                                <span class="glyphicon glyphicon-comment"></span><strong> Примечания:</strong> &nbsp;
                                <span><?=$row['description'];?></span>
                            </li>
                        <?php endif; ?>
                        <?php if ( isset($row['print_cost']) && !empty($row['print_cost']) && User::getAccess() > 0 ) : ?>
                            <li class="list-group-item">
                                <span class="badge badge-lg"><?=$row['print_cost']?></span>
                                <i class="fas fa-print"></i><span class="glyphicon glyphicon-usd"></span> Стоимость 3Д Печати:
                            </li>
                        <?php endif; ?>
                        <?php if ( isset($row['model_cost']) && !empty($row['model_cost']) && User::getAccess() > 0 ) : ?>
                            <li class="list-group-item">
                                <span class="badge badge-lg"><?=$row['model_cost']?></span>
                                <i class="fas fa-hammer"></i><span class="glyphicon glyphicon-usd"></span> Стоимость доработки:
                            </li>
                        <?php endif; ?>
                        <?php if (  trueIsset($ai_file) ) : ?>
                            <li class="list-group-item" title="загрузить файл накладки">
                                <span class="badge badge-lg"><a class="text-white" href="<?=_stockDIR_HTTP_.$row['number_3d'].'/'.$id.'/ai/'.$ai_file?>" download="<?='ai_'.$ai_file?>">Скачать</a></span>
                                <span class="glyphicon glyphicon-floppy-save"></span> AI Файл накладки:
                            </li>
                        <?php endif; ?>
                        <?php if (  trueIsset($stl_file) && User::permission('stl') ) : ?>
                            <li class="list-group-item" title="загрузить STL файл">
                                <span class="badge badge-lg"><a class="text-white" href="<?= _stockDIR_HTTP_.$row['number_3d'].'/'.$id.'/stl/'.$stl_file ?>" download="<?='stl_'.$stl_file?>">Скачать</a></span>
                                <span class="glyphicon glyphicon-floppy-save"></span> Stl Файл модели:
                            </li>
                        <?php endif; ?>
                        <?php if (  trueIsset($rhino_file) && User::permission('rhino3dm') ) : ?>
                            <li class="list-group-item" title="загрузить 3dm файл">
                                <span class="badge badge-lg"><a class="text-white" href="<?= _stockDIR_HTTP_.$row['number_3d'].'/'.$id.'/3dm/'.$rhino_file ?>" download="<?='3dm_'.$rhino_file?>">Скачать</a></span>
                                <span class="glyphicon glyphicon-floppy-save"></span> 3dm Файл модели:
                            </li>
                        <?php endif; ?>
                        <li class="list-group-item">
                            <span class="pull-left" title="Дата добавления модели в базу"><span class="glyphicon glyphicon-calendar"></span>&nbsp;&nbsp;Дата создания:</span>
                            <span title="Кто добавил" class="badge"><?=$row['creator_name'];?></span>
                            <span title="Дата создания" class="badge"><?=date_create( $row['date'] )->Format('d.m.Y');?></span>
                            <div class="clearfix"></div>
                        </li>
                        <?php if (  trueIsset($usedInModels) ) : ?>
                            <li class="list-group-item">
                                <span class="pull-left" title="Используется в моделях"><span class="glyphicon glyphicon-refresh"></span>&nbsp;&nbsp;Используется в моделях: &nbsp;&nbsp;</span>
                                <?php foreach ( $usedInModels as $usedInModel ) : ?>
                                    <span class="badge" style="background-color: #cbdfed!important; float: none;" >
                                        <i><a href="/model-view/?id=<?=$usedInModel['id']?>"><?= $usedInModel['number_3d'].($usedInModel['vendor_code']?' / '.$usedInModel['vendor_code']:'').' - '.$usedInModel['model_type'] ?></a></i>
                                    </span>
                                <?php endforeach;?>
                                <div class="clearfix"></div>
                            </li>
                        <?php endif; ?>
                        <li class="list-group-item">
                            <div class="btn-group">
                              <button type="button" class="btn btn-sm btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span ><span class="glyphicon glyphicon-print"></span>&nbsp;Печать <span class="caret"></span>
                              </button>
                              <ul class="dropdown-menu text-black">
                                <li><a onclick="getPDF('passport');" title="">Пасспорт</a></li>
                                <li><a onclick="getPDF('runner');" title="">Бегунок</a></li>
                                <li><a onclick="getPDF('both');" title="Пасспорт и Бегунок">П+Б</a></li>
                                <li><a onclick="getPDF('picture');" title="Печать выбранной картинки">Одна картинка</a></li>
                                <li><a onclick="getPDF('pictureAll');" title="Печать всех картинок">Все картинки</a></li>
                              </ul>
                            </div>
                            <?php if ( $editBtn ): ?>
                            <a href="/model-edit/?id=<?=$id?>" class="btn btn-sm btn-default pull-right" style="color: #0f0f0f!important;">
                                <span class="glyphicon glyphicon-pencil"></span>
                                Редактировать
                            </a>
                            <?php endif;?>
                            <div class="clearfix"></div>
                        </li>
                    </ul>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane in fade pt-1" id="history">
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
        </div>
    </div>

    <div class="col-xs-12 col-sm-12 col-lg-6 pl-1 pr-0">
        <div class="panel mb-1 panel-success">
            <div class="panel-heading"><i class="fab fa-codepen"></i> <b>Детали / Материалы:</b></div>
            <div class="panel-body p0">
                <div class="row pl-3 pr-3">
                    <?php foreach ( $matsCovers??[] as $material ) : ?>
                        <div class="col-xs-12 col-sm-6 p1">
                            <ul class="list-group mb-0 cursorArrow">
                                <?php if ( ($material['count']??'') || ($material['part']??'') ): ?>
                                    <li class="list-group-item list-group-item-danger p1">
                                        <span class="badge"><?= ($material['count']??'') ? $material['count']." шт." : "" ?></span>
                                        <b><?=($material['part']??'') ? $material['part'] : "&nbsp;"?></b>
                                    </li>
                                <?php endif; ?>
                                <li title="Материал изделия" class="list-group-item p1">
                                    <span class="badge"><?=($material['probe']??'') ? $material['probe'] ."&deg;" : ""?></span>
                                    <span class="badge"><?=$material['metalColor']??''?></span>
                                    <span class="badge"><?=$material['type']??''?></span>
                                    <small>Метал:</small>
                                </li>
                                <?php if ( $material['covering'] || $material['area'] || $material['covColor'] || $material['handling'] ): ?>
                                    <li class="list-group-item list-group-item-danger" style="padding: 1px!important;"></li>
                                <?php endif; ?>
                                <?php if ( $material['covering']??'' ): ?>
                                    <li title="Покрытие" class="list-group-item p1">
                                        <span class="badge"><?=$material['covering']?></span>
                                        <?php $isLongStr = $this->cutLongNames($material['covering'], $this->isMobile?18:20, true) ?>
                                        <?= $isLongStr ? "&nbsp;" : "Покрытие" ?>
                                    </li>
                                <?php endif; ?>
                                <?php if ( $material['area']??'' ): ?>
                                    <li title="Площадь" class="list-group-item p1">
                                        <span class="badge"><?=$material['area']?></span>
                                        <?php $isLongStr = $this->cutLongNames($material['area'], $this->isMobile?18:20, true) ?>
                                        <?= $isLongStr ? "&nbsp;" : "Площадь" ?>
                                    </li>
                                <?php endif; ?>
                                <?php if ( $material['covColor']??'' ): ?>
                                    <li title="Цвет Покрытия" class="list-group-item p1">
                                        <span class="badge"><?=$material['covColor']?></span>
                                        <?php $isLongStr = $this->cutLongNames($material['covColor'], $this->isMobile?18:20, true) ?>
                                        <?= $isLongStr ? "&nbsp;" : "Цвет" ?>
                                    </li>
                                <?php endif; ?>
                                <?php if ( $material['handling']??'' ): ?>
                                    <li title="Обработка" class="list-group-item p1">
                                        <span class="badge"><?=$material['handling']?></span>
                                        <?php $isLongStr = $this->cutLongNames($material['handling'], $this->isMobile?18:20, true) ?>
                                        <?= $isLongStr ? "&nbsp;" : "Обработка" ?>
                                    </li>
                                <?php endif; ?>
                                <li class="list-group-item list-group-item-danger" style="padding: 1px!important;"></li>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ( !empty($gemsTR) ) : ?>
        <div class="col-xs-12 col-sm-12 col-lg-6 pl-1 pr-0">
            <div class="panel mb-1 panel-info">
                <div class="panel-heading"><i class="far fa-gem"></i> <b>Вставки 3D:</b></div>
                <div class="panel-body p0">
                    <div class="row pl-3 pr-3">
                        <?php foreach ( $gemsTR??[] as $gem ) : ?>
                            <div class="col-xs-12 col-sm-4 p1">
                                <ul class="list-group mb-0 cursorArrow">
                                    <li title="Сырье" class="list-group-item list-group-item-warning p1">
                                        <span class="badge"><?=$gem['gem_name']?></span>
                                        <?php $isLongStr = $this->cutLongNames($gem['gem_name'], $this->isMobile?12:18, true) ?>
                                        <?= $isLongStr ? "&nbsp;" : "Сырье" ?>
                                    </li>
                                    <li title="Размер" class="list-group-item  p1">
                                        <span class="badge"><?=$gem['gem_size']?></span>
                                        <?php $isLongStr = $this->cutLongNames($gem['gem_size'], $this->isMobile?12:18, true) ?>
                                        <?= $isLongStr ? "&nbsp;" : "Размер" ?>
                                    </li>
                                    <li title="Кол-во" class="list-group-item p1">
                                        <span class="badge"><?=$gem['gem_value']?></span>
                                        Кол-во
                                    </li>
                                    <li title="Огранка" class="list-group-item p1">
                                        <span class="badge"><?=$gem['gem_cut']?></span>
                                        <?php $isLongStr = $this->cutLongNames($gem['gem_cut'], $this->isMobile?12:18, true) ?>
                                        <?= $isLongStr ? "&nbsp;" : "Огранка" ?>
                                    </li>
                                    <li title="Цвет" class="list-group-item p1">
                                        <span class="badge"><?=$gem['gem_color']?></span>
                                        <?php $isLongStr = $this->cutLongNames($gem['gem_color'], $this->isMobile?11:19, true) ?>
                                        <?= $isLongStr ? "&nbsp;" : "Цвет" ?>
                                    </li>
                                    <li class="list-group-item list-group-item-info" style="padding: 1px!important;"></li>
                                </ul>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>


    <?php if ( !empty($dopVCTr) ) : ?>
        <div class="col-xs-12 col-sm-12 col-lg-6 pl-1 pr-0">
            <div class="panel mb-1 panel-warning">
                <div class="panel-heading"><span class="glyphicon glyphicon-link"></span> <b>Ссылки на другие артикулы:</b></div>
                <div class="panel-body p0">
                    <div class="row pl-3 pr-3 table_vc_links">
                        <?php foreach ( $dopVCTr??[] as $vcLink ) : ?>
                            <div class="col-xs-12 col-sm-6 p1">
                                <ul class="list-group mb-0 cursorArrow">
                                    <li title="Вид модели" class="list-group-item p1">
                                        <span class="badge"><?=$vcLink['vc_names']?></span>
                                        <?php $isLongStr = $this->cutLongNames($vcLink['vc_names'], $this->isMobile?18:20, true) ?>
                                        <?= $isLongStr ? "&nbsp;" : "Вид" ?>
                                    </li>
                                    <li title="№3D/Арт" class="list-group-item p1">
                                        <span class="badge" style="background-color: #265a88!important;"><?=$vcLink['vc_link']?></span>
                                        №3D/Арт
                                    </li>
                                    <?php if ( $vcLink['vc_descript'] ): ?>
                                        <li title="Описание" class="list-group-item p1">
                                            <?=$vcLink['vc_descript']?>
                                        </li>
                                    <?php endif; ?>
                                    <li class="list-group-item list-group-item-warning" style="padding: 1px!important;"></li>
                                </ul>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php foreach ( isset($repairs)?$repairs:[] as $repair ): ?>
        <?php if ( empty($repair['repair_descr']) ) continue; ?>
        <?php $whichRepair = $repair['which'] ? true : false ?>
        <div class="col-xs-12 col-sm-12 col-lg-6 pl-1 pr-0">
            <?php require _WEB_VIEWS_."add-edit/protoRepair.php"?>
        </div>
    <?php endforeach; ?>
</div>

<!-- lond cut div -->
<div id="longTD" class="longTD hidden"></div>
<img src="" id="imageBoxPrev" style="max-height:250px; max-width:200px;" class="imgPrev-thumbnail hidden"/>
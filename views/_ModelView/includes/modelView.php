<?php
use Views\_Globals\Models\User;
$session = $this->session->getAll();
$isView = true;
?>
<div class="row" id="middleRow">
    <!-- images block start-->
    <div class="col-xs-12 col-sm-6 pl-0 pr-1" id="images_block">

        <ul class="nav nav-tabs" style="margin-left: -15px;">
            <li role="presentation" class="active" title="Картинки 3Д модели"><a href="#images3d" role="tab" data-toggle="tab">Рендеры 3Д</a></li>
            <? if ($button3D): ?>
            <li role="presentation" title="Доступен 3D просмотр">
                <a href="#" role="tab" id="butt3D" data-toggle="tab" ><span class="button-3D-pict"></span> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Просмотр</a>
                <form method="POST" id="extractForm" class="hidden">
                    <input type="hidden" name="zip_name" value="<?=$button3D?>" />
                    <input type="hidden" name="zip_id" value="<?=$row['id']?>" />
                    <input type="hidden" name="zip_num3d" value="<?=$row['number_3d']?>" />
                    <input type="hidden" name="zipExtract" value="1" />
                </form>
                <form method="post" id="dellStlForm" class="hidden">
                    <input type="hidden" name="zipDelete" value="1" />
                </form>
            </li>
            <? endif; ?>
        </ul>

        <div class="tab-content">
            <div role="tabpanel" class="tab-pane active in fade pt-1" id="images3d">
                <div class="row">
                    <div class="col-xs-12 pl-0">
                        <div class="panel mb-1">
                            <a id="saveMainIMG" title="Сохранить картинку" href="<?= $mainImg['src'] ?>" target="_blank" download="<?= $row['number_3d'] .'-'. $row['model_type'] ?>" class="btn btn-default absolute btnImgSave">
                                <span class="glyphicon glyphicon-floppy-disk"></span>
                            </a>
                            <div class="mainImage cursorLoupe" data-id="<?=$mainImg['id']?>" style="background-image: url(<?=$mainImg['src']?>);"></div>
                        </div>
                    </div>
                </div>
                <div class="row dopImages">
                    <? foreach ( isset($images)?$images:[] as $image ) :?>
                        <?php $borderDopImg = isset($image['active']) ? 'border-primary-1': 'border-secondary-1' ?>
                        <div class="col-xs-6 col-sm-3 pl-0 pr-2 mb-1">
                            <div class="imageSmall cursorPointer border-radius-1 <?=$borderDopImg?> <?= isset($image['active']) ? 'activeImage':''?>" data-id="<?=$image['id']?>" style="background-image: url(<?= $setPrevImg($image) ?>); height: 10rem;"></div>
                        </div>
                    <? endforeach; ?>
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
                            <span class="badge badge-lg" id="modelType"><?=$row['model_type']?></span>
                            <?php if (isset($row['vendor_code']) && !empty($row['vendor_code'])): ?>
                                <span class="badge badge-lg cursorPointer" id="articl" title="Скопировать" onclick="copyInnerHTMLToClipboard(this)"><?=$row['vendor_code']?></span>
                            <?php endif; ?>
                            <span class="badge badge-lg cursorPointer" id="num3d" title="Скопировать" onclick="copyInnerHTMLToClipboard(this)"><?=$row['number_3d']?></span>
                            <i class="fas fa-hashtag"></i> Номер 3D / Арт. / Вид:
                        </li>
                        <li class="list-group-item">
                            <?php foreach ( isset($coll_id)?$coll_id:[] as $coll ) : ?>
                                <span class="badge badge-lg" style="margin-bottom:2px;"><i><a style="color:white;" href="/main/?coll_show=<?=$coll['id']?>" id="collection"><?=$coll['name']?></a></i></span>
                            <?php endforeach;?>
                            <i class="fas fa-gem"></i> Коллекции:
							<div class="clearfix"></div>
                        </li>
                        <?php if ( !empty($complectes) ): ?>
                            <li class="list-group-item" id="complects">
                                <?php foreach ($complectes as $complect) : ?>
                                <span class="badge badge-lg">
                                    <a style="color:white!important;" imgtoshow="<?= $complect['img_name'] ?>" href="/model-view/?id=<?=$complect['id']?>"><?=$complect['model_type']?></a>
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
                        <?endif;?>
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
                                <span class="badge badge-lg"><a class="text-white" href="<?=_stockDIR_HTTP_.$row['number_3d'].'/'.$id.'/ai/'.$ai_file['name']?>" download="<?='ai_'.$ai_file['name']?>">Скачать</a></span>
                                <span class="glyphicon glyphicon-floppy-save"></span> AI Файл накладки:
                            </li>
                        <?php endif; ?>
                        <?php if (  trueIsset($stl_file) && User::permission('stl') ) : ?>
                            <li class="list-group-item" title="загрузить STL файл">
                                <span class="badge badge-lg"><a class="text-white" href="<?= _stockDIR_HTTP_.$row['number_3d'].'/'.$id.'/stl/'.$stl_file['stl_name'] ?>" download="<?='stl_'.$stl_file['stl_name']?>">Скачать</a></span>
                                <span class="glyphicon glyphicon-floppy-save"></span> Stl Файл модели:
                            </li>
                        <?php endif; ?>
                        <?php if (  trueIsset($rhino_file) && User::permission('rhino3dm') ) : ?>
                            <li class="list-group-item" title="загрузить 3dm файл">
                                <span class="badge badge-lg"><a class="text-white" href="<?= _stockDIR_HTTP_.$row['number_3d'].'/'.$id.'/3dm/'.$rhino_file['name'] ?>" download="<?='3dm_'.$rhino_file['name']?>">Скачать</a></span>
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
                            <? if ( $editBtn ): ?>
                            <a href="/add-edit/?id=<?=$id?>&component=2" class="btn btn-sm btn-default pull-right" style="color: #0f0f0f!important;">
                                <span class="glyphicon glyphicon-pencil"></span>
                                Редактировать
                            </a>
                            <?endif;?>
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
            <div class="panel-heading"><i class="fab fa-codepen"></i> <b>Материалы:</b></div>
            <ul class="list-group">
                <?php foreach ( isset($matsCovers)?$matsCovers:[] as $material ) : ?>
                <li class="list-group-item brb-2-secondary bg-info-light">
                    <span class="badge badge-lg"><?=$material['probe'] ? $material['probe'] ."&deg;" : ""?></span>
                    <span class="badge badge-lg"><?=$material['metalColor']?></span>
                    <span class="badge badge-lg"><?=$material['type']?></span>
                    <b><?=$material['part'] ? $material['part']." - " :"&#160;"?></b> <i><?=$material['count'] ? $material['count']." шт." : "" ?></i>
                </li>
                <li class="list-group-item p0">
                    <?php if ( !empty($material['covering']) || !empty($material['area']) || !empty($material['covColor']) || !empty($material['handling']) ): ?>
                        <table class="table table-condensed table-striped mb-0 text-small text-center">
                            <thead>
                                <tr class="text-muted">
                                    <td>Покрытие</td><td>Площадь</td><td>Цвет Покрытия</td><td>Обработка</td>
                                </tr>
                            </thead>
                            <tbody class="brb-2-success">
                                <tr>
                                    <th class="border-right-1 text-center"><?=$material['covering'];?></th>
                                    <th class="border-right-1 text-center"><?=$material['area'];?></th>
                                    <th class="border-right-1 text-center"><?=$material['covColor'];?></th>
                                    <th class="text-center"><?=$material['handling'];?></th>
                                </tr>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <?php if ( !empty($gemsTR) ) : ?>
        <div class="col-xs-12 col-sm-12 col-lg-6 pl-1 pr-0">
            <div class="panel mb-1 panel-info">
                <div class="panel-heading"><i class="far fa-gem"></i> <b>Вставки 3D:</b></div>
                <table class="table table-condensed mb-0 text-small text-center table_gems">
                    <thead>
                    <tr class="text-muted">
                        <td>Размер</td><td>Кол-во</td><td>Огранка</td><td>Сырьё</td><td>Цвет</td>
                    </tr>
                    </thead>
                    <tbody class="tbody_gems">
                    <?php foreach ( $gemsTR?:[] as $gem ) : ?>
                        <tr class="text-bold">
                            <td><?=$gem['gem_size']?></td>
                            <td><?=$gem['gem_value']?></td>
                            <td><?=$gem['gem_cut']?></td>
                            <?php $isLongStr = $this->cutLongNames($gem['gem_name'], 20, true) ?>
                            <td class="<?= $isLongStr?'bg-info cursorPointer':''?>">
                                <div data-toggle="<?= $isLongStr?'tooltip':''?>" title="<?= $isLongStr?$gem['gem_name']:''?>"><?= $this->cutLongNames($gem['gem_name']) ?></div>
                            </td>
                            <?php $isLongStr = $this->cutLongNames($gem['gem_color'], 20, true) ?>
                            <td class="<?= $isLongStr?'bg-info cursorPointer':''?>">
                                <div data-toggle="<?= $isLongStr?'tooltip':''?>" title="<?= $isLongStr?$gem['gem_color']:''?>"><?= $this->cutLongNames($gem['gem_color']) ?></div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
    <?php if ( !empty($dopVCTr) ) : ?>
        <div class="col-xs-12 col-sm-12 col-lg-6 pl-1 pr-0">
            <div class="panel mb-1 panel-warning">
                <div class="panel-heading"><span class="glyphicon glyphicon-link"></span> <b>Ссылки на другие артикулы:</b></div>
                <table class="table table-condensed mb-0 text-small text-center table_gems table_vc_links">
                    <thead>
                    <tr class="text-muted">
                        <td>Название</td><td>№3D/Арт.</td><td>Описание</td>
                    </tr>
                    </thead>
                    <tbody class="tbody_vc_links">
                    <?php foreach ( $dopVCTr?:[] as $vcLink  ) : ?>
                        <tr class="text-bold">
                            <?php $isLongStr = $this->cutLongNames($vcLink['vc_names'], 20, true) ?>
                            <td class="<?= $isLongStr?'bg-info cursorPointer':''?>">
                                <div data-toggle="<?= $isLongStr?'tooltip':''?>" title="<?= $isLongStr ? $vcLink['vc_names']:''?>"><?= $this->cutLongNames($vcLink['vc_names']) ?></div>
                            </td>
                            <td><?= $vcLink['vc_link'] ?></td>
                            <?php $isLongStr = $this->cutLongNames($vcLink['vc_descript'], 20, true) ?>
                            <td class="<?= $isLongStr?'bg-info cursorPointer':''?>">
                                <div data-toggle="<?= $isLongStr?'tooltip':''?>" title="<?= $isLongStr ? $vcLink['vc_descript']:''?>"><?= $this->cutLongNames($vcLink['vc_descript']) ?></div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
    <?php foreach ( isset($repairs)?$repairs:[] as $repair ): ?>
        <?php if ( empty($repair['repair_descr']) ) continue; ?>
        <? $whichRepair = $repair['which'] ? true : false ?>
        <div class="col-xs-12 col-sm-12 col-lg-6 pl-1 pr-0">
            <?require _viewsDIR_ . "_AddEdit/includes/protoRepair.php"?>
        </div>
    <? endforeach; ?>
</div>

<!-- lond cut div -->
<div id="longTD" class="longTD hidden"></div>
<img src="" id="imageBoxPrev" style="max-height:250px; max-width:200px;" class="imgPrev-thumbnail hidden"/>
<?php
    switch ( $switchTableRow )
    {
        case "collection": //прототип строки Коллекций
?>
        <tr <?php if ( !isset($collection) ) echo 'class="hidden protoRow" id="protoCollectionRow"'; ?> >
            <td style="width: 30px"><?=++$i?></td>
            <td><?php require 'collections_input.php' ?></td>
            <td style="width:100px;">
                <button class="btn btn-sm btn-default" type="button" onclick="deleteRow(this);" title="удалить строку">
                    <span class="glyphicon glyphicon-trash"></span>
                </button>
            </td>
        </tr>
<?php
        break;
        case "dopVC": //прототип строки доп. артикулов
?>
        <tr <?php if ( !isset($dopVc) ) echo 'class="hidden protoRow" id="protoArticlRow"'; ?> >
            <td><?= ++$vcI ?></td>
            <td>
                <input type="hidden" class="rowID" name="vc_links[id][]" value="<?=$dopVc['id']?>">
                <div class="input-group">
                    <input type="text" class="form-control" name="vc_links[vc_names][]" value="<?=$dopVc['vc_names'];?>"/>
                    <div class="input-group-btn">
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-right">
                            <?=$vc_namesLI;?>
                        </ul>
                    </div>
                </div>
            </td>
            <td>
                <div class="input-group" >
                    <input type="text" class="form-control" name="vc_links[vc_3dnum][]" value="<?=$dopVc['vc_3dnum'];?>">
                    <div class="input-group-btn">
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-right">
                            <?= isset($vcI) ? $num3DVC_LI[$vcI-1] : "" ?>
                        </ul>
                    </div>
                </div>
            </td>
            <td>
                <input type="text" class="form-control" name="vc_links[descript][]" value="<?=$dopVc['descript'];?>">
            </td>
            <td>
                <button class="btn btn-sm btn-default" type="button" onclick="duplicateRowNew(this);" title="дублировать строку">
                    <span class="glyphicon glyphicon-duplicate"></span>
                </button>
                <button class="btn btn-sm btn-default" type="button" onclick="deleteRowNew(this);" title="удалить строку">
                    <span class="glyphicon glyphicon-trash"></span>
                </button>
            </td>
        </tr>
<?php
        break;
        case "gems": //прототип строки камней
?>
            <tr <?= !isset($gem) ? 'class="hidden protoRow" id="protoGemRow"':'' ?> >
                <td><?= ++$c ?></td>
                <td>
                    <input type="hidden" class="rowID" name="gems[id][]" value="<?=$gem['id']?>">
                    <?php include('gems_input.php'); ?>
                </td>
                <td>
                    <?php include('gems_cut_input.php'); ?>
                </td>
                <td>
                    <?php include('gems_diametr_input.php'); ?>
                </td>
                <td>
                    <input type="number" min="1" class="form-control gems_value_input" name="gems[value][]" value="<?=$gem['value'];?>">
                </td>
                <td>
                    <?php include('gems_color_input.php'); ?>
                </td>
                <td style="width:100px;">
                    <button class="btn btn-sm btn-default" type="button" onclick="duplicateRowNew(this);" title="дублировать строку">
                        <span class="glyphicon glyphicon-duplicate"></span>
                    </button>
                    <button class="btn btn-sm btn-default" type="button" onclick="deleteRowNew(this);" title="удалить строку">
                        <span class="glyphicon glyphicon-trash"></span>
                    </button>
                </td>
            </tr>
<?php
        break;
        case 'materialsFull': //прототип строки материалов
?>
            <tr <?= !isset($materialRow) ? 'class="hidden protoRow" id="protoMaterialsRow"':'' ?>>
                <td>
                    <div class="input-group input-group-sm">
                        <input type="hidden" class="rowID" name="mats[id][]" value="<?=$materialRow['id']?>">
                        <input type="text" class="form-control" name="mats[part][]" value="<?=$materialRow['part']?>">
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <?=$modTypeLi?>
                            </ul>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" name="mats[type][]" value="<?=$materialRow['type']?>">
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <?php foreach ( $materialsData['names']?:[] as $type ) : ?>
                                    <li style="position:relative;">
                                        <a elemToAdd><?=$type?></a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" name="mats[probe][]" value="<?=$materialRow['probe']?>">
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <?php foreach ( $materialsData['probes']?:[] as $probes ) : ?>
                                    <?php foreach ( $probes as $probe ) : ?>
                                        <li style="position:relative;">
                                            <a elemToAdd><?=$probe?></a>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </td>
                <td class="brr-2-secondary">
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" name="mats[metalColor][]" value="<?=$materialRow['metalColor']?>">
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <?php foreach ( $materialsData['colors']?:[] as $color ) : ?>
                                    <li style="position:relative;">
                                        <a elemToAdd><?=$color?></a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" name="mats[covering][]" value="<?=$materialRow['covering']?>">
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <?php foreach ( $coveringsData['names']?:[] as $type ) : ?>
                                    <li style="position:relative;">
                                        <a elemToAdd><?=$type?></a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" name="mats[area][]" value="<?=$materialRow['area']?>">
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <?php foreach ( $coveringsData['areas']?:[] as $area ) : ?>
                                    <li style="position:relative;">
                                        <a elemToAdd><?=$area?></a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" name="mats[covColor][]" value="<?=$materialRow['covColor']?>">
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <?php foreach ( $materialsData['colors']?:[] as $color ) : ?>
                                    <li style="position:relative;">
                                        <a elemToAdd><?=$color?></a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" name="mats[handling][]" value="<?=$materialRow['handling']?>">
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <?php foreach ( $handlingsData?:[] as $type ) : ?>
                                    <li style="position:relative;">
                                        <a elemToAdd><?=$type['name']?></a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </td>
                <td>
                    <input type="number" min="1" max="999" step="1" class="form-control input-sm" name="mats[count][]" value="<?=$materialRow['count'] ?? 1?>">
                </td>
                <td style="width:80px;">
                    <button class="btn btn-sm btn-default" type="button" onclick="duplicateRowNew(this);" title="дублировать строку">
                        <span class="glyphicon glyphicon-duplicate"></span>
                    </button>
                    <button class="btn btn-sm btn-default" type="button" onclick="deleteRowNew(this);" title="удалить строку">
                        <span class="glyphicon glyphicon-trash"></span>
                    </button>
                </td>
            </tr>
<?php
            break;
        case 'repair':
?>
            <div id="protoRepair" class="panel panel-default repair new hidden">
                <div class="panel-heading cursorPointer">
                    <i class="fas "></i>
                    <strong>
                        <span class="repairs_name"></span>
                        <span class="repairs_number"></span>
                        от - <span class="repairs_date"></span>
                    </strong>
                    <button class="btn btn-sm btn-danger pull-right removeRepair" style="top:-5px !important; position:relative;" type="button" title="Удалить Ремонт">
                        <span class="glyphicon glyphicon-remove"></span>
                    </button>
                </div>
                <div id="" class="panel-collapse collapse" role="tabpanel" aria-labelledby="" aria-expanded="false" style="height: 0;">
                    <ul class="list-group">
                        <li class="list-group-item list-group-item-info text-center">
                            <i class="far fa-paper-plane"></i> <b><i>Отправитель</i></b>
                        </li>
                    </ul>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-xs-4">
                                <label for="sender_3dRep" title=""><span class="glyphicon glyphicon-user"></span> Технолог (кто отправил в ремонт):</label>
                                <div class="input-group">
                                    <input required type="text" title="Технолог" class="form-control sender" name="" value="">
                                    <div class="input-group-btn">
                                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-right">
                                            <li><a elemtoadd="">Дзюба В.М.</a></li>
                                            <li><a elemtoadd="">Занин В.А.</a></li>
                                            <li><a elemtoadd="">Бондаренко А.</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-4">
                                <label for="toWhom_3dRep" title=""><span class="glyphicon glyphicon-user"></span> Мастер (кто будет делать):</label>
                                <div class="input-group">
                                    <input required type="text" title="Мастер" class="form-control toWhom" name="" value="">
                                    <div class="input-group-btn">
                                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-right toWhomList">
                                            <?=$masterLI?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-12">
                                <label for="repairs_descr_need" class=""><span class="glyphicon glyphicon-comment"></span> Причина ремонта (что нужно сделать): </label>
                                <textarea class="form-control repairs_descr_need" title="Причина ремонта" required rows="2" name=""></textarea>
                                <input type="hidden" class="repairs_id"  name="" value=""/>
                                <input type="hidden" class="repairs_num" name="" value=""/>
                                <input type="hidden" class="repairs_which" name="" value=""/>
                            </div>
                        </div>
                    </div>
                    <ul class="list-group">
                        <li class="list-group-item pt-0 list-group-item-success"></li>
                    </ul>
                    <div class="panel-footer">
                        <div class="row">
                            <div class="col-xs-12 col-sm-2">
                                <span class="pull-right pt-1">
                                    <span class="glyphicon glyphicon-ok"></span> <b><i>Статус ремонта: </i></b>
                                </span>
                            </div>
                            <div class="col-xs-12 col-sm-10">
                                <select class="form-control repairStatus" name="">
                                    <option data-repairFor="id_repair" selected value="1" title="Новый ремонт. Недавно создан.">Новый</option>
                                    <option data-repairFor="id_repair"  value="2" title="Создан. Ожидает принятия в работу.">Ожидает принятия</option>
                                    <option data-repairFor="id_repair"  value="3" title="Принят в работу. Над ним сейчас трудится мастер">В работе</option>
                                    <option data-repairFor="id_repair"  value="4" title="Ремонт завершен">Завершен</option>
                                </select>
                                <input type="hidden" class="form-control statusDate hidden" name="" value="">
                                <input type="hidden" class="form-control date hidden" name="" value="">
                                <input type="hidden" class="form-control posID hidden" name="" value="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
<?php
            break;
        case 'dropImage':
?>
            <div class="col-xs-6 col-sm-3 col-md-2 <?=$protoImgRow?'hidden':'image_row'?>" <?=$protoImgRow ? 'id="proto_image_row"': ''?> >
                <div class="ratio img-thumbnail">
                    <div class="ratio-inner ratio-4-3">
                        <div class="ratio-content">
                            <img src="<?=$protoImgRow ? '': $setPrevImg($image) ?>" class="imgThumbs" />
                        </div>
                        <div class="img_dell">
                            <?php if ( !$protoImgRow && $component === 3 ): $onClk = "dellImgPrew(this)";  endif; ?>
                            <?php if ( !$protoImgRow && $component === 2 ): $onClk = "dell_fromServ($id, '{$image['imgName']}', 'image', false, this)";  endif; ?>
                            <button class="btn btn-default" type="button" onclick="<?= $onClk ?>" >
                                <span class="glyphicon glyphicon-remove"></span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="img_inputs">
                    <input type="hidden" class="rowID" <?=$protoImgRow ? '': 'name="image[id][]"'?> value="<?=!$protoImgRow && $component === 3 ? '': $image['id']?>">
                    <select class="form-control input-sm" <?=$protoImgRow ? '': 'name="image[imgFor][]" onchange="handlerFiles.onSelect(this)"'?>>
                        <?php $statusImgArray = $protoImgRow ? $dataArrays : $image ?>
                        <?php foreach ( $statusImgArray['imgStat']??[] as $statusImg ): ?>
                            <option <?=(int)$statusImg['selected'] === 1 ?'selected':''?> data-imgFor="<?=$statusImg['id']?>" value="<?=$statusImg['id']?>" title="<?=$statusImg['title']?>"><?=$statusImg['name']?></option>
                        <?php endforeach; ?>
                    </select>
                    <? if ( !$protoImgRow && $component === 3) : ?>
                        <input type="hidden" name="image[img_name][sketch]" value="<?=$row['number_3d'].'#'.$row['id'].'#'.$image['imgName']?>">
                    <?endif;?>
                </div>
            </div>
<?php
            break;
        case 'notes':
?>
            <div class="panel panel-default mb-2 <?= trueIsset($note??[]) ? 'model-note':'proto-note hidden' ?>">
                <div class="panel-heading">
                    <i class="far fa-comment-alt"></i>
                    <strong> Описание №<span class="note-num"><?=$note['num']?></span>: </strong>
                    <button class="btn btn-sm btn-default pull-right remove-note" onclick="removeNote(this)" style="top:-5px !important; position:relative;" type="button" title="Удалить описание">
                        <span class="glyphicon glyphicon-remove"></span>
                    </button>
                    <?php if ( $note??'' ) :?>
                        <span class="pull-right">Добавлено: <?=$note['date']?> - <?=$note['userName']?>&nbsp;&nbsp; </span>
                    <?endif;?>
                </div>
                <input type="hidden" class="note_id" name="notes[id][]" value="<?=$note['id']?>">
                <input type="hidden" class="note_num" name="notes[num][]" value="<?=$note['num']?>">
                <textarea class="form-control note-text" rows="2" name="notes[text][]"><?=$note['text']?></textarea>
            </div>
<?php
            break;
    }
<div id="<?=$row['id'] ?>" class="prj col-xs-6 col-sm-4 col-md-<?=$columns?> col-lg-<?=$columnsLG?>">
    <div class="thumbnail prj-thumbnail mb-1 cursorPointer">
        <div class="ratio">
            <div class="pb-1 relative" style="width: 390%;" title="Дата создания: <?=date_create( $row['date'] )->Format('d.m.Y'); ?>">
                <a style="text-decoration: none; background-color: #ffffff; padding: 2px;" href="/model-view/?id=<?=$row['id'] ?>">
                    <b><?= $row['number_3d'].$vc_show ?></b>
                </a>
            </div>
            <div class="ratio-inner ratio-4-3">
                <div class="ratio-content">
                    <div class="main_hot">
                        <?php if ( count($labels) ): ?>
                            <div class="all-model-labels">
                                <?php foreach ( $labels as $label ):?>
                                    <div class="label-main">
                                        <span title="<?=$label['info'] ?>" class="label <?=$label['class'] ?>">
                                            <span class="glyphicon glyphicon-tag"></span>&nbsp;&nbsp;<?=$label['name'] ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <? if ( $status['stat_name'] ):?>
                        <? if ( $status['glyphi'] == 'glyphicons-ring'): ?>
                            <span class="<?=$status['glyphi'] ?>"></span>
                        <? endif; ?>
                        <div class="<?=$status['classMain'] ?> main_statusNew" title="<?=$status['title'] ?>">
                            <span class="glyphicon glyphicon-<?=$status['glyphi'] ?>"></span>
                        </div>
                    <? endif; ?>
                    <a href="/model-view/?id=<?=$row['id'] ?>" class="model-href">
                        <img src="<?=_rootDIR_HTTP_ ?>web/picts/loading_circle_low2.gif" class="imgLoadCircle_main" />
                        <img src="<?=$showimg ?>" class="img-responsive imgThumbs_main hidden" onload="onImgLoad(this);" />
                    </a>
                    <div class="caption p1 mainCardCaption">
                        <div class="">
                            <? if ($editBtn): ?>
                                <a href="/add-edit/?id=<?=$row['id'] ?>&component=2" class="btn btn-sm btn-default pull-left">
                                    <span class="glyphicon glyphicon-pencil"></span>
                                </a>
                            <? endif; ?>
                            <div class="selectionCheck <?=$checkedSM['active'] ?>">
                                <label for="checkId_<?=$row['id'] ?>" class="pointer">
                                    <span class="glyphicon <?=$checkedSM['class'] ?>"></span>
                                </label>
                                <input class="hidden checkIdBox" <?=$checkedSM['inptAttr'] ?> checkBoxId modelId="<?=$row['id'] ?>" modelName="<?= $row['number_3d'].$vc_show ?>" modelType="<?=$row['model_type'] ?>" type="checkbox" id="checkId_<?=$row['id'] ?>">
                            </div>
                            <?if ($btn3D):?>
                                <i class="button-3D-pict-main fas fa-dice-d6" title="Доступен 3D просмотр"></i>
                            <? endif; ?>
                            <b class="pull-right p1" style="background-color: #ffffff" title="<?=$row['model_type'] ?>"><?=$modTypeStr ?></b>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
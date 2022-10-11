<?php $bcStart = $newKitStarts ? '#2e6da4' : '#fffff' ?>
<?php $bcEnd = $newKitEnds ? '#2e6da4' : '#fffff' ?>
<div id="<?=$row['id'] ?>" class="<?=$comlectIdent?"p0":"prj"?> col-xs-6 col-sm-4 col-md-<?=$columns?> col-lg-<?=$columnsLG?>">
    <div class="thumbnail prj-thumbnail mb-1 cursorPointer"
    <?=$comlectIdent?"style=\"border-left-color: $bcStart;!important; border-right-color: $bcEnd;!important;\"":""?>
    >
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
                    <?php if ( isset($status['stat_name']) ):?>
                        <?php if ( $status['glyphi'] == 'glyphicons-ring'): ?>
                            <span class="<?=$status['glyphi'] ?>"></span>
                        <?php endif; ?>
                        <div class="<?=$status['classMain'] ?> main_statusNew" title="<?=$status['title'] ?>">
                            <span class="glyphicon glyphicon-<?=$status['glyphi'] ?>"></span>
                        </div>
                    <?php endif; ?>
                    <a href="/model-view/?id=<?=$row['id'] ?>" class="model-href">
                        <img src="<?=_rootDIR_HTTP_ ?>web/picts/loading_circle_low2.gif" class="imgLoadCircle_main" />
                        <img src="<?=$showimg ?>" class="img-responsive imgThumbs_main hidden" onload="onImgLoad(this);" />
                    </a>
                    <div class="caption p1 mainCardCaption">
                        <div class="">
                            <?php if ($editBtn): ?>
                                <a href="/model-edit/?id=<?=$row['id'] ?>" class="btn btn-sm btn-default pull-left">
                                    <span class="glyphicon glyphicon-pencil"></span>
                                </a>
                            <?php endif; ?>
                            <div class="selectionCheck <?=$checkedSM['active'] ?>">
                                <label for="checkId_<?=$row['id'] ?>" class="pointer">
                                    <span class="glyphicon <?=$checkedSM['class'] ?>"></span>
                                </label>
                                <input class="hidden checkIdBox" <?=$checkedSM['inptAttr'] ?> checkBoxId modelId="<?=$row['id'] ?>" modelName="<?= $row['number_3d'].$vc_show ?>" modelType="<?=$row['model_type'] ?>" type="checkbox" id="checkId_<?=$row['id'] ?>">
                            </div>
                            <?php if ($btn3D === true): ?>
                                <i class="button-3D-pict-main fas fa-dice-d20" title="Доступен 3D просмотр"></i>
                            <?php endif; ?>
                            <b class="pull-right p1" style="background-color: #ffffff" title="<?=$row['model_type'] ?>"><?=$modTypeStr ?></b>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="<?=$row['id'] ?>" class="col-xs-6 col-sm-4 col-md-<?=$columns?> col-lg-<?=$columnsLG?> prj-item">
	<div class="ratio">
		<div class="ratio-inner ratio-4-3">
			<div class="ratio-content">
				<? if ( $status['stat_name'] ):?>
					<? if ( $status['glyphi'] == 'glyphicons-ring' ): ?>
						<span class="<?=$status['glyphi'] ?>"></span>
					<? endif; ?>
					<div class="<?=$status['classMain'] ?> main_status pull-right" title="<?=$status['title'] ?>">
						<span class="glyphicon glyphicon-<?=$status['glyphi'] ?>"></span>
					</div>
				<? endif; ?>
				<div class="main_hot">
					<?for ( $i = 0; $i < count($labels); $i++ ):?>
						<span title="<?=$labels[$i]['info'] ?>" class="label <?=$labels[$i]['class'] ?>">
							<span class="glyphicon glyphicon-tag"></span>
							<?=$labels[$i]['name'] ?>
						</span>
						<br/>
					<?endfor; ?>
				</div>
				<a href="/model-view/?id=<?=$row['id'] ?>">
					<div class="text-primary txt-art">
						<? if ( $comlectIdent !== true ): ?>
						<span><?= $row['number_3d'].$vc_show ?></span>
						<? endif; ?>
					</div>
					<img src="<?=_rootDIR_HTTP_ ?>web/picts/loading_circle_low2.gif" class="imgLoadCircle_main" />
					<img src="<?=$showimg ?>" class="img-responsive imgThumbs_main hidden" onload="onImgLoad(this);" />
				</a>
			</div>
			<? if ($editBtn): ?>
				<a href="/add-edit/?id=<?=$row['id'] ?>&component=2" class="btn btn-sm btn-default editbtnshow">
					<span class="glyphicon glyphicon-pencil"></span>
				</a>
			<? endif; ?>
			<?if ($btn3D): ?>
				<span class="button-3D-pict-main" title="Доступен 3D просмотр"></span>
			<? endif; ?>
		</div>
		<div class="text-muted margtop">
			<span class="glyphicon glyphicon-calendar pull-left" title="дата создания"></span>
			<?=date_create( $row['date'] )->Format('d.m.Y'); ?>
			<div class="selectionCheck <?=$checkedSM['active'] ?>">
				<label for="checkId_<?=$row['id'] ?>" class="pointer">
					<span class="glyphicon <?=$checkedSM['class'] ?>"></span>
				</label>
				<input class="hidden checkIdBox" <?=$checkedSM['inptAttr'] ?> checkBoxId modelId="<?=$row['id'] ?>" modelName="<?= $row['number_3d'].$vc_show ?>" modelType="<?=$row['model_type'] ?>" type="checkbox" id="checkId_<?=$row['id'] ?>">
			</div>
			<?= $columnsLG===1?"<br>":"" ?>
			<b class="pull-right" title="<?=$row['model_type'] ?>"> <?=$modTypeStr ?></b>
		</div>
		<div class="clearfix"></div>
	</div>
</div>
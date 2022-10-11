<?php
use models\User;
use soffit\{HtmlHelper,Router};

/** Data varables for tools panel under the topBar */
if ( !isset($toolsPanel) || empty($toolsPanel) )
	throw new \Exception("Someting goes wrong with tools panel variables, in: " . __METHOD__, 504);
$session = $this->session;
$assist = $session->assist;
$cNameOrigin = Router::getControllerNameOrigin();

$workCentersSort = $workCentersSort??false;
$workingCenters = $workingCenters??[];
?>
<script src="/web/views/main/js/trytoload.js?ver=005"></script>

<!-- SORT PANEL START-->
<div class="row centered">
	<div class="col-xs-12" style="margin-top: -19px!important;">
		<div class="btn-group btn-group-sm" role="group" aria-label="...">
			
			<?php if ( $workCentersSort === true ): ?>
			<div class="btn-group btn-group-sm" role="group">
				<button type="button" class="btn btn-group-sm btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<span>Участок: <?= $assist['wcSort']['name']??'Нет' ?> </span>
					<span class="caret"></span>
				</button>
				<ul class="dropdown-menu dropdown-menu-right">
					<li role="presentation">
						<a href="<?=HtmlHelper::URL($cNameOrigin,['wcSort'=>'none']) ?>">Нет</a></li>
					<li role="presentation" class="divider"></li>
					<?php foreach ( $workingCenters as $wcKey => $workingCenter ): ?>
						<?php
							$wcIDs = '';
							foreach ( $workingCenter as $wcID => $wcArray ) $wcIDs .= $wcID.'-';
							$wcIDs = trim($wcIDs,'-');
						?>
						<li role="presentation">
							<a href="<?=HtmlHelper::URL($cNameOrigin,['wcSort'=>$wcIDs]) ?>"><?=$wcKey ?></a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
			<?php endif; ?>

            <!-- PURGE SELECT -->
            <div class="btn-group btn-group-sm" role="group" aria-label="...">
                <a href="<?=HtmlHelper::URL($cNameOrigin,['purgeselect'=>1]) ?>" type="button" title="Сбросить выборку" class="btn btn-default"><i class="fas fa-broom"></i></a>
            </div>

            <!-- MODEL GEM SELECT -->
            <div class="btn-group btn-group-sm" role="group">
                <button id="modelTypeSelect" title="Тип камней" type="button" class="btn btn-default dropdown-toggle trigger" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span><i class="far fa-gem"></i> <?=$assist['gemType']?></span>
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                    <li><a href="<?= HtmlHelper::URL($cNameOrigin,['gem'=>-1]) ?>">Все</a></li>
                    <li role="separator" class="divider"></li>
                    <?php foreach ( $toolsPanel['modelGemTypes'] as $gemType ): ?>
                        <li><a href="<?= HtmlHelper::URL($cNameOrigin,['gem'=>$gemType['id']]) ?>"><?= $gemType['name'] ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- MODEL MATERIAL SELECT -->
            <div class="btn-group btn-group-sm" role="group">
                <button id="modelTypeSelect" title="Материал изделия" type="button" class="btn btn-default dropdown-toggle trigger" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span><i class="fab fa-codepen"></i> <?=$assist['modelMaterial']?></span>
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                    <li><a href="<?= HtmlHelper::URL($cNameOrigin,['mat'=>-1]) ?>">Все</a></li>
                    <li role="separator" class="divider"></li>
                    <?php foreach ( $toolsPanel['modelMaterials'] as $modelMaterial ): ?>
                        <li><a href="<?= HtmlHelper::URL($cNameOrigin,['mat'=>$modelMaterial['id']]) ?>"><?= $modelMaterial['name'] ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- MODEL TYPE SELECT -->
            <div class="btn-group btn-group-sm" role="group">
                <button id="modelTypeSelect" title="Тип модели" type="button" class="btn btn-default dropdown-toggle trigger" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span><i class="fas fa-object-group"></i> <?=$assist['modelType']?></span>
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                    <li><a href="<?= HtmlHelper::URL($cNameOrigin,['mt'=>-1]) ?>">Все</a></li>
                    <li role="separator" class="divider"></li>
                    <?php foreach ( $toolsPanel['modelTypes'] as $modelType ): ?>
                        <li><a href="<?= HtmlHelper::URL($cNameOrigin,['mt'=>$modelType['id']]) ?>"><?=$modelType['name'] ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="btn-group btn-group-sm" role="group">
                <button id="statusesSelect" title="Статус" type="button" class="btn btn-default dropdown-toggle trigger" data-izimodal-open="#modalStatuses" data-izimodal-transitionin="fadeInDown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span><i class="fas fa-code-branch"></i> <?=$assist['regStat']?></span>
                    <span class="caret"></span>
                </button>
            </div>

			<div class="btn-group btn-group-sm" role="group">
				<button type="button" class="btn btn-default dropdown-toggle" title="<?=$toolsPanel['chevTitle']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<span style="font-size:9px;"><span class="glyphicon glyphicon-<?=$toolsPanel['chevron_']?>"></span></span>
				</button>
				<ul class="dropdown-menu dropdown-menu-right">
					<li>
						<a href="<?=HtmlHelper::URL($cNameOrigin,['sortDirect'=>1])?>" title="По возростанию">
							<span class="glyphicon glyphicon-triangle-top"></span> По возростанию</a>
					</li>
					<li>
						<a href="<?=HtmlHelper::URL($cNameOrigin,['sortDirect'=>2])?>" title="По убыванию">
							<span class="glyphicon glyphicon-triangle-bottom"></span> По убыванию</a>
					</li>
				</ul>
			</div>

			<div class="btn-group btn-group-sm" role="group">
				<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<?="Сорт. по ".$toolsPanel['showsort']; ?>
					<span class="caret"></span>
				</button>
				<ul class="dropdown-menu dropdown-menu-right">
					<li>
						<a href="<?=HtmlHelper::URL($cNameOrigin,['sortby'=>'date'])?>">По Дате</a></li>
					<li>
						<a href="<?=HtmlHelper::URL($cNameOrigin,['sortby'=>'number_3d'])?>">По №3D</a></li>
					<li>
						<a href="<?=HtmlHelper::URL($cNameOrigin,['sortby'=>'vendor_code'])?>">По Артикулу</a></li>
					<li>
						<a href="<?=HtmlHelper::URL($cNameOrigin,['sortby'=>'status'])?>">По Статусу</a></li>
				</ul>
			</div>

			<div class="btn-group btn-group-sm" role="group">
				<button type="button" class="btn btn-default dropdown-toggle" title="кол-во отображаемых позиций" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<span><?=$assist['maxPos']; ?></span>
					<span class="caret"></span>
				</button>
				<ul class="dropdown-menu dropdown-menu-right">
					<li>
						<a href="<?=HtmlHelper::URL($cNameOrigin,['maxpos'=>12])?>">12</a></li>
					<li>
						<a href="<?=HtmlHelper::URL($cNameOrigin,['maxpos'=>18])?>">18</a></li>
					<li>
						<a href="<?=HtmlHelper::URL($cNameOrigin,['maxpos'=>24])?>">24</a></li>
					<li>
						<a href="<?=HtmlHelper::URL($cNameOrigin,['maxpos'=>48])?>">48</a></li>
					<li>
						<a href="<?=HtmlHelper::URL($cNameOrigin,['maxpos'=>102])?>">102</a></li>
				</ul>
			</div>

			

			<!-- BUTTONS TO SHOW MODELS-->
			<div class="btn-group btn-group-sm" role="group" aria-label="...">
				<a type="button" href="/tiles/?row_pos=1" title="Отобразить плиткой" class="btn btn-default <?=$activeTiles??''?>">
					<span class="glyphicon glyphicon-th-large">
				</a>
				<a type="button" href="/kits/?row_pos=2" title="Соединить по комплектам" class="btn btn-default <?=$activeKits??''?>">
					<span class="glyphicon glyphicon-th-list"></span>
				</a>
				<a type="button" href="/working-centers/?row_pos=3" title="Таблица Рабочих Участков" class="btn btn-default <?=$activeWC??''?>"><i class="fas fa-tasks"></i>
					<!--</span>-->
				</a>
				<a type="button" href="/location-centers/?row_pos=4" title="Конечный центр нахождения" class="btn btn-default <?=$activeLC??''?>">
					<span class="glyphicon glyphicon-tasks">
				</a>
				<a type="button" href="/overdues/?row_pos=5" title="Таблица просроченных" class="btn btn-default <?=$activeOver??''?>">
					<i class="fa-clock far"></i>
				</a>
			</div>
			<!-- BUTTONS PDF / XLS Export -->
			<div class="btn-group btn-group-sm">
				<?php $drawBy_ = (int)$assist['drawBy_']?>
				<?php if ( $drawBy_ == 1 || $drawBy_ == 2 ): ?>
					<a onclick="sendPDF()" id="sendPDF" type="button" class="btn btn-default" type="button" title="Записать коллекцию в PDF"><i class="far fa-file-pdf"></i>
					</a>
				<?php elseif( $drawBy_ >= 3 && $drawBy_ <= 5 ): ?>
					<a onclick="sendXLS(<?=$drawBy_?>)" id="sendXLS" type="button" class="btn btn-default" type="button" title="Записать коллекцию в Excel"><i class="far fa-file-excel"></i>
					</a>
				<?php endif; ?>
			</div>

			<?php if ( $drawBy_ == 1 || $drawBy_ == 2  ): ?>
				<div class="btn-group btn-group-sm <?=$toolsPanel['toggleSelectedGroup']?>" id="selectedGroup" role="group">
					<button type="button" class="btn btn-default dropdown-toggle" title="Выделенные модели" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<span class="glyphicon glyphicon-screenshot"></span>
						<span class="caret"></span>
					</button>
					<ul class="dropdown-menu dropdown-menu-right">
						<li role="presentation">
							<a title="Выделить все модели на странице" class="selectsCheckAll">Выделить все</a></li>
						<li role="presentation">
							<a title="Снять выделение со всех моделей" class="selectsUncheckAll">Снять выделение</a></li>
						<li role="presentation">
							<a title="Отобразить только выделенные модели" href="<?=HtmlHelper::URL($cNameOrigin,['selected-models-show'=>''])?>" class="selectsShowModels">Показать</a></li>
	                    <?php if ( User::permission('statuses') ): ?>
	                        <li role="presentation">
	                            <a title="Изменить статус для всех выделенных моделей" class="editStatusesSelectedModels">Проставить статус</a>
	                        </li>
	                    <?php endif;?>
						<li role="presentation" class="divider"></li>
						<?php  $selModels = $session->selectionMode['models']??[] ?>
						<?php foreach ( $selModels as $selModel ): ?>
	                        <li data-id="<?=$selModel['id']?>">
	                        	<a href="<?=HtmlHelper::URL('model-view/',['id'=>$selModel['id']])?>"><?=$selModel['name']?></a>
	                        </li>
	                    <?php endforeach;?>
					</ul>
				</div>
				<div class="btn-group btn-group-sm" role="group" aria-label="...">
					<a type="button" id="selectMode" onclick="selects.toggleSelectionMode(this);" class="btn btn-default <?=$toolsPanel['activeSelect']?>">
						<span class="glyphicon glyphicon-edit" title="Режим выделения"></span></a>
				</div>
			<?php endif; ?>

		</div>

	</div><!-- end col -->
</div><!-- /row -->
<!-- SORT PANEL END-->
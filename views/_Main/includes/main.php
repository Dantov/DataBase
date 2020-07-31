<?php

use Views\_Globals\Models\User;

$session = $this->session;
?>
<script src="/Views/_Main/js/trytoload.js?ver=004"></script>

<div class="row">
	<div class="col-xs-12">
		<div class="btn-group pull-right" role="group" aria-label="...">

			<?php if ( $workCentersSort === true ): ?>
			<div class="btn-group" role="group">
				<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<span>Участок: <?= $_SESSION['assist']['wcSort']['name'] ?: 'Нет' ?> </span>
					<span class="caret"></span>
				</button>
				<ul class="dropdown-menu dropdown-menu-right">
					<li role="presentation">
						<a href="/main/?wcSort=none">Нет</a></li>
					<li role="presentation" class="divider"></li>
					<?php foreach ( $workingCenters?:[] as $wcKey => $workingCenter ) : ?>
						<?php
							$wcIDs = '';
							foreach ( $workingCenter as $wcID => $wcArray ) $wcIDs .= $wcID.'-';
							$wcIDs = trim($wcIDs,'-');
						?>
						<li role="presentation">
							<a href="/main/?wcSort=<?=$wcIDs ?>"><?=$wcKey ?></a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
			<?php endif; ?>

			<div class="btn-group" role="group">
				<button id="statusesSelect" type="button" class="btn btn-default dropdown-toggle trigger" data-izimodal-open="#modalStatuses" data-izimodal-transitionin="fadeInDown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<span>Статус:<?=$selectedStatusName ?></span>
					<span class="caret"></span>
				</button>
			</div>

			<div class="btn-group" role="group">
				<button type="button" class="btn btn-default dropdown-toggle" title="<?=$chevTitle; ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<span style="font-size:9px;">
						<span class="glyphicon glyphicon-<?=$chevron_; ?>"></span></span>
				</button>
				<ul class="dropdown-menu dropdown-menu-right">
					<li>
						<a href="/main/?sortDirect=1" title="По возростанию">
							<span class="glyphicon glyphicon-triangle-top"></span> По возростанию</a></li>
					<li>
						<a href="/main/?sortDirect=2" title="По убыванию">
							<span class="glyphicon glyphicon-triangle-bottom"></span> По убыванию</a></li>
				</ul>
			</div>

			<div class="btn-group" role="group">
				<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<?="Сорт. по ".$showsort; ?>
					<span class="caret"></span>
				</button>
				<ul class="dropdown-menu dropdown-menu-right">
					<li>
						<a href="/main/?sortby=date">По Дате</a></li>
					<li>
						<a href="/main/?sortby=number_3d">По №3D</a></li>
					<li>
						<a href="/main/?sortby=vendor_code">По Артикулу</a></li>
					<li>
						<a href="/main/?sortby=status">По Статусу</a></li>
				</ul>
			</div>

			<div class="btn-group" role="group">
				<button type="button" class="btn btn-default dropdown-toggle" title="кол-во отображаемых позиций" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<span><?=$_SESSION['assist']['maxPos']; ?></span>
					<span class="caret"></span>
				</button>
				<ul class="dropdown-menu dropdown-menu-right">
					<li>
						<a href="/main/?maxpos=12">12</a></li>
					<li>
						<a href="/main/?maxpos=18">18</a></li>
					<li>
						<a href="/main/?maxpos=24">24</a></li>
					<li>
						<a href="/main/?maxpos=48">48</a></li>
					<li>
						<a href="/main/?maxpos=102">102</a></li>
				</ul>
			</div>

			<div class="btn-group" role="group" aria-label="...">
				<a type="button" href="/main/?row_pos=2" class="btn btn-default <?=$activeList; ?>">
					<span class="glyphicon glyphicon-th-list" title="Разбить по комплектам"></span></a>
				<a type="button" href="/main/?row_pos=1" class="btn btn-default <?=$activeSquer; ?>"><span class="glyphicon glyphicon-th-large" title="Отобразить изделия плиткой"></a>
			</div>
			<div class="btn-group" role="group">
				<button type="button" class="btn btn-default <?=$activeWorkingCenters; ?> <?=$activeWorkingCenters2; ?> dropdown-toggle" title="кол-во отображаемых позиций" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<span class="glyphicon glyphicon-save-file" title="Таблицы Рабочих участков"></span>
				</button>
				<ul class="dropdown-menu dropdown-menu-right">
					<li>
						<a href="/main/?row_pos=3">
							<span class="glyphicon glyphicon-tasks"></span> Таблица Рабочих Участков</a></li>
					<li>
						<a href="/main/?row_pos=4">
							<span class="glyphicon glyphicon-menu-hamburger"></span> Конечный центр нахождения</a></li>
					<li>
						<a href="/main/?row_pos=5">
							<i class="fa-clock far"></i> Таблица просроченных</a></li>
				</ul>
			</div>

			<div class="btn-group <?=$toggleSelectedGroup; ?>" id="selectedGroup" role="group">
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
						<a title="Отобразить только выделенные модели" href="/main/?selected-models-show" class="selectsShowModels">Показать</a></li>
                    <?php if ( User::permission('statuses') ): ?>
                        <li role="presentation">
                            <a title="Изменить статус для всех выделенных моделей" class="editStatusesSelectedModels">Проставить статус</a>
                        </li>
                    <?php endif;?>
					<li role="presentation" class="divider"></li>
					<?=$selectedModelsByLi; ?>
				</ul>
			</div>
			<div class="btn-group" role="group" aria-label="...">
				<a type="button" id="selectMode" onclick="selects.toggleSelectionMode(this);" class="btn btn-default <?=$variables['activeSelect']; ?>">
					<span class="glyphicon glyphicon-edit" title="Режим выделения"></span></a>
			</div>
		</div>

		<!-- Кнопки Xlsx PDF -->
		<div class="btn-group pull-left" role="group">
			<?php $drawBy_ = (int)$_SESSION['assist']['drawBy_']; ?>
			<?php if ( $drawBy_ > 2 && $drawBy_ < 6  ): ?>
				<a onclick="sendXLS(<?=$drawBy_ ?>)"  id="sendXLS" class="btn btn-link" style="font-size: 18px; padding: 5px 8px 0 8px;" type="button" title="Записать коллекцию в Excel" >
					<i class="far fa-file-excel"></i>
				</a>
			<?php elseif( $drawBy_ > 0 && $drawBy_ < 3 ): ?>
				<a onclick="sendPDF()"  id="sendPDF" class="btn btn-link" style="font-size: 18px; padding: 5px 8px 0 8px;" type="button" title="Записать коллекцию в PDF" >
					<i class="far fa-file-pdf"></i>
				</a>
			<?php endif; ?>
			
			<?php if ( $drawBy_ == 3 ): ?>
				<a id="expiredButon" href="/main/?row_pos=5" class="btn btn-link" style="font-size: 18px; padding: 5px 8px 0 8px;" type="button" title="Таблица просроченных" >
					<i class="far fa-clock"></i>
				</a>
			<?php endif; ?>
			
			<?php if ( $drawBy_ == 5 ): ?>
				<a id="wCentersButon" href="/main/?row_pos=3" class="btn btn-link" style="font-size: 18px; padding: 5px 8px 0 8px;" type="button" title="Таблица Рабочих Участков" >
					<i class="fas fa-tasks"></i>
				</a>
			<?php endif; ?>
		</div>

		<div class="pull-left">
			<h3 style="margin: 0 0 0 15px; padding-top:4px;">
                <?php if ( $searchFor = $session->getKey('searchFor') ): ?>
                    <a type="button" title="сбросить" href="/globals/?search=resetSearch">
                        <span id="collectionName">Поиск по: &#171;<?=$searchFor?>&#187;</span>
                    </a>
                <?php else: ?>
                    <a type="button" title="<?=$collectionName ?>" href="/main/?coll_show=<?=$session->getKey('assist')['collection_id']?>">
                        <span id="collectionName"><?=$collectionName?></span>
                    </a>
                <?php endif; ?>
			</h3>
		</div>
	</div><!-- end col -->
</div><!-- /row -->
<div class="clearfix"></div>
<hr/>
<div class="row loading_cont" id="loadeding_cont">
	<?php if ( !isset($_SESSION['nothing']) ): ?>
		<?php if ( $wholePos == 0 ): ?>
			<img src="<?=_rootDIR_HTTP_ ?>web/picts/web1.png" width="10%"/>
			В этой коллекции изделий нет.
		<?php endif; ?>
	<?php else: ?>
		<img src="<?=_rootDIR_HTTP_ ?>web/picts/web1.png" width="10%"/>
		<?
			$showModels .= $_SESSION['nothing'];
			unset($_SESSION['nothing']); 
		?>
	<?php endif; ?>
	<?=$showModels ?>
</div>
<!-- paggination -->
<center>
	<span class="statsbuttom"><?=$statsbottom?></span>
	<?=$pagination?>
</center>
<?php 
use Views\_Globals\Models\User;

$nomGS = User::permission('nomGS');
$nomUsers = User::permission('nomUsers');
$gs = $gs??'';
$u = $u??'';
?>
<ul class="nav nav-tabs" role="tablist" id="tablist">
    <li role="presentation" class="<?= $gs || $u ?'':'active'?>"><a href="<?= $gs || $u ? '/nomenclature/' : '#tab1' ?>" role="tab" data-toggle="<?=$gs || $u?'':'tab'?>">Коллекции</a></li>
    <li role="presentation"><a href="<?= $gs || $u ? '/nomenclature/' : '#tab2' ?>" role="tab" data-toggle="<?=$gs || $u?'':'tab'?>">Камни</a></li>
    <li role="presentation"><a href="<?= $gs || $u ? '/nomenclature/' : '#tab3' ?>" role="tab" data-toggle="<?=$gs || $u?'':'tab'?>">Материалы</a></li>
    <li role="presentation"><a href="<?= $gs || $u ? '/nomenclature/' : '#tab4' ?>" role="tab" data-toggle="<?=$gs || $u?'':'tab'?>">Общие данные</a></li>
    <?php if ($nomGS): ?>
    	<li role="presentation" class="<?= $gs?'active':''?>"><a role="tab" href="\nomenclature\?tab=gs" >Общая система оценок</a></li>
	<?php endif; ?>
	<?php if ($nomUsers): ?>
    	<li role="presentation" class="<?= $u?'active':''?>"><a role="tab" href="\nomenclature\?tab=users" >Пользователи</a></li>
    <?php endif; ?>
</ul>
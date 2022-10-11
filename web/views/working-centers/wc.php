<?php use widgets\Paginator;?>
<?php require dirname(__DIR__) . "/main/main.php" ?>
<!-- paggination -->
<div class="row unselectable">
	<div class="col-sm-6 pl-1 pr-0"><span class="pull-left mt-4"><?=$statistic?></span></div>
	<div class="col-sm-6 pr-1 pl-0">
		<?php
	        echo Paginator::widget([
	            'pagination' => $pagination,
	            'options' => [
	                'template' => _globDIR_ . "paginator_tpl.php",
	                'squaresPerPage' => 10,
	                'size' => 'small', // large | small
	                'color' => '',
	                'class' => 'pagination m0 mt-3 mb-2 pull-right',
	            ],
	        ]);
	    ?>
	</div>
	<div class="clearfix"></div>
</div>



<!-- SHOW MODELS HERE -->
<div class="row unselectable" id="loadeding_cont">
	<?php if ( !isset($_SESSION['nothing']) ): ?>
		<?php if ( $wholePos == 0 ): ?>
			<img src="<?=_HOST_web_.'/picts/web1.png'?>" width="10%"/>
			В этой коллекции изделий нет.
		<?php endif; ?>
	<?php else: ?>
		<img src="<?=_HOST_web_.'/picts/web1.png'?>" width="10%"/>
		<?php
			$showModels .= $_SESSION['nothing'];
			unset($_SESSION['nothing']); 
		?>
	<?php endif; ?>
	<?=$showModels ?>
</div>



<!-- paggination -->
<div class="row unselectable">
	<div class="col-sm-6 pl-1 pr-0"><span class="pull-left mt-4"><?=$statistic?></span></div>
	<div class="col-sm-6 pr-1 pl-0">
		<?php
	        echo Paginator::widget([
	            'pagination' => $pagination,
	            'options' => [
	                'template' => _globDIR_ . "paginator_tpl.php",
	                'squaresPerPage' => 10,
	                'size' => 'small', // large | small
	                'color' => '',
	                'class' => 'pagination m0 mt-1 pull-right',
	            ],
	        ]);
	    ?>
	</div>
	<div class="clearfix"></div>
</div>
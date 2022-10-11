<?php require dirname(__DIR__) . "/main/main.php" ?>
<hr/>



<!-- SHOW MODELS HERE -->
<div class="row unselectable" id="loadeding_cont">
	<?php if ( !isset($_SESSION['nothing']) ): ?>
		<?php if ( $wholePos == 0 ): ?>
			<img src="<?=_HOST_web_?>/picts/web1.png" width="10%"/>
			В этой коллекции изделий нет.
		<?php endif; ?>
	<?php else: ?>
		<img src="<?=_HOST_web_?>/picts/web1.png" width="10%"/>
		<?php
			$showModels .= $_SESSION['nothing'];
			unset($_SESSION['nothing']); 
		?>
	<?php endif; ?>
	<?=$showModels ?>
</div>


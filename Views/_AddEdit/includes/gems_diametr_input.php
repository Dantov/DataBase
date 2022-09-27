<div class="input-group gems_diametr_input">
	<input type="text" class="form-control" name="gems[gems_sizes][]" value="<?=$gem['gems_sizes']??'' ?>"/>
    <div class="input-group-btn">
		<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			<span class="caret"></span>
		</button>
		<ul class="dropdown-menu dropdown-menu-right">
			<?=$gems_sizesLi??'' ?>
		</ul>
    </div>
</div>
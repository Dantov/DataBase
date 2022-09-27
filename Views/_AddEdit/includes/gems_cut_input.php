<div class="input-group gems_cut_input">
    <input type="text" class="form-control" name="gems[gems_cut][]" value="<?=$gem['gems_cut']??'' ?>">
    <div class="input-group-btn">
		<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			<span class="caret"></span>
		</button>
		<ul class="dropdown-menu dropdown-menu-right">
			<?=$gems_cutLi??'' ?>
		</ul>
    </div>
</div>
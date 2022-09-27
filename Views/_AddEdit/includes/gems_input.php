<div class="input-group">
    <input type="text" class="form-control" name="gems[gems_names][]" value="<?=$gem['gems_names']??'' ?>"/>
    <div class="input-group-btn">
		<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			<span class="caret"></span>
		</button>
		<ul class="dropdown-menu dropdown-menu-right">
			<?=$gems_namesLi??'' ?>
		</ul>
    </div>
</div>
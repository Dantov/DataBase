<input type="hidden" class="rowID" name="vc_links[id][]" value="<?=$dopVc['id']?>">
<div class="input-group">
    <input type="text" class="form-control" name="vc_links[vc_names][]" value="<?=$dopVc['vc_names'];?>"/>
    <div class="input-group-btn">
		<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			<span class="caret"></span>
		</button>
		<ul class="dropdown-menu dropdown-menu-right">
			<?=$vc_namesLI;?>
		</ul>
    </div>
</div>


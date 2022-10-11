<?php use soffit\HtmlHelper?>
<tr class="">
    <td>
        <a href="<?=HtmlHelper::URL('model-view',['id'=>$row['id']])?>"><?=$row['vendor_code'] ?: $row['number_3d']?></a>
    </td>
    <td><?=$row['model_type']?></td>
    <td><?= $workingCenter['name']??"" ?></td>
    <td title="<?= ($lastStatus['status']['title']??'') . " - " . ($lastStatus['name']??'') ?>">
        <span class="glyphicon glyphicon-<?=isset($lastStatus['status']['glyphi'])?:''?>"></span>&nbsp;
        <?= isset($lastStatus['status']['name_ru'])?$lastStatus['status']['name_ru']:"" ?>
    </td>
    <td title="<?=$row['size_range']?>"><?=$sizeRange?></td>
    <td><?=$vc_done?></td>
    <td><?=$vc_balance?></td>
    <td>
    <?php if ( $drawEditDate ) : ?>
         <input type="date" name="lastStatusDate" class="form-control input-sm" onchange="main.changeStatusDate(this)" data-id="<?=$lastStatus['id']?>" value="<?=$lastStatus['date']?>">
    <?php else: ?>
        <?=$this->formatDate($lastStatus['date'])?>
    <?php endif; ?>
    </td>
</tr>
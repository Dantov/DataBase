<tr class="">
    <td title="" style="text-align: right!important;"><?=$workingCenter['name']?:''?> :</td>
    <td title="" style="text-align: left!important;"><?=$workingCenter['descr']?:''?></td>
    <td title="Кол-во моделей из выбранных принадлежат этому участку">
        <?php if ( $workingCenter['countAll']??'' ): ?>
            <?php $countAllIds = implode(',',$workingCenter['ids']) ?>
            <a href="<?="/main/?countedIds=".$countAllIds ?>"><b><?=$workingCenter['countAll']?></b></a>
        <?php else: ?>
        <?php endif; ?>
    </td>
    <td title="">
        <?php if ( $workingCenter['expired']??[] ): ?>
            <?php $expiredIds = implode(',',$workingCenter['expiredIds']) ?>
            <a href="<?="/main/?countedIds=".$expiredIds ?>"><b><?=$workingCenter['expired']?></b></a>
        <?php else: ?>
        <?php endif; ?>
    </td>
    <td title="<?= $wcUser['fullFio'] ?>"><?= $wcUser['fio'] ?></td>
</tr>
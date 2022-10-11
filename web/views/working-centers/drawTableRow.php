<tr <?= $trFill ? 'class="danger"':'' ?> onclick="window.location.href = _URL_ + '/model-view/?id=<?=$row['id']?>'">
    <td title="Артикул / №3Д"><?=$row['vendor_code'] ?: $row['number_3d']?></td>
    <td></td>
    <td title="<?=$row['size_range']?>"><?=$sizeRange?></td>
    <?php foreach ( $wCenters as $wCenterL ):?>

        <?php $startNameRu = isset($wCenterL['start']['status']['name_ru'])?$wCenterL['start']['status']['name_ru']:'' ?>
        <?php $endNameRu = isset($wCenterL['end']['status']['name_ru'])?$wCenterL['end']['status']['name_ru']:'' ?>
        <?php $startDate = isset($wCenterL['start']['date'])?$wCenterL['start']['date']:'' ?>

        <!-- Статус принятия -->
        <?php
            if ( $startDate === -1 )
            {
                $title = 'Прошло больше 2х дней с момента последней сдачи!';
                $style = 'style="background: #333; color: #fff"';
                $text  = 'Просрочено';
            } else {
                $style = '';
                $title = $startNameRu ?: '';
                $text  = $startDate;
            }
        ?>
        <td <?=$style?> title="<?=$title?>"><?=$text?></td>

        <!-- Статус сдачи -->
        <?php
            $endDate = $wCenterL['end']['date']??'';
            if ( $endDate === -1 )
            {
                $title = 'Прошло больше 2х дней с момента принятия!';
                $style = 'style="background: #dc5d26; color: #fff"';
                $text = 'Просрочено';
            } else {
                $style = '';
                $title = $endNameRu ?: '';
                $text = $endDate;
            }
        ?>
        <td <?=$style?> title="<?=$title?>"><?=$text?></td>
    <?php endforeach; ?>
</tr>
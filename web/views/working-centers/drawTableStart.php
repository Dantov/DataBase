<div class="table-responsive" style="font-size: 10px; background: #edf9ff">
    <table style="width: 3000px !important;" class="table table-striped table-bordered table-hover table-condensed tableWC">
        <thead>
            <tr class="info">
                <th title="Артикул">Арт.</th>
                <th title="Количество деталей из которых состоит артикул шт.">Кол-во</th>
                <th title="Размерный ряд">Р-Ряд</th>
                <?php foreach ( $this->workingCentersSorted as $wCenterSorted ): ?>
                    <th colspan="2"><?=$wCenterSorted['name']?></th>
                <?php endforeach; unset($wCenterSorted); ?>
            </tr>
        </thead>
        <tbody>
            <tr class="warning">
                <td></td>
                <td></td>
                <td></td>
                <?php foreach ( $this->workingCentersSorted as $wCenterSorted ): ?>
                    <td colspan="2"><?=$wCenterSorted['descr']?></td>
                <?php endforeach; unset($wCenterSorted); ?>
            </tr>
            <tr class="active">
                <td></td>
                <td></td>
                <td></td>
                <?php foreach ( $this->workingCentersSorted as $wCenterSorted ): ?>
                    <td colspan="2"><?="Срок 1 день ( " . $wCenterSorted['perf_day'] . " Артикула/день)"?></td>
                <?php endforeach; unset($wCenterSorted); ?>
            </tr>
            <tr class="danger">
                <td></td>
                <td></td>
                <td></td>
                <?php foreach ( $this->workingCentersSorted as $wCenterSorted ): ?>
                <?php $statusStart = $wCenterSorted['statuses']['start']??[]; ?>
                <?php $statusEnd = $wCenterSorted['statuses']['end']??[]; ?>
                <td title="<?=$statusStart['title']??''?>"><span class="<?=$statusStart['name_en']??''?> glyphicon glyphicon-<?=$statusStart['glyphi']??''?>"></span><br>Поступило</td>
                <td title="<?=$statusEnd['title']??''?>"><span class="<?=$statusEnd['name_en']??''?> glyphicon glyphicon-<?=$statusEnd['glyphi']??''?>"></span><br>Сдано</td>
                <?php endforeach; unset($wCenterSorted); ?>
            </tr>
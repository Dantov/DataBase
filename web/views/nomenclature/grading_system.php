<?php
use models\User;
    $gs = true;
?>
<div class="row">
    <p class="lead text-info text-center">Списки Наименований</p>

    <div class="col-xs-12 stats_table">
        <?php require_once __DIR__ . "/nom_list.php" ?>
        <div class="tab-content">
            <div role="tabpanel" class="tab-pane active in fade" id="tab5">
                <br/>
                <?php foreach ( $data??[] as $wn => $wn_list ): ?>
                    <div class="panel panel-<?= $wn === 'База' ? 'warning' : 'default'?>">
                        <!-- Default panel contents -->
                        <div class="panel-heading text-bold text-center"><?= $wn ?></div>
                        <?php if ( $wn === 'База' ): ?>
                            <div class="panel-body">
                                <i>Базовая стоимость. Все остальные оценки формируются в процентном отношении от этой.</i>
                            </div>
                        <?php endif; ?>
                        <!-- Table -->
                        <table class="table cursorArrow">
                            <thead>
                            <tr class="thead11">
                                <th width="65%" class="text-center">Описание</th>
                                <th width="7.5%">Примеры</th>
                                <th width="7.5%" class="text-center" title="Процент от базовой стоимости">%</th>
                                <th width="7.5%" class="text-center">Баллы</th>
                                <th width="7.5%" class="text-center">Дата</th>
                                <th width="5%" class="text-center"></th>
                            </tr>
                            </thead>
                            <tbody data-tab="">
                                <?php foreach ( $wn_list??[] as $workType ): ?>
                                    <tr class="collsRow">
                                        <td><textarea readonly class="br-0 cursorArrow" style="width: 100%; overflow:hidden; resize: none;"><?=trim($workType['description'])?></textarea></td>
                                        <td><?=$workType['examples']?></td>
                                        <td class="text-center"><?=$workType['percent'] * 1?></td>
                                        <td class="text-center" title="<?=$workType['points'] * 100?>"><?=$workType['points'] == 0 ? 'Индивидуально' : $workType['points'] ?></td>
                                        <td class="text-center" title="Дата последнего изменения"><?=date_create( $workType['date'] )->Format('d.m.Y')?></td>
                                        <td class="text-center">
                                            <?php if ( User::permission('nomGS_edit') ): ?>
                                            <a class="btn btn-sm btn-default" type="button" title="Изменить" data-id="<?=$workType['id']?>" data-toggle="modal" data-target="#gsEditModal" role="button">
                                                <span class="glyphicon glyphicon-edit" aria-hidden="true"></span>
                                            </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
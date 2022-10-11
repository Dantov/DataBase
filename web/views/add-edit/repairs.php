<?php
/**
 * Date: 28.11.2020
 * Time: 23:14
 */

if ( !isset($whichRepair) ) exit;

$isPaid = $repair['notDell']??'';

switch ($whichRepair)
{
    case 0:
    {
        $repairRow['whichID']     =  'repairs3d';
        $repairRow['whichName']   =  'Ремонт 3Д №';
        $repairRow['panelColor']  =  '#5fd7f5';
        $repairRow['panelIcon']   =  'fa-draw-polygon';
        $repairRow['is3d_grade']   =  8;

        $whichName = '3d';
    } break;
    case 1:
    {
        $repairRow['whichID']     =  'repairsJew';
        $repairRow['whichName']   =  'Ремонт Мастер модели №';
        $repairRow['panelColor']  =  '#c1b467';
        $repairRow['panelIcon']   =  'fa-screwdriver';
        $repairRow['is3d_grade']   =  9;

        $whichName = 'jew';
    } break;
    case 2:
    {
        $repairRow['whichID']     =  'repairsProd';
        $repairRow['whichName']   =  'Ремонт модели на производстве №';
        $repairRow['panelColor']  =  '#c2b497';
        $repairRow['panelIcon']   =  'fa-hammer';
        $repairRow['is3d_grade']   =  10;

        $whichName = 'prod';
    } break;
}
if ( !$isPaid )
{
    $repairRow['sender']      = 'repairs['.$whichName.'][sender][]';
    $repairRow['toWhom']      = 'repairs['.$whichName.'][toWhom][]';
    $repairRow['descrName']   = 'repairs['.$whichName.'][repair_descr][]';
    $repairRow['descrNeed']   = 'repairs['.$whichName.'][descrNeed][]';
    $repairRow['status']      = 'repairs['.$whichName.'][status][]';
    $repairRow['statusDate']  = 'repairs['.$whichName.'][status_date][]';
    $repairRow['lasModUser']  = 'repairs['.$whichName.'][last_mod_user][]';
    $repairRow['lasModDate']  = 'repairs['.$whichName.'][last_mod_date][]';
    $repairRow['date']        = 'repairs['.$whichName.'][date][]';

    $repairRow['id']         = 'repairs['.$whichName.'][id][]';
    $repairRow['num']        = 'repairs['.$whichName.'][rep_num][]';
    $repairRow['which']      = 'repairs['.$whichName.'][which][]';
}


$masterLI = $whichRepair ? $formVars['jewelerNameLi'] : $formVars['mod3DLi'];
$panelID = "allRepairs_" . $repair['id'];
$collapseID = "repairCollapse_" . $repair['id'];

?>
<div class="panel panel-default <?= $repairRow['whichID'] ?>" id="<?=$panelID?> repair">
    <div class="panel-heading cursorPointer" style="background-color: <?=$repairRow['panelColor']?>!important;">
        <i class="fas <?=$repairRow['panelIcon']?>"></i>
        <strong>
            <span class="repairs_name"><?=$repairRow['whichName']?></span>
            <span class="repairs_number"><?=$repair['rep_num']?></span>
            от - <span class="repairs_date"><?=date_create( $repair['date'] )->Format('d.m.Y')?></span>
            <?php if ( $repair['status'] == 4 ): ?>
            <span> (Завершен)</span>
            <?php endif; ?>
        </strong>
        <?php if ( !$isPaid ): ?>
        <button class="btn btn-sm btn-danger pull-right removeRepair" data-repType="<?=$repairRow['whichID']?>" style="top:-5px !important; position:relative;" type="button" title="Удалить Ремонт">
            <span class="glyphicon glyphicon-remove"></span>
        </button>
        <?php endif; ?>
    </div>
    <div id="<?= $collapseID ?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="<?=$panelID?>" aria-expanded="false" style="height: 0;">
        <ul class="list-group">
            <li class="list-group-item list-group-item-info text-center">
                <i class="far fa-paper-plane"></i> <b><i>Отправитель</i></b>
            </li>
        </ul>
        <div class="panel-body">
            <div class="row">
                <div class="col-xs-4">
                    <label for="sender_3dRep" title=""><span class="glyphicon glyphicon-user"></span> Технолог (кто отправил в ремонт):</label>
                    <div class="input-group">
                        <input required type="text" title="Технолог" <?=$isPaid?'disabled':''?> class="form-control sender" name="<?=$repairRow['sender']?>" value="<?=$repair['sender']?>">
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="caret"></span>
                            </button>
                            <?php if ( !$isPaid ): ?>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <li><a elemtoadd="">Дзюба В.М.</a></li>
                                    <li><a elemtoadd="">Занин В.А.</a></li>
                                    <li><a elemtoadd="">Бондаренко А.</a></li>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-xs-4">
                    <label for="toWhom_3dRep" title=""><span class="glyphicon glyphicon-user"></span> Мастер (кто будет делать):</label>
                    <div class="input-group">
                        <input required type="text" <?=$isPaid?'disabled':''?> class="form-control toWhom" title="Мастер" name="<?=$repairRow['toWhom']?>" value="<?=$repair['toWhom']?>">
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="caret"></span>
                            </button>
                            <?php if ( !$isPaid ): ?>
                                <ul class="dropdown-menu dropdown-menu-right toWhomList">
                                    <?=$masterLI?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12">
                    <label for="repairs_descr_need" class=""><span class="glyphicon glyphicon-comment"></span> Причина ремонта (что нужно сделать): </label>
                    <textarea required class="form-control repairs_descr_need" <?=$isPaid?'disabled':''?> title="Причина ремонта" rows="2" name="<?=$repairRow['descrNeed']?>"><?=$repair['descrNeed']?></textarea>
                    <input type="hidden" class="repairs_id"  name="<?=$repairRow['id']?>" value="<?=$repair['id']?>"/>
                    <input type="hidden" class="repairs_num" name="<?=$repairRow['num']?>" value="<?=$repair['rep_num']?>"/>
                    <input type="hidden" class="repairs_which" name="<?=$repairRow['which']?>" value="<?= $repair['which'] ?>"/>
                </div>
            </div>
        </div>
        <ul class="list-group">
            <li class="list-group-item list-group-item-success text-center" style="border-top: 2px solid #55a456!important; ">
                <i class="fas fa-bezier-curve"></i> <b><i>Мастер</i></b>
            </li>
            <li class="list-group-item">
                <label for="repairs_descr_done" class=""><span class="glyphicon glyphicon-comment"></span> Описание (что сделано): </label>
                <textarea class="form-control repairs_descr_done" rows="2" <?=$isPaid?'disabled':''?> name="<?=$repairRow['descrName']?>"><?=$repair['repair_descr']?></textarea>
            </li>
            <li class="list-group-item">
                <i class="fas fa-dollar-sign"></i>
                <strong><i>Стоимость ремонта</i></strong>
                <?php if ( !$this->isCredited($repair['prices']??[], $repairRow['is3d_grade']) ): ?>
                    <button class="btn btn-sm btn-default pull-right repairPriceAdd" style="top:-5px !important; position:relative;" data-toggle="modal" data-target="#repairPricesModal" type="button" title="Добавить оценку">
                        <span class="glyphicon glyphicon-plus"></span>
                    </button>
                <?php endif; ?>
            </li>
        </ul>
        <table class="table">
            <thead>
                <tr class="thead11">
                    <th>№</th>
                    <th width="30%">Название</th>
                    <th width="30%">Стоимость</th>
                    <th>Статус</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="<?=$repairRow['whichID']?>">
            <!-- // автозаполнение -->
            <?php $pr_total = 0; $repairPriceStatus = 0; $repairPricePaid = 0; $priceNum = 0; ?>
            <?php foreach ( $repair['prices']??[] as $repairPrice ): ?>
                <tr data-gradeID="<?= $repairPrice['gs_id'] ?>">
                    <td style="width: 30px"><?= ++$priceNum ?></td>
                    <td>
                        <?php $mpTitle = $repairPrice['cost_name'];
                            foreach ( $gradingSystem as $gsRow )
                                if ( $gsRow['id'] == $repairPrice['gs_id'] )
                                    $mpTitle = $gsRow['description'];
                        ?>
                        <div class="cursorPointer lightUpGSRow" data-toggle="tooltip" data-placement="bottom" title="<?=$mpTitle?>" style="width: 100%">
                            <?=$repairPrice['cost_name'] ?>
                        </div>
                    </td>
                    <td>
                        <?= $repairPrice['value'] ?>
                        <input hidden class="hidden" value="<?= $repairPrice['value'] ?>" name="">
                    </td>
                    <?php $pr_total += $repairPrice['value']; ?>
                    <td>
                        <?php if ( !$this->isCredited($repair['prices'], 8) ): ?>
                            <input hidden class="hidden" value="<?= $repairPrice['id'] ?>"    name="">
                            <input hidden class="hidden" value="<?= $repairPrice['gs_id'] ?>" name="">
                        <?php endif; ?>
                        <?php $repairPriceStatus = (int)$repairPrice['status'] ?>
                        <?php $repairPricePaid = (int)$repairPrice['paid'] ?>
                    </td>
                    <td style="width:100px;">
                        <?php if ( !$repairPriceStatus ): ?>
                            <button class="btn btn-sm btn-default repDellGrade" type="button" title="Удалить Оценку">
                                <span class="glyphicon glyphicon-trash"></span>
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <tr class="active text-bold t-total">
                <td style="width: 30px">
                </td>
                <td>Всего: </td>
                <td><?= $pr_total; ?></td>
                <td>
                    <?php if ( $priceNum ): ?>
                        <?php if ( $repairPriceStatus === 1 ): ?>
                            <span class="label label-primary ">Зачислено!</span>
                        <?php else: ?>
                            <span class="label label-default ">Не зачислено!</span>
                        <?php endif; ?>
                        <?php if ( $repairPricePaid === 1 ): ?>
                            <span class="label label-success ">Оплачено!</span>
                        <?php else: ?>
                            <span class="label label-default ">Не Оплачено!</span>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
                <td style="width:100px;"></td>
            </tr>
            </tbody>
        </table>
        <ul class="list-group">
            <li class="list-group-item pt-0 list-group-item-success"></li>
        </ul>
        <div class="panel-footer">
            <div class="row">
                <div class="col-xs-12 col-sm-2">
                    <span class="pull-right pt-1">
                        <span class="glyphicon glyphicon-ok"></span> <b><i>Статус ремонта: </i></b>
                    </span>
                </div>
                <div class="col-xs-12 col-sm-10">
                    <select class="form-control repairStatus" <?=$isPaid?'disabled':''?> name="<?=$repairRow['status']?>">
                        <option data-repairFor="id_repair" <?= $repair['status'] == 1 ? 'selected':'' ?> value="1" title="Новый ремонт. Недавно создан.">Новый</option>
                        <option data-repairFor="id_repair" <?= $repair['status'] == 2 ? 'selected':'' ?> value="2" title="Создан. Ожидает принятия в работу.">Ожидает принятия</option>
                        <option data-repairFor="id_repair" <?= $repair['status'] == 3 ? 'selected':'' ?> value="3" title="Принят в работу. Над ним сейчас трудится мастер">В работе</option>
                        <option data-repairFor="id_repair" <?= $repair['status'] == 4 ? 'selected':'' ?> value="4" title="Ремонт завершен">Завершен</option>
                    </select>
                    <input type="hidden" class="form-control statusDate hidden" name="<?=$repairRow['statusDate']?>" value="<?=$repair['status_date']?>">
                    <input type="hidden" class="form-control date hidden" name="<?=$repairRow['date']?>" value="<?=$repair['date']?>">
                </div>
            </div>
        </div>
    </div>

</div>
<?php unset($repairRow); ?>

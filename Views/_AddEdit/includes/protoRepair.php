<?php
$repairRow = [
    'isHidden' => 'hidden',
    'idProto' => 'protoRepairs',
];
$isRepairProto = $isRepairProto??'';
if ( !$isRepairProto??'' )
{
    $repairRow = [
        'isHidden' => '',
        'idProto' => '',
    ];
    $repairRow['which'] = $whichRepair ? 'repairsJew' : 'repairs3d';
    $repairRow['whichName'] = $whichRepair ? 'Ремонт Модельера-доработчика №' : '3Д Ремонт №';
    $repairRow['panelColor'] = $whichRepair ? 'panel-jewRepair' : 'panel-3dRepair';
    $repairRow['panelIcon'] = $whichRepair ? 'wrench' : 'cog';



    if ( isset($repair['rep_num']) ) $repairRow['number'] = $repair['rep_num'];
    if ( isset($repair['date']) ) $repairRow['date'] = $repair['date'];
    if ( isset($repair['repair_descr']) ) $repairRow['description'] = $repair['repair_descr'];
    if ( isset($repair['id']) ) $repairRow['id'] = $repair['id'];
    if ( isset($repair['cost']) ) $repairRow['cost'] = $repair['cost'];
}
?>
<div id="<?=$repairRow['idProto']?>" class="panel <?=$repairRow['panelColor']?> <?=$repairRow['isHidden']?> <?=$repairRow['which']?> <?=!$isView? 'mt-2':'mb-1'?>">
    <div class="panel-heading">
        <span class="glyphicon glyphicon-<?=$repairRow['panelIcon']?>"></span>
        <strong>
            <span class="repairs_name"><?=$repairRow['whichName']?></span><span class="repairs_number"><?=$repairRow['number']?></span>
            от - <span class="repairs_date"><?=date_create( $repairRow['date'] )->Format('d.m.Y')?></span>
        </strong>
    </div>
    <textarea <?= !$isView ? '' : 'readonly'?> class="form-control repairs_descr" rows="3" name="<?=$repairRow['descrName']??''?>"><?=$repairRow['description']?></textarea>

    <? if( $repair['paid'] ) : ?>
        <div class="w100 pb-1 brb-3-success"></div>
    <?endif;?>
</div>
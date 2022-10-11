<?php
use widgets\Paginator;
use models\User;
use libs\classes\URLCrypt;

$paymentManager = User::permission('paymentManager');
?>
<div class="row pl-3 pr-3 mt-2 allmodels">
    <?php
    $wholeTotal = 0; $priceIDsAll = '';  $totalCosts = 0;
    $countStock = count($stockInfo??[]);
    $columns = [0=>'', 1=>'', 2=>''];
    $c = 0;
    $modelIDs = '';
    ob_start();
    ?>
    <?php foreach ( $stockInfo as $stockModel ): ?>
        <?php $panelID = "allModels_" . $stockModel['id']; $collapseID = "collapseAllModels_" . $stockModel['id']; $modelIDs .= $stockModel['id'] . ";" ?>
        <div class="panel panel-default mb-1">
            <div class="panel-heading p0 cursorPointer relative" role="tab" id="<?=$panelID?>">
                <span class="panel-title modelInfo" role="button" data-toggle="collapse" aria-expanded="false" aria-controls="<?=$collapseID?>">
                    <img src="<?=$stockModel['img_name']?>" class="thumbnail mb-0 d-inline" style="max-height: 60px; max-height: 60px;" />
                    <?= ($stockModel['number_3d']??'') . "/" . ($stockModel['vendor_code']??'') . " - " . ($stockModel['model_type']??'') ?>
                </span>
                <a role="button" title="Просмотр модели" class="btn btn-sm btn-info modelHref absolute" style="top: 0; right: 0" href="/model-view/?id=<?=$stockModel['id']?>"><span class="glyphicon glyphicon-eye-open modelHref"></span></a>
            </div>
            <div id="<?=$collapseID?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="<?=$panelID?>" aria-expanded="false" style="height: 0;">
                <ul class="list-group">
                    <?php $total = 0; ?>
                    <?php foreach ( $modelPrices??[] as $modelID => $prices ): ?>
                        <?php if ( $modelID != $stockModel['id'] ) continue; ?>
                        <?php $priceIDs = ''; $totalCosts += count($prices??[])?>
                        <?php foreach ( $prices as $price ): ?>
                            <?php $total += $price['value']; $wholeTotal += $price['value']; $singlePriceIDs=''; ?>
                            <li class="list-group-item">
                                <span class="priceName_value" ><?= $price['cost_name'] . " - " .  $price['value'] . "грн.  -  " . date_create( $price['date'] )->Format('d.m.Y'); ?></span>
                                <br>
                                <?php if ( $price['status'] ):?>
                                    <span class="label label-primary ">Зачислено!</span>
                                <?php else: ?>
                                    <span class="label label-default ">Не зачислено!</span>
                                <?php endif; ?>
                                <?php if ( $price['paid'] ):?>
                                    <span class="label label-success ">Оплачено!</span>
                                <?php else: ?>
                                    <span class="label label-default ">Не Оплачено!</span>
                                    <?php if ( $price['status'] && $paymentManager ): ?>
                                        <?php $priceIDs .=  $price['is3d_grade'] == 1 ? $price['ids_3d'] : $price['id'] . ';' ?>
                                        <?php $priceIDsAll .= $singlePriceIDs .=  $price['is3d_grade'] == 1 ? $price['ids_3d'] : $price['id'] . ';' ?>
                                        <?php $sPriceID_crypt = URLCrypt::strEncode($singlePriceIDs); ?>
                                        <?php $modelID_crypt = URLCrypt::strEncode($stockModel['id']); ?>
                                        <button type="button" data-toggle="modal" data-prices="single" data-priceID="<?=$sPriceID_crypt?>" data-posID="<?=$modelID_crypt?>" data-target="#paymentModal" class="btn btn-sm btn-default absolute" style="right: 10px; top: 50%; margin-top: -15px;" title="Оплатить">
                                            <span class="glyphicon glyphicon-usd"></span>
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </ul>
                <div class="panel-footer text-bold relative">
                    <span>Всего: <?= $total?> грн. </span>
                    <?php if ( $tabID === 3 && $paymentManager ): ?>
                        <?php $priceIDs_crypt = URLCrypt::strEncode($priceIDs); ?>
                        <?php $modelID_crypt = URLCrypt::strEncode($stockModel['id']); ?>
                        <button type="button" data-toggle="modal" data-prices="allInModel" data-priceID="<?=$priceIDs_crypt?>" data-posID="<?=$modelID_crypt?>" data-target="#paymentModal" class="btn btn-sm btn-success absolute" style="right: 10px; top: 50%; margin-top: -15px;" title="Оплатить Все"><span class="glyphicon glyphicon-usd"></span></button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php $columns[$c] .= ob_get_contents(); ?>
        <?php ob_clean(); ?>
        <?php $c++; ?>
        <?php if ( !($c % 3) ) $c = 0; ?>
    <?php endforeach; ?>
    <?php ob_end_clean(); ?>
    <div class="col-xs-12 col-sm-6 col-md-4 pr-0 pl-0"><?= $columns[0] ?></div>
    <div class="col-xs-12 col-sm-6 col-md-4 pr-0 pl-0"><?= $columns[1] ?></div>
    <div class="col-xs-12 col-sm-6 col-md-4 pr-0 pl-0"><?= $columns[2] ?></div>
    <div class="col-xs-12">
        <div class="row alert alert-info text-bold pl-3 pr-3 mt-2 p1" role="alert">
            <div class="col-sm-12 col-md-4 pr-0 pl-0 pt-2">
                <span title="Показано моделей и прайсов в них" class="cursorArrow" data-toggle="tooltip">
                    <i>Моделей: </i><?=$countStock?> | <i>Прайсов: </i><?=$totalCosts?> | <i>Сумма: </i><?=number_format($wholeTotal,0,',',' ');?> грн.
                </span>    
            </div>
            <div class="col-sm-12 col-md-4 pr-0 pl-0"><?php
                echo Paginator::widget([
                    'pagination' => $pagination,
                    'options' => [
                        'template' => _globDIR_ . "paginator_tpl.php",
                        'squaresPerPage' => 5,
                        'size' => 'small', // large | small
                        'color' => '',
                        'class' => 'pagination m0 pull-right mt-1 mb-1',
                    ],
                ]);
            ?></div>
            <div class="col-sm-12 col-md-4 pr-0 pl-0 pt-2 text-right">
                <span title="Всего оцененных изделий" class="cursorArrow" data-toggle="tooltip">
                    Всего: <i>Моделей</i> - <?=$totalM?> | <i>Прайсов</i> - <?=$totalMP?>
                </span>
                <?php if ( $tabID === 3 && $paymentManager ): ?>
                <span class="pull-right">
                <?php $priceIDsAll = $priceIDsAll ? URLCrypt::strEncode($priceIDsAll):'' ?>
                    <?php $modelIDs = $modelIDs ? URLCrypt::strEncode($modelIDs):'' ?>
                    <?php if ( $modelIDs ): ?>
                        <button type="button" data-toggle="modal" data-prices="all" data-priceID="<?=$priceIDsAll?>" data-posID="<?=$modelIDs?>" data-target="#paymentModal" class="btn btn-sm btn-danger relative ml-1" style="top: -4px;" title="Оплатить Все">
                            <span class="glyphicon glyphicon-usd"></span>
                            Оплатить Все
                        </button>
                    <?php endif; ?>
                </span>
                <div class="clearfix"></div>
            <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php
if (!$assist = $this->session->assist)
    throw new \Error('Unable to find assist variable!', 500);

$wcSortedFull = $wcSortedFull??[];
?>

<div id="modalStatuses" data-iziModal-fullscreen="true" data-iziModal-title="Вабрать модели по статусу. Текущий выбор: <b><?=$assist['regStat']?></b>">
    <span id="currentSelectedStatus" class="hidden"><?=$assist['regStat']?></span>
    <div id="modalContent" style="padding: 10px" class="hidden">
        <div class="row">
            <div class="col-xs-1" >
                <button id="openAll" title="Раскрыть Все" onclick="event.preventDefault()" style="margin-bottom: 10px" class="pull-left btn btn-sm btn-info"><span class="glyphicon glyphicon-menu-left"></span> Раскрыть Все</button>
                <button id="closeAll" title="Закрыть Все" onclick="event.preventDefault()" style="margin-bottom: 10px" class="pull-left hidden btn btn-sm btn-primary"><span class="glyphicon glyphicon-menu-down"></span> Закрыть Все</button>
            </div>
            <div class="col-xs-1" >
                <a title="Сбросить" href="/main/?regStat=none" style="margin-bottom: 10px" class="pull-right btn btn-sm btn-success"><span class="glyphicon glyphicon-remove"></span> Нет</a>
            </div>
            <div class="col-xs-10" >
                <!-- Rounded switch -->
                <label class="switchByStatusHistory">
                    <input type="checkbox" <?=$assist['byStatHistory']?'checked':''?> name="byHistory" id="byStatusHistory">
                    <span class="slider round"></span>
                </label>
                <span title="Добавляет модель к выборке, если выбранный статус когда-либо был поставлен" style="vertical-align: middle; font-weight: <?=$assist['byStatHistory']?'bold':'normal'?>;"><?=$assist['byStatHistory']?'Поиск в истории включен!':'Поиск в истории выключен'?></span>
                <span class="byStatHistory_dates <?=$assist['byStatHistory']?'':'hidden'?>" title="Выбрать даты постановки статуса">
                    &nbsp;&nbsp;от: <input type="date" class="input-sm" name="byStatHistoryFrom" value="<?=$assist['byStatHistoryFrom']??'' ?>">
                    &nbsp;&nbsp;до: <input type="date" class="input-sm" name="byStatHistoryTo" value="<?=$assist['byStatHistoryTo']??'' ?>">
                </span>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="row">
            <?php
                $countWC = count($wcSortedFull);
                $columns = [
                    0=>'',
                    1=>'',
                    2=>'',
                    3=>'',
                ];
                $c = 0;
                ob_start();
            ?>
            <?php
                //У людей проблемы с открытием статусов на компах под Win Xp chrome 40 - 49
            ?>
            <?php foreach ( $wcSortedFull as $wcName => $workingCenter ) :?>
                <div class="panel panel-info mb-1" style="position:relative;">
                    <div class="panel-heading">
                        <?=$wcName?>
                        <button title="Раскрыть" onclick="event.preventDefault()" data-status="0" class="btn btn-sm btn-info statusesChevron"><span class="glyphicon glyphicon-menu-left"></span></button>
                    </div>
                    <div class="panel-body pb-0 statusesPanelBody statusesPanelBodyHidden">
                        <?php foreach ( $workingCenter as $subUnit ) :?>
                            <div class="list-group">
                                <a class="list-group-item list-group-item-success"><?=$subUnit['descr']?></a>
                                <?php foreach ( $subUnit['statuses']??[] as $status ) :?>
                                    <a title="<?=$status['title'];?>" class="list-group-item wc-status-item" href="/main/?regStat=<?=$status['id']?>">
                                        <span class="glyphicon glyphicon-<?=$status['glyphi']?>"></span>
                                        <span><?=$status['name_ru']?></span>
                                    </a>
                                <?php endforeach; ?>
                                <a title="Ответственный" class="list-group-item list-group-item-danger"><?=$subUnit['user']?></a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php $columns[$c] .= ob_get_contents(); ?>
                <?php ob_clean(); ?>
                <?php $c++; ?>
                <?php if ( !($c % 4) ) $c = 0; ?>
            <?php endforeach; ?>

            <?php ob_end_clean(); ?>

            <div class="col-xs-3" style="padding-right: 2px;"><?php echo $columns[0] ?></div>
            <div class="col-xs-3" style="padding: 0 2px 0 2px; "><?php echo $columns[1] ?></div>
            <div class="col-xs-3" style="padding: 0 2px 0 2px; "><?php echo $columns[2] ?></div>
            <div class="col-xs-3" style="padding-left: 2px;"><?php echo $columns[3] ?></div>
        </div>
    </div>
</div>
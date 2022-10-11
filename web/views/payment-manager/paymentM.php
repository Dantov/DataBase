<?php
use soffit\HtmlHelper;

// Указатель на вид, Менеджер или Кошелек
$pmView = $pmView??false;

$currentWorker = $this->session->getKey('currentWorker'); 
$tabID = (int)$this->request->get('tab');


HtmlHelper::defineURLParams([
    'ppCount' => $ppCount,
	'tab'    => $tabID,
	'worker' => $workerID,
	'year'   => $yearID,
	'month'  => $monthID,
	'page'   => $page,
	'searchForPM' => $searchForValue,
]);

$tabName = '';
switch ($tab??'all')
{
    case 'all': $tabName = "Всех моделей"; break;
    case 'paid': $tabName = "Оплаченные"; break;
    case 'notpaid': $tabName = "Не оплаченные"; break;
    case 'notCredited': $tabName = "Не Зачисленные"; break;
}
?>
<div class="row">
    <p class="lead text-info text-center"><span class="glyphicon glyphicon-credit-card" aria-hidden="true"></span> <?= $title ?></p>
    <div class="col-xs-12 stats_table">

    	<div class="btn-group btn-group-sm pull-right">
            <?php if ( $pmView ): ?>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="currentWorkerName"><?= $currentWorker['fio'] ?></span> <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a href="<?= HtmlHelper::URL('/',['worker'=>0]) ?>">Все работники</a></li>
                        <li role="separator" class="divider"></li>
                        <?php foreach ($usersList as $user): ?>
                            <li><a href="<?=HtmlHelper::URL('/',['worker'=>$user['id']])?>"><?=$user['fio']?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
			<div class="btn-group btn-group-sm" role="group">
				<button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<?= $monthID ? getMonthRu($monthID) : "Все месяцы"?> <span class="caret"></span>
				</button>
				<ul class="dropdown-menu">
					<li><a href="<?=HtmlHelper::URL('/',['month'=>date('n')])?>">Текущий месяц</a></li>
					<li><a href="<?=HtmlHelper::URL('/',['month'=>0])?>">Все</a></li>
					<li role="separator" class="divider"></li>
					<?php for ( $m = 1; $m <= 12; $m++ ) : ?>
						<li><a href="<?=HtmlHelper::URL('/',['month'=>$m])?>"><?=getMonthRu($m)?></a></li>
				    <?php endfor; ?>
				</ul>
			</div>
			<div class="btn-group btn-group-sm" role="group">
				<button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<?= $yearID ?: "Текущий год" ?> <span class="caret"></span>
				</button>
				<ul class="dropdown-menu">
					<li><a href="<?=HtmlHelper::URL('/',['year'=>date('Y')])?>">Текущий год</a></li>
					<li role="separator" class="divider"></li>
					<?php for( $y = 2020; $y <= date('Y'); $y++ ): ?>
						<li><a href="<?=HtmlHelper::URL('/',['year'=>$y])?>"><?=$y?></a></li>
					<?php endfor; ?>
                    <li><a href="<?=HtmlHelper::URL('/',['year'=>2019])?>">2019</a></li>
				</ul>
			</div>
            <button type="button" class="btn btn-default" id="openAllModels"><span class="glyphicon glyphicon-calendar" aria-hidden="true"></span> <span class="t">Развернуть все</span></button>
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-default dropdown-toggle" title="Кол-во отображаемых позиций" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <?= $ppCount ?> <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                    <?php for ( $ppc = 18; $ppc <= 120; $ppc = $ppc+6 ) : ?>
                        <li><a href="<?=HtmlHelper::URL('/',['ppCount'=>$ppc])?>"><?=$ppc?></a></li>
                    <?php endfor; ?>
                </ul>
            </div>
            <div class="input-group input-group-sm" role="group" style="display: inline-flex;">
                <form action="<?=HtmlHelper::URL('/',['searchPM'=>1])?>" method="get" enctype="text/plain">
                    <div class="input-group ">
                        <input placeholder="Поиск..." type="text" class="form-control input-sm searchInputPM" aria-label="..." name="searchForPM" title="поиск конкретной модели" value="<?= $searchForValue ?>">
                        <div class="input-group-btn">
                            <button class="btn btn-default btn-sm" type="submit" title="Нажать для поиска">
                                <span class="glyphicon glyphicon-search"></span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
		</div>

        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="<?= $tab == 'all'? 'active':'' ?>"><a href="<?=HtmlHelper::URL('/',['tab'=>1])?>">Все Модели</a></li>
            <li role="presentation" class="dropdown <?= in_array($tab,['paid','notpaid','notCredited']) ? 'active':'' ?>">
                <a href="#" id="tabOptions" class="dropdown-toggle cursorPointer" data-toggle="dropdown" aria-controls="tabOptions-contents" aria-expanded="false">
                    <?= in_array($tab,['paid','notpaid','notCredited']) ? $tabName : 'Ещё' ?> <span class="caret"></span>
                </a>
                <ul class="dropdown-menu" aria-labelledby="tabOptions" id="tabOptions-contents">
                    <li role="presentation" class="<?= $tab == 'notCredited'? 'active':'' ?>">
                        <a href="<?=HtmlHelper::URL('/',['tab'=>4])?>" >Не зачисленные</a>
                    </li>
                    <li role="presentation" class="<?= $tab == 'notpaid'? 'active':'' ?>">
                        <a href="<?=HtmlHelper::URL('/',['tab'=>3])?>">Не оплаченные</a>
                    </li>
                    <li role="presentation" class="<?= $tab == 'paid'? 'active':'' ?>">
                        <a href="<?=HtmlHelper::URL('/',['tab'=>2])?>" >Оплаченные</a>
                    </li>
                </ul>
            </li>
            <li role="presentation" class=""><a href="#statistic" aria-controls="statistic" role="tab" data-toggle="tab">Статистика</a></li>
            <li role="presentation" class=""><a href="#help" aria-controls="help" role="tab" data-toggle="tab">Помощь</a></li>
        </ul>

        <div class="tab-content">
            <div role="tabpanel" class="tab-pane active in fade" id="allModels">
                <?php require_once dirname(__DIR__) . "/user-pouch/allModelsTab.php"; ?>
            </div>

            <div role="tabpanel" class="tab-pane" id="statistic">
                <p></p>
                <div class="panel panel-default">
                    <div class="panel-heading text-center text-bold">Всего из <?= $tabName ?> для <?= $currentWorker['fio']??'' ?></div>
                    <div class="panel-body">
                        <p>Статистика начислений. Сколько начислено, получено, в ожидании и т.д. </p>
                    </div>
                    <ul class="list-group text-bold">
                        <li class="list-group-item list-group-item-success">Возможно оплатить: <span class="pull-right"><?= $statistic['notpaid'] ?> грн.</span></li>
                        <li class="list-group-item list-group-item-info">Не зачисленные: <span class="pull-right"><?= $statistic['waiting'] ?> грн.</span></li>
                        <li class="list-group-item list-group-item-danger">Оплаченные: <span class="pull-right"><?= $statistic['paid'] ?> грн.</span></li>
                    </ul>
                </div>
            </div>

            <div role="tabpanel" class="tab-pane" id="help">
                <p></p>
                <div class="panel panel-default">
                    <div class="panel-heading text-center text-bold">Список добавления/зичислений стоимостей</div>
                    <div class="panel-body"></div>
                    <ul class="list-group">
                        <li class="list-group-item">
                            1) Эскиз - создаёт <i>Дизайнер Юв.изделий</i> (Дзюба). <b>Добавляет</b> стоимость за эскиз, дизайнеру (Дзюбе).
                        </li>
                        <li class="list-group-item">
                            2) Утв. эскиза - ставит <i>Худ.совет</i> (Богдан). <b>Зачисляет</b> стоимость за эскиз, дизайнеру (Дзюбе).
                        </li>
                        <li class="list-group-item">
                            3) В работе 3Д  - ставит <i>3д моделлер</i>. <b>Добавляет</b> стоимость дизайнеру за курирование 3д (Дзюбе).
                        </li>
                        <li class="list-group-item">
                            4) На проверке – ставит <i>Технолог 3Д</i> (Быков) <b>Добавляет</b> стоимость технологу 3д. за просмотр 3д модели (Быкову)
                        </li>
                        <li class="list-group-item">
                            5) Подпись технолога – ставит <i>Техн.Юв.изделий</i> (Занин).
                            <b>Добавляет</b> стоимоть технологу за просмотр 3д модели и сопровождение (Занину).
                            А также <b>Зачисляет</b> дизайнеру за курирование 3д. (Дзюбе)
                        </li>
                        <li class="list-group-item">
                            6) Проверено – ставит <i>Технолог 3Д</i> (Быков). <b>Зачисляет</b> стоимость утверждение 3д модели – (Быкову и Занину).
                            <b>Зачисляет</b> за курирование 3д (Дзюбе). А также <b>Зачисляет</b> всю стоимость 3д моделлеру.
                        </li>
                        <li class="list-group-item">
                            7) Поддержки – ставит <i>Оператор 3д принтера</i> (Емельянцевы Слава/Влад).
                            <b>Добавляет</b> стоимость за создание поддержек (Емельянцеву Владу)
                        </li>
                        <li class="list-group-item">
                            8) Выращено - ставит <i>Оператор 3д принтера</i> (Емельянцевы Слава/Влад).
                            <b>Зачисляет</b> стоимость Емельянцеву Владу за поддержки.
                        </li>
                        <li class="list-group-item">
                            9) На сбыте – ставит <i>Участок ПДО</i>. <b>Зачисляет</b> стоимость Техн.Юв.Изделий (Занину) за сопровождение.
                            А так же <b>Зачисляет</b> стоимости модельерам за мастер модель.
                        </li>
                    </ul>
                </div>
            </div>
        </div><!-- end of Tab content -->

        <a class="btn btn-default" type="button" href="<?= $this->session->getKey('prevPage'); ?>"><span class="glyphicon glyphicon-triangle-left"></span> Назад</a>
    </div>
</div>
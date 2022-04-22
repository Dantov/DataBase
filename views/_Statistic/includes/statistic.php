<div class="row">
    <p class="lead text-info text-center">Статистика</p>
    <div class="col-xs-12 stats_table">

        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active"><a href="#tab1" role="tab" data-toggle="tab">Посетители</a></li>
            <li role="presentation"><a href="#tab2" role="tab" data-toggle="tab">Модели</a></li>
            <li role="presentation"><a href="#tab3" role="tab" data-toggle="tab">Общее</a></li>
        </ul>
        <div class="tab-content">
            <div role="tabpanel" class="tab-pane active in fade" id="tab1">
                <center><h4>Сейчас на сайте</h4></center>
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>№</th>
                        <th>ФИО</th>
                        <th>Пред посдедний запрос</th>
                        <th>Посдедний запрос</th>
                        <th>Устройство</th>
                        <th>Просмотров</th>
                        <th>Дата первого визита</th>
                        <th>Дата последнего визита</th>
                        <th>IP адрес</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ( is_array($usersOnline) ): ?>
                    <?php foreach ( $usersOnline as $num => $userOnline ): ?>
                        <tr>
                            <td><?= ++$num ?></td>
                            <td>(<?= "id_".$userOnline['user_id'] ?>) <?= $userOnline['fio'] ?></td>
                            <td><?= $userOnline['referer'] ?></td>
                            <td><?= $userOnline['last_uri'] ?></td>
                            <td><?= $userOnline['device'] ?></td>
                            <td><?= $userOnline['views_count'] ?></td>
                            <td><?= $userOnline['first_connect'] ?></td>
                            <td><?= $userOnline['update_connect'] ?></td>
                            <td><?= $userOnline['user_ip'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>

                    <tr class="warning">
                        <td colspan="9"></td>
                    </tr>
                    </tbody>
                </table>
            </div> <!-- end of panel 1 -->

            <div role="tabpanel" class="tab-pane fade" id="tab2">
                <center><h4>Коллекции в базе</h4></center>
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>№</th>
                        <th>Коллекция</th>
                        <th>Комплектов</th>
                        <th>Изделий</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr class="warning">
                        <td></td>
                        <td><b>Всего:</b></td>
                        <td><b><?=$complAll;?></b></td>
                        <td><b><?=$modelsAll;?></b></td>
                    </tr>
                    </tbody>
                </table>
                <center><h4>Топ 10 лайков/дизлайков</h4></center>
                <div class="row">
                    <div class="col-xs-6">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>№</th>
                                <th>Номер 3Д / Арт.</th>
                                <th>Лайки</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr class="warning">
                                <td></td>
                                <td><b></b></td>
                                <td><b></b></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-xs-6">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>№</th>
                                <th>Номер 3Д / Арт.</th>
                                <th>Дизлайки</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr class="warning">
                                <td></td>
                                <td><b></b></td>
                                <td><b></b></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div> <!-- end of panel 2 -->

            <div role="tabpanel" class="tab-pane fade" id="tab3">
                <div class="row">
                    <div class="col-xs-6">
                        <center><h5>Модельеры сделали</h5></center>
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>№</th>
                                <th>Имя</th>
                                <th>Кол-во комплектов</th>
                                <th>Кол-во моделей</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr class="warning">
                                <td></td>
                                <td><b>Всего</b></td>
                                <td><b>Комплектов: <?=$complAll;?></b></td>
                                <td><b>Моделей: <?=$modelsAll;?></b></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-xs-6">
                        <center><h5>За Авторством</h5></center>
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>№</th>
                                <th>Имя</th>
                                <th>Кол-во комплектов</th>
                                <th>Кол-во моделей</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr class="warning">
                                <td></td>
                                <td><b>Всего</b></td>
                                <td><b>Комплектов: <?=$complAll;?></b></td>
                                <td><b>Моделей: <?=$modelsAll;?></b></td>
                            </tr>
                            </tbody>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div> <!-- end of panel 3 -->

        </div><!-- end of Tab content -->
    </div>

</div><!--row-->
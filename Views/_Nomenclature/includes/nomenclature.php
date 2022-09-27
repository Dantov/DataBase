<?php
$tables = $tables??[];
$i = 0;
?>
<div class="row">
    <p class="lead text-info text-center">Списки Наименований</p>

    <div class="col-xs-12 stats_table">
        <?php require_once __DIR__."/nom_list.php" ?>
        <div class="tab-content">

            <!-- КОЛЛЕКЦИИ -->
            <div role="tabpanel" class="tab-pane active in fade" id="tab1">
                <br/>
                <table class="table table-hover">
                    <thead>
                    <tr class="thead11">
                        <th width="5%">№</th>
                        <th>Имя</th>
                        <th>Дата создания</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody data-tab="collections">
                    <?php foreach ($tables['collections']??[] as $i => $row) : ?>
                        <tr class="collsRow">
                            <td><?=$i+1;?></td>
                            <td>
                                <input type="text" class="form-control" data-tab="<?=$row['tab']?>" data-id="<?=$row['id'];?>" value="<?=$row['name'];?>">
                            </td>
                            <td><?=date_create( $row['date'] )->Format('d.m.Y');?></td>
                            <td>
                                <a class="btn btn-sm btn-default" type="button" role="button">
                                    <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php require __DIR__.'/plus.php'?>
                    <tr class="warning">
                        <td></td>
                        <td>Всего коллекций: <?=$i?></td>
                        <td></td>
                        <td></td>
                    </tr>
                    </tbody>
                </table>
                <?php if (isset($row)) unset($row) ?>
            </div> <!-- end of КОЛЛЕКЦИИ -->

            <!-- start of panel 2 -->
            <div role="tabpanel" class="tab-pane fade" id="tab2">
                <br/>
                <div class="row">

                    <div class="col-xs-6">
                        <!-- start КАМНИ -->
                        <table class="table table-hover">
                            <thead>
                            <tr class="thead11">
                                <th>№</th>
                                <th>Сырьё</th>
                                <th></th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody data-tab="gems_names">
                            <?php foreach ($tables['gems_names']??[] as $i => $row) : ?>
                                <tr class="collsRow">
                                    <td><?=$i+1;?></td>
                                    <td>
                                        <input type="text" class="form-control" data-tab="<?=$row['tab']?>" data-id="<?=$row['id'];?>" value="<?=$row['name'];?>">
                                    </td>
                                    <td></td>
                                    <td>
                                        <a class="btn btn-sm btn-default" type="button" role="button">
                                            <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php require __DIR__.'/plus.php'?>
                            <tr class="warning">
                                <td></td>
                                <td>Всего: <?=$i+1;?></td>
                                <td></td>
                                <td></td>
                            </tr>
                            </tbody>
                        </table>
                        <?php if (isset($row)) unset($row) ?>

                        <!-- gems КАМНИ ОГРАНКА -->
                            <table class="table table-hover">
                                <thead>
                                <tr class="thead11">
                                    <th>№</th>
                                    <th>Огранка</th>
                                    <th></th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody data-tab="gems_cut">
                                <?php foreach ($tables['gems_cut']??[] as $i => $row) : ?>
                                    <tr class="collsRow">
                                        <td><?=$i+1;?></td>
                                        <td>
                                            <input type="text" class="form-control" data-tab="<?=$row['tab']?>" data-id="<?=$row['id'];?>" value="<?=$row['name']; ?>">
                                        </td>
                                        <td></td>
                                        <td>
                                            <a class="btn btn-sm btn-default" type="button" role="button">
                                                <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php require __DIR__.'/plus.php'?>
                                <tr class="warning">
                                    <td></td>
                                    <td>Всего: <?=$i;?></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                </tbody>
                            </table> <!-- end gems cut -->
                            <?php if (isset($row)) unset($row) ?>


                            <!-- gems КАМНИ ЦВЕТА -->
                            <table class="table table-hover">
                                <thead>
                                <tr class="thead11">
                                    <th>№</th>
                                    <th>Цвета</th>
                                    <th></th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody data-tab="gems_color">
                                <?php foreach ($tables['gems_color']??[] as $i => $row) : ?>
                                    <tr class="collsRow">
                                        <td><?=$i+1; ?></td>
                                        <td>
                                            <input type="text" class="form-control" data-tab="<?=$row['tab']?>" data-id="<?=$row['id'];?>" value="<?=$row['name']; ?>">
                                        </td>
                                        <td></td>
                                        <td>
                                            <a class="btn btn-sm btn-default" type="button" role="button">
                                                <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php require __DIR__.'/plus.php'?>
                                <tr class="warning">
                                    <td></td>
                                    <td>Всего: <?=$i; ?></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                </tbody>
                            </table> <!-- end gems color -->
                        <?php if (isset($row)) unset($row) ?>
                    </div>
                    <!-- end of col-xs-6 -->


                    <div class="col-xs-6">
                        <!-- gems sizes start -->
                        <table class="table table-hover">
                            <thead>
                            <tr class="thead11">
                                <th>№</th>
                                <th>Размеры</th>
                                <th></th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody data-tab="gems_sizes">
                            <?php foreach ($tables['gems_sizes']??[] as $i => $row) : ?>
                                <tr class="collsRow">
                                    <td><?=$i+1;?></td>
                                    <td>
                                        <input type="text" class="form-control" data-tab="<?=$row['tab']?>" data-id="<?=$row['id'];?>" value="<?=$row['name'];?>">
                                    </td>
                                    <td></td>
                                    <td>
                                        <a class="btn btn-sm btn-default" type="button" role="button">
                                            <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php require __DIR__.'/plus.php'?>
                            <tr class="warning">
                                <td></td>
                                <td>Всего: <?php echo $i; ?></td>
                                <td></td>
                                <td></td>
                            </tr>
                            </tbody>
                        </table><!-- end gems sizes -->
                        <?php if (isset($row)) unset($row) ?>
                    </div><!-- end of col-xs-6 -->

                </div><!-- end row -->
            </div> <!-- end of panel 2 -->


            <!-- start of panel 3 -->
            <div role="tabpanel" class="tab-pane fade" id="tab3">

                <!-- model material start -->
                <div class="col-xs-12 col-sm-6">
                    <table class="table table-hover">
                        <thead>
                        <tr class="thead11">
                            <th>№</th>
                            <th>Возможные материалы изделий</th>
                            <th></th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody data-tab="model_material">
                        <?php foreach ($tables['model_material']??[] as $i => $row) : ?>
                            <tr class="collsRow">
                                <td><?=$i+1;?></td>
                                <td>
                                    <input type="text" class="form-control" data-tab="<?=$row['tab']?>" data-id="<?=$row['id'];?>" value="<?php echo $row['name'];?>">
                                </td>
                                <td></td>
                                <td>
                                    <a class="btn btn-sm btn-default" type="button" role="button">
                                        <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php require __DIR__.'/plus.php'?>
                        <tr class="warning">
                            <td></td>
                            <td>Всего: <?php echo $i; ?></td>
                            <td></td>
                            <td></td>
                        </tr>
                        </tbody>
                    </table>
                    <?php if (isset($row)) unset($row) ?>
                </div>
                <!-- model material end -->

                <!-- model covering start -->
                <div class="col-xs-12 col-sm-6">
                    <table class="table table-hover">
                        <thead>
                        <tr class="thead11">
                            <th>№</th>
                            <th>Покрытия</th>
                            <th></th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody data-tab="model_covering">
                        <?php foreach ($tables['model_covering']??[] as $i => $row) : ?>
                            <tr class="collsRow">
                                <td><?=$i+1;?></td>
                                <td>
                                    <input type="text" class="form-control" data-tab="<?=$row['tab']?>" data-id="<?=$row['id'];?>" value="<?=
                                    $row['name'];?>">
                                </td>
                                <td></td>
                                <td>
                                    <a class="btn btn-sm btn-default" type="button" role="button">
                                        <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php require __DIR__.'/plus.php'?>
                        <tr class="warning">
                            <td></td>
                            <td>Всего: <?php echo $i; ?></td>
                            <td></td>
                            <td></td>
                        </tr>
                        </tbody>
                    </table>
                    <?php if (isset($row)) unset($row) ?>
                </div>
                <!-- model covering end -->

                <!-- metal color start -->
                <div class="col-xs-12 col-sm-6">
                    <table class="table table-hover">
                        <thead>
                        <tr class="thead11">
                            <th>№</th>
                            <th>Цвета</th>
                            <th></th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody data-tab="metal_color">
                        <?php foreach ($tables['metal_color']??[] as $i => $row) : ?>
                            <tr class="collsRow">
                                <td><?=$i+1;?></td>
                                <td>
                                    <input type="text" class="form-control" data-tab="<?=$row['tab']?>" data-id="<?=$row['id'];?>" value="<?php echo $row['name'];?>">
                                </td>
                                <td></td>
                                <td>
                                    <a class="btn btn-sm btn-default" type="button" role="button">
                                        <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php require __DIR__.'/plus.php'?>
                        <tr class="warning">
                            <td></td>
                            <td>Всего: <?= $i; ?></td>
                            <td></td>
                            <td></td>
                        </tr>
                        </tbody>
                    </table>
                    <?php if (isset($row)) unset($row) ?>
                </div>
                <!-- model covering end -->

            </div><!-- end of panel 3 -->


            <!-- start of panel 4 -->
            <div role="tabpanel" class="tab-pane fade" id="tab4">

                <!-- authors start -->
                <div class="col-xs-12 col-sm-6">
                    <table class="table table-hover">
                        <thead>
                        <tr class="thead11">
                            <th>№</th>
                            <th>Авторы</th>
                            <th></th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody data-tab="author">
                        <?php foreach ($tables['author']??[] as $i => $row) : ?>
                            <tr class="collsRow">
                                <td><?=$i+1;?></td>
                                <td>
                                    <input type="text" class="form-control" data-tab="<?=$row['tab']?>" data-id="<?=$row['id'];?>" value="<?=$row['name'];?>">
                                </td>
                                <td></td>
                                <td>
                                    <a class="btn btn-sm btn-default" type="button" role="button">
                                        <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php require __DIR__.'/plus.php'?>
                        <tr class="warning">
                            <td></td>
                            <td>Всего: <?=$i;?></td>
                            <td></td>
                            <td></td>
                        </tr>
                        </tbody>
                    </table>
                    <?php if (isset($row)) unset($row) ?>
                </div><!-- authors end -->

                <!-- 3d modellers start -->
                <div class="col-xs-12 col-sm-6">
                    <table class="table table-hover">
                        <thead>
                        <tr class="thead11">
                            <th>№</th>
                            <th>3D модельеры</th>
                            <th></th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody data-tab="modeller3d">
                        <?php foreach ($tables['modeller3d']??[] as $i => $row) : ?>
                            <tr class="collsRow">
                                <td><?= $i+1 ?></td>
                                <td>
                                    <input type="text" class="form-control" data-tab="<?=$row['tab']?>" data-id="<?=$row['id'];?>" value="<?=$row['name'];?>">
                                </td>
                                <td></td>
                                <td>
                                    <a class="btn btn-sm btn-default" type="button" role="button">
                                        <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php require __DIR__.'/plus.php'?>
                        <tr class="warning">
                            <td></td>
                            <td>Всего: <?= $i; ?></td>
                            <td></td>
                            <td></td>
                        </tr>
                        </tbody>
                    </table>
                    <?php if (isset($row)) unset($row) ?>
                </div>
                <!-- 3d modellers end -->

                <!-- Jeweler start -->
                <div class="col-xs-12 col-sm-6">
                    <table class="table table-hover">
                        <thead>
                        <tr class="thead11">
                            <th>№</th>
                            <th>Модельеры-доработчики</th>
                            <th></th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody data-tab="jeweler_names">
                        <?php foreach ($tables['jeweler']??[] as $i => $row) : ?>
                            <tr class="collsRow">
                                <td><?= $i+1; ?></td>
                                <td>
                                    <input type="text" class="form-control" data-tab="<?=$row['tab']?>" data-id="<?=$row['id'];?>" value="<?=$row['name'];?>">
                                </td>
                                <td></td>
                                <td>
                                    <a class="btn btn-sm btn-default" type="button" role="button">
                                        <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php require __DIR__.'/plus.php'?>
                        <tr class="warning">
                            <td></td>
                            <td>Всего: <?= $i; ?></td>
                            <td></td>
                            <td></td>
                        </tr>
                        </tbody>
                    </table>
                    <?php if (isset($row)) unset($row) ?>
                </div>
                <!-- Jeweler end -->

                <!-- model type start -->
                <div class="col-xs-12 col-sm-6">
                    <table class="table table-hover">
                        <thead>
                        <tr class="thead11">
                            <th>№</th>
                            <th>Вид модели</th>
                            <th></th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody data-tab="model_type">
                        <?php foreach ($tables['model_type']??[] as $i => $row) : ?>
                            <tr class="collsRow">
                                <td><?=$i+1;?></td>
                                <td>
                                    <input type="text" class="form-control" data-tab="<?=$row['tab']?>" data-id="<?=$row['id'];?>" value="<?php echo $row['name'];?>">
                                </td>
                                <td></td>
                                <td>
                                    <a class="btn btn-sm btn-default" type="button" role="button">
                                        <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php require __DIR__.'/plus.php'?>
                        <tr class="warning">
                            <td></td>
                            <td>Всего: <?= $i; ?></td>
                            <td></td>
                            <td></td>
                        </tr>
                        </tbody>
                    </table>
                    <?php if (isset($row)) unset($row) ?>
                </div>
                <!-- model type end -->

                <!-- dop articles start -->
                <div class="col-xs-12 col-sm-6">
                    <table class="table table-hover">
                        <thead>
                        <tr class="thead11">
                            <th>№</th>
                            <th>Доп. Артикулы</th>
                            <th></th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody data-tab="vc_names">
                        <?php foreach ($tables['vc_names']??[] as $i => $row) : ?>
                            <tr class="collsRow">
                                <td><?=$i+1?></td>
                                <td>
                                    <input type="text" class="form-control" data-tab="<?=$row['tab']?>" data-id="<?=$row['id'];?>" value="<?= $row['name'];?>">
                                </td>
                                <td></td>
                                <td>
                                    <a class="btn btn-sm btn-default" type="button" role="button">
                                        <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php require __DIR__.'/plus.php'?>
                        <tr class="warning">
                            <td></td>
                            <td>Всего: <?=$i;?></td>
                            <td></td>
                            <td></td>
                        </tr>
                        </tbody>
                    </table>
                    <?php if (isset($row)) unset($row) ?>
                </div>
                <!-- model type end -->

            </div><!-- end of panel 4 -->

        </div><!-- end of Tab content -->

    </div><!--row-->

</div>
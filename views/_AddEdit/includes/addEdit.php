<?php
use Views\_Globals\Models\User;
use Views\vendor\core\Crypt;
$permittedFields = User::permissions();
?>
<div class="row mb-2">
    <div class="col-xs-12 col-sm-8">
        <div class="text-warning text-justify font" id="topName" style="font-size: 130%">
            <?php if ( $component === 1 ): ?>
                <strong>
                    <span><i class="far fa-file-alt"></i>&#160;&#160;Добавить новую модель</span>
                </strong>
            <?php elseif ( $component === 2 ):?>
                <img src="<?=$mainImage?>" height="64px" class="thumbnail mb-0" style="display: inline!important;" />
                <span class="glyphicon glyphicon-pencil"></span>
                Редактировать Модель <strong><?=$row['number_3d']." - ".$row['model_type'] ?></strong>
                <?php if ( count($complected??[]) ):?>
                    (<i>В Комплекте:
                    <?php foreach ($complected??[] as $complect) : ?>
                        <a class="imgPrev" imgtoshow="<?= $complect['img_name'] ?>" href="/add-edit/?id=<?=$complect['pos_id']?>&component=2"><?=$complect['model_type']?></a>
                    <?php endforeach;?>
                    </i>)
                <?php endif; ?>
            <?php elseif (  $component === 3 ):?>
                <span class="glyphicon glyphicon-duplicate"></span>
                <strong>Добавить комплект к </strong> <?=$row['number_3d']." - ".$row['model_type'] ?>
                <?php if ( count($complected??[]) ):?>
                    (<i>В Комплекте:
                        <?php foreach ($complected??[] as $complect) : ?>
                            <a class="imgPrev" imgtoshow="<?= $complect['img_name'] ?>" href="/add-edit/?id=<?=$complect['pos_id']?>&component=2"><?=$complect['model_type']?></a>
                        <?php endforeach;?>
                    </i>)
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-xs-12 col-sm-4">
        <div class="btn-group btn-group-sm pull-right" role="group" aria-label="...">
            <a role="button" class="btn btn-info" href="<?=$prevPage?>"><span class="glyphicon glyphicon-triangle-left"></span> Назад</a>
            <?php if ( $component === 2 ): ?>
                <a role="button" class="btn btn-info" href="/model-view/?id=<?=$id;?>" ><span class="glyphicon glyphicon-eye-open"></span> Просмотр</a>
            <?php endif; ?>
            <?php if ( $component === 2 ): ?>
                <?php if ( $permittedFields['addComplect'] === true ): ?>
                    <a role="button" class="btn btn-info" href="/add-edit/?id=<?=$id;?>&component=3" ><span class="glyphicon glyphicon-duplicate"></span> Добавить комплект</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<!-- конец заголовка -->


<!--MAIN FORM-->
<form method="POST" id="addform" enctype="multipart/form-data">

    <ul class="nav nav-tabs text-center" id="tab_roller">
        <li role="presentation" class="active" title="Текстовая информация"><a href="#baseData" role="tab" data-toggle="tab"><i class="fas fa-file-alt"></i> Текстовые Данные</a></li>
        <?php if ( $permittedFields['repairs'] ): ?>
            <li role="presentation" class="" title="Управление ремонтами"><a href="#repairsData" role="tab" data-toggle="tab"><i class="fas fa-tools"></i> Ремонты</a></li>
        <?php endif; ?>
        <?php if ( $permittedFields['files'] ): ?>
            <li role="presentation" class="" title="Файлы"><a href="#filesData" role="tab" data-toggle="tab"><i class="far fa-images"></i> Файлы</a></li>
        <?php endif; ?>
        <?php if (  $permittedFields['modelAccount'] ): ?>
            <li role="presentation" class="" title="Стоимость работ по этой модели"><a href="#pricesData" role="tab" data-toggle="tab"><i class="fas fa-hand-holding-usd"></i> Стоимости</a></li>
        <?php endif; ?>
    </ul> 
    <div class="tab-content">
                        
        <!-- ******************** TEXT ******************** -->
        <div role="tabpanel" class="tab-pane in fade pt-1 active" id="baseData">
            <div class="row">
                <?php if ( $permittedFields['number_3d'] ): ?>
                    <div class="col-sm-6">
                        <div class="form-group" title="По нему формируются комплекты. '000' вводить не обязательно.">
                            <label for="number_3d">
                                <i class="fas fa-hashtag"></i>
                                номер 3D: <?=$component === 1?' Вносится автоматически. Можно изменить при редактировании.':''?>
                            </label>
                            <input <?=($component===1||$component===3)?'readonly':''?> id="num3d" type="text" name="number_3d" class="form-control" value="<?= $row['number_3d']??'' ?>">
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ( $permittedFields['vendor_code'] ): ?>
                    <div class="col-sm-6">
                        <div class="form-group" title="Добавляется во все изделия в комплекте (если там было пусто)">
                            <label for="shortName">
                                <i class="fas fa-industry"></i>
                                Фабричный артикул:
                            </label>
                            <input id="vendor_code" type="text" name="vendor_code" class="form-control" value="<?=$row['vendor_code']??''?>" />
                        </div>
                    </div>
                <?php endif; ?>
            </div> <!--end row-->

            <div class="row">
                <?php if ( $permittedFields['author'] ): ?>
                    <div class="col-sm-4 ">
                        <label for="author"><span class="glyphicon glyphicon-user"></span> Автор:</label>
                        <div class="input-group">
                            <input required type="text" class="form-control" aria-label="..." name="author" id="author" value="<?=$row['author']?>" >
                            <div class="input-group-btn">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu">
                                    <?=$authLi;?>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>


                <?php if ( $permittedFields['modeller3d'] ): ?>
                    <div class="col-sm-4 ">
                        <label for="modeller3d"><span class="glyphicon glyphicon-user"></span> 3Д модельер:</label>
                        <div class="input-group">
                            <input required type="text" class="form-control" aria-label="..." name="modeller3d" id="modeller3d" value="<?=$row['modeller3D']?>">
                            <div class="input-group-btn">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <?=$mod3DLi;?>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ( $permittedFields['jewelerName'] ): ?>
                    <div class="col-sm-4 ">
                        <label for="jewelerName"><span class="glyphicon glyphicon-user"></span> Доработчик:</label>
                        <div class="input-group">
                            <input type="text" class="form-control" aria-label="..." name="jewelerName" value="<?=$row['jewelerName']?>">
                            <div class="input-group-btn">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <?=$jewelerNameLi;?>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="col-xs-12">
                    <br/>
                </div>

                <?php if ( $permittedFields['model_type'] ): ?>
                    <div class="col-xs-3">
                        <label for="model_type" class=""><span class="glyphicon glyphicon-eye-open"></span> Вид модели:</label>
                        <div class="input-group ">
                            <input required  type="text" id="modelType" class="form-control" aria-label="..." name="model_type" value="<?=($component===3)?'':$row['model_type']?>" />
                            <div class="input-group-btn">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <?=$modTypeLi;?>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ( $permittedFields['size_range'] ): ?>
                    <div class="col-xs-3">
                        <label for="model_weight"><i class="fab fa-quinscape"></i> Размерный Ряд:</label>
                        <input type="text" class="form-control" name="size_range" value="<?=$row['size_range']?>" />
                    </div>
                <?php endif; ?>

                <?php if ( $permittedFields['model_weight'] ): ?>
                    <div class="col-xs-2 ">
                        <label for="model_weight"><span class="glyphicon glyphicon-scale"></span> Вес 3D:</label>
                        <input step="0.01" type="number" class="form-control" required id="modelWeight" name="model_weight" value="<?=($component===3)?'':$row['model_weight'] ?>">
                    </div>
                <?php endif; ?>

                <?php if ( User::getAccess() === 8 ): ?>
                    <div class="col-xs-2 ">
                        <label for="model_weight"><span class="glyphicon glyphicon-usd"></span>	Стоимость печати:</label>
                        <input type="text" class="form-control" name="print_cost" value="<?=$row['print_cost'];?>" />
                    </div>
                <?php endif; ?>

                <?php if ( User::getAccess() === 8 && User::permission('MA_modellerJew') ): ?>
                    <div class="col-xs-2 ">
                        <label for="work_cost"><span class="glyphicon glyphicon-usd"></span> Стоимость доработки:</label>
                        <input type="text" class="form-control" name="model_cost" value="<?=$row['model_cost'];?>" />
                    </div>
                <?php endif; ?>

                <?php if ( $permittedFields['collections'] ): ?>
                <div class="col-xs-12 mt-2">
                    <div class="form-group">
                        <div class="panel panel-default" style="position: relative;">
                            <div class="panel-heading" title="Коллекции к которым будет принадлежать данное изделие">
                                <i class="fas fa-gem"></i>
                                <strong> Коллекции:</strong>
                                <button id="addCollection" class="btn btn-sm btn-default pull-right" style="top:-5px !important; position:relative;" type="button" title="Добавить коллекцию">
                                    <span class="glyphicon glyphicon-plus"></span>
                                </button>
                            </div>
                            <table class="table <?= $row['collections'] ? "" : "hidden" ?>">
                                <thead>
                                <tr class="thead11">
                                    <th>№</th><th>Название</th><th></th>
                                </tr>
                                </thead>
                                <tbody id="collections_table">
                                <!-- // автозаполнение если добавляем комплект или редакт модель -->
                                <?php $switchTableRow = "collection" ?>
                                <?php foreach ( $row['collections']??[] as $collection ) : ?>
                                    <?php require _viewsDIR_."_AddEdit/includes/protoRows.php"?>
                                <?php endforeach; ?>
                                <?php if ( isset($collection) ) unset($collection); ?>
                                <?php if ( isset($i) ) unset($i); ?>
                                </tbody>
                            </table>
                            <?php require _viewsDIR_.'_AddEdit/includes/collectionsBlock.php'?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ( $permittedFields['material'] ): ?>
                    <div class="col-xs-12" id="material">
                        <?php require _viewsDIR_."_AddEdit/includes/model_materials_full.php" ?>
                    </div>
                <?php endif; ?>

                <?php if ( $permittedFields['gems'] ): ?>
                    <div class="col-xs-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <i class="far fa-gem"></i>
                                <strong> Вставки:</strong>
                                <button id="addGem" class="btn btn-sm btn-default pull-right" style="top:-5px !important; position:relative;" type="button" title="Добавить камень">
                                    <span class="glyphicon glyphicon-plus"></span>
                                </button>
                            </div>
                            <table class="table <?= $gemsRow ? "" : "hidden"?>">
                                <thead>
                                <tr class="thead11">
                                    <th>№</th>
                                    <th>Сырьё</th>
                                    <th>Огранка</th>
                                    <th>Ø(Размер мм)</th>
                                    <th>Кол-во шт.</th>
                                    <th>Цвет</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody id="gems_table">
                                <?php $switchTableRow = "gems" ?>
                                <?php foreach ( $gemsRow??[] as $c => $gem ): ?>
                                    <?php require _viewsDIR_."_AddEdit/includes/protoRows.php"?>
                                <?php endforeach; ?>
                                <?php if( isset($gem) ) unset($gem, $gemsRow); ?>
                                </tbody>
                            </table>
                        </div><!-- end panel gems-->
                    </div>
                <?php endif; ?>

                <div class="col-xs-12">
                    <?php if ( $permittedFields['vc_links'] ): ?>
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <span class="glyphicon glyphicon-link"></span>
                                <strong> Ссылки на другие артикулы:</strong>
                                <button id="addVC" class="btn btn-sm btn-default pull-right " style="top:-5px !important; position:relative;" type="button" title="Добавить отсылку">
                                    <span class="glyphicon glyphicon-plus"></span>
                                </button>
                            </div>
                            <table class="table <?=$dopVCs ? "" : "hidden"?>">
                                <thead>
                                <tr class="thead11">
                                    <th>№</th><th>Название</th><th>Артикул / Номер 3D</th><th>Описание</th><th></th>
                                </tr>
                                </thead>
                                <tbody id="dop_vc_table">
                                <?php $switchTableRow = "dopVC"; $vcI = 0; ?>
                                <?php foreach ( $dopVCs??[] as $dopVc ): ?>
                                    <?php require _viewsDIR_."_AddEdit/includes/protoRows.php" ?>
                                <?php endforeach; ?>
                                <?php if ( isset($dopVc) ) unset($dopVc); ?>
                                <?php if ( isset($vcI) ) unset($vcI); ?>
                                </tbody>
                            </table>
                        </div><!-- end panel dopArticls-->
                    <?php endif; ?>
                </div>


                <!-- кнопка заглушка ( при нажатии Enter на любом поле пытается нажать первую свободную кнопку - типа отправляет форму ) -->
                <button class="hidden"></button>
                <!-- Примечания / Описания -->
                <?php if ( $permittedFields['description'] ): ?>
                    <div class="col-xs-12">
                        <label for="descr" class=""><span class="glyphicon glyphicon-comment"></span> Примечания:</label>
                        <textarea id="descr" class="form-control" rows="2" name="description" style="margin:0px 0 15px 0 !important;"><?=$row['description']?></textarea>
                    </div>
                    <div class="col-xs-12 modelNotes">
                        <?php if ( trueIsset($notes??[]) ): ?>
                            <?php $switchTableRow = 'notes'; ?>
                            <?php foreach ( $notes??[] as $note ): ?>
                                <?php require _viewsDIR_."_AddEdit/includes/protoRows.php" ?>
                            <?php endforeach; unset($note); ?>
                        <?php endif; ?>
                    </div>
                    <div class="col-xs-12">
                        <button class="btn btn-default addNote"><i class="far fa-comment-alt"></i> Добавить Описание</button>
                    </div>
                    <div class="col-xs-12"><hr/></div>
                <?php endif; ?>
            </div><!--row-->

            <div class="row">
                <?php if ( $permittedFields['labels'] ): ?>
                    <!-- Labels-->
                    <div class="col-xs-12 lables">
                        <p><b><span class="glyphicon glyphicon-tags"></span> &#160;Метки:</b></p>
                        <div class="row">
                            <?php foreach ( $labels??[] as $label):?>
                                <div class="col-xs-6 col-sm-4 col-md-3 col-lg-3" title="<?=$label['info']?>">
                                    <input type="checkbox" <?=$label['check'];?> name="labels[name][<?=$label['id']?>]" id="<?=$label['id']?>" aria-label="..." value="<?=$label['name'];?>">
                                    <label for="<?=$label['id'];?>">
                            <span class="label <?=$label['class'];?> lables-bottom">
                                <span class="glyphicon glyphicon-tag"></span> <?=$label['name'];?></span>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <!--end Labels-->
                <?php endif; ?>

            </div><!--end row-->
        </div>



        <!-- ******************** REPAIRS ******************** -->
        <?php if ( $permittedFields['repairs'] ): ?>
        <div role="tabpanel" class="tab-pane in fade pt-1" id="repairsData">
            <div class="row" id="repairsBlock">
                <div class="col-xs-12">
                    <ul class="nav nav-tabs text-center nav-justified">
                        <?php if ( $permittedFields['repairs3D'] ): ?>
                            <li role="presentation" class="active" title="Ремонты 3Д моделей"><a href="#repairs3d" role="tab" data-toggle="tab"><i class="fas fa-vector-square"></i> Ремонты 3Д (<?=$countRepairs['3d']?>)</a></li>
                        <?php endif; ?>
                        <?php if ( $permittedFields['repairsJew'] ): ?>
                            <li role="presentation" class="" title="Ремонты Мастер Моделей"><a href="#repairsJew" role="tab" data-toggle="tab"><i class="fas fa-screwdriver"></i> Ремонты ММ (<?=$countRepairs['jew']?>)</a></li>
                        <?php endif; ?>
                        <?php if ( $permittedFields['repairsProd'] ): ?>
                            <li role="presentation" class="" title="Ремонты моделей на производстве"><a href="#repairsProd" role="tab" data-toggle="tab"><i class="fas fa-hammer"></i> Ремонты производства (<?=$countRepairs['prod']?>)</a></li>
                        <?php endif; ?>
                    </ul>
                    <?php if ( !isset($repairs) ) $repairs = []; ?>
                    <div class="tab-content">

                    <?php if ( $permittedFields['repairs3D'] ): ?>
                        <div role="tabpanel" class="tab-pane in fade pt-1 active repairs3d" style="background-color: white;" id="repairs3d">
                            <h4 class="text-center">
                                Описание ремонтов 3д моделей.
                                <button data-repair="repairs3d" style="font-size: large" title="Добавить ремонт 3Д" class="btn btn-link addRepair"><i class="far fa-plus-square"></i></button>
                            </h4>
                            <?php foreach ( $repairs as $repair ): ?>
                                <?php if ( (int)$repair['which'] === 0 ) {
                                    $whichRepair = 0;
                                    require _viewsDIR_.'_AddEdit/includes/repairs.php';
                                } ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ( $permittedFields['repairsJew'] ): ?>
                        <div role="tabpanel" class="tab-pane in fade pt-1" id="repairsJew" style="background-color: white;">
                            <h4 class="text-center">
                                Описание ремонтов мастер моделей.
                                <button data-repair="repairsJew" style="font-size: large" title="Добавить ремонт Модельера-доработчика" class="btn btn-link addRepair"><i class="far fa-plus-square"></i></button>
                            </h4>
                            <div class="col-xs-12 repairsJew">
                            <?php foreach ( $repairs as $repair ) : ?>
                                <?php if ( $whichRepair = (int)$repair['which'] === 1 ? 1 : false )
                                    require _viewsDIR_.'_AddEdit/includes/repairs.php'; ?>
                            <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ( $permittedFields['repairsProd'] ): ?>
                        <div role="tabpanel" class="tab-pane in fade pt-1" id="repairsProd" style="background-color: white;">
                            <h4 class="text-center">
                                Описание ремонтов моделей на производстве ( монтирвка, закрепка, матрица и т.д.)
                                <button data-repair="repairsProd" style="font-size: large" title="Добавить ремонт производства" class="btn btn-link addRepair"><i class="far fa-plus-square"></i></button>
                            </h4>
                            <div class="col-xs-12 repairsProd">
                                <?php foreach ( $repairs as $repair ) : ?>
                                    <?php if ( $whichRepair = (int)$repair['which'] === 2 ? 2 : false )
                                        require _viewsDIR_.'_AddEdit/includes/repairs.php'; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php $switchTableRow = 'repair'; require _viewsDIR_.'_AddEdit/includes/protoRows.php' ?>
        </div>
        <?php endif; ?>



        <!-- ******************** FILES ******************** -->
        <?php if ( $permittedFields['files'] ): ?>
        <div role="tabpanel" class="tab-pane in fade pt-1" id="filesData">
            <div class="row">
                <div class="col-xs-12">
                    <div id="drop-area" title="Загрузить Файлы">
                        <p>Загрузить файлы можно перетащив их в эту область. Форматы: .jpg .jpeg .png .gif .stl .mgx .3dm .ai .dxf</p>
                        <button type="button" id="addImageFiles" class="button"><i class="far fa-images"></i> Выбрать файлы</button>
                    </div>
                </div>

                <!-- IMAGES Block -->
                <?php if ( $permittedFields['images'] ): ?>
                    <div class="col-xs-12">
                        <div class="row" id="picts">
                            <div class="col-xs-12">
                                <h5 class="text-bold">Файлы изображений: <span id="imgFor" class="help-block hidden err-notice"></span></h5>
                            </div>
                            <?php $switchTableRow = "dropImage"; $protoImgRow = 0; ?>
                            <?php foreach ( $images?:[] as $image ) : ?>
                                <?php require _viewsDIR_."_AddEdit/includes/protoRows.php"?>
                            <?php endforeach; ?>
                            <?php $protoImgRow = 1; $image = []; require _viewsDIR_."_AddEdit/includes/protoRows.php" // Прототип ?>
                        </div>
                        <hr class=""/>
                    </div>
                <?php endif; ?>
                <!-- IMAGES Block END -->

                <!-- STL Block -->
            <?php if ( $permittedFields['stl'] ): ?>
                <div class="col-xs-12">
                    <div style="position:relative; top: -6px; font-size:13px;">* Перед загрузкой stl файлов нужно применять к моделям метод Triangle Reduction в Magics с параметром 0,0025. Для уменьшения размера файлов.<br>
                        ** Максимальный размер всех .stl файлов не должен превышать 10 магабайт!
                    </div>
                    <?php if ( isset($stl_file['stl_name']) && !empty($stl_file['stl_name']) ): ?>
                        <?php
                            $stlFileName = _stockDIR_.$row['number_3d'].'/'.$id.'/stl/'.$stl_file['stl_name'];
                            $fileSize = 0;
                            if ( file_exists($stlFileName) ) $fileSize = filesize(_stockDIR_.$row['number_3d'].'/'.$id.'/stl/'.$stl_file['stl_name']) / 1024;
                        ?>
                        <div class="haveStl">
                            <span><b>STL/MGX файлы:</b><i><?= '('.round($fileSize / 1024,2).' Мб)'?></i> <b><?=$stl_file['stl_name']?></b></span>
                            <button type="button" id="dellStl" class="btn btn-sm btn-default" onclick="dell_fromServ( <?=$id.',\''.$stl_file['stl_name'].'\'';?>, 'stl', false )" title="Удалить">
                                <span class="glyphicon glyphicon-trash"></span>
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <div class="col-xs-12">
                                <b class="pull-left">STL/MGX файлы: <span></span></b>
                                <button type="button" id="removeStl" class="btn btn-sm btn-default hidden removeDataFiles pull-right" data-type="stl" title="Убрать Stl файлы"><span class="glyphicon glyphicon-remove"></span></button>
                            </div>
                        </div>
                        <div class="row" id="stl-files-area"></div>
                    <?php endif; ?>
                </div>
                <div class="col-xs-12">
                    <hr class=""/>
                </div>
            <?php endif; ?>
                <!-- STL Block END -->

                <!-- 3DM Block -->
            <?php if ( $permittedFields['rhino3dm'] ): ?>
                <div class="col-xs-12">
                    <div style="position:relative; top: -6px; font-size:13px;">* Максимальный размер всех .3dm файлов не должен превышать 25 магабайт!</div>
                    <?php if ( isset($rhino_file['name']) && !empty($rhino_file['name']) ): ?>
                        <div class="haveStl">
                            <span><b>3dm Rhino файл:</b><i><?= '('.round(($rhino_file['size'] / 1024)/1024,2).' Мб)'?></i><b> &nbsp;<?=$rhino_file['name'];?></b></span>
                            <button type="button" id="dell3dm" class="btn btn-sm btn-default" onclick="dell_fromServ( <?=$id.',\''.$rhino_file['name'].'\'';?>, '3dm', false )" title="Удалить">
                                <span class="glyphicon glyphicon-trash"></span>
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <div class="col-xs-12">
                                <b class="pull-left">3dm Rhino файлы: <span></span></b>
                                <button type="button" id="remove3dm" class="btn btn-sm btn-default hidden removeDataFiles pull-right" data-type="3dm" title="Убрать 3dm файлы"><span class="glyphicon glyphicon-remove"></span></button>
                            </div>
                        </div>
                        <div class="row" id="3dm-files-area"></div>
                    <?php endif; ?>
                </div>
                <div class="col-xs-12">
                    <hr>
                </div>
            <?php endif; ?>
                <!-- 3DM Block END -->

                <!-- AI Block -->
            <?php if ( $permittedFields['ai'] ): ?>
                <div class="col-xs-12 AIBlock">
                    <?php if ( isset($ai_file['name']) && !empty($ai_file['name']) ): ?>
                        <div class="haveStl">
                            <span><b>AI/DXF файлы накладки:&nbsp;&nbsp;<?=$ai_file['name'];?></b></span>
                            <button type="button" id="dellAi" class="btn btn-sm btn-default" onclick="dell_fromServ( <?=$id.',\''.$ai_file['name'].'\'';?>, 'ai', false )" title="Удалить">
                                <span class="glyphicon glyphicon-trash"></span>
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <div class="col-xs-12">
                                <b class="pull-left">AI/DXF файлы накладки: <span></span></b>
                                <button type="button" id="removeAi" class="btn btn-sm btn-default hidden removeDataFiles pull-right" data-type="ai" title="Убрать ai файлы"><span class="glyphicon glyphicon-remove"></span></button>
                            </div>
                        </div>
                        <div class="row" id="ai-files-area" style="vertical-align: top"></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
                <div class="col-xs-2 file-block-proto hidden text-center pr-0">
                    <img src="../../web/picts/icon_3dm.png" style="max-width: 64px;">
                    <div class="data-file-bg text-center mt-1"></div>
                </div>
                <!-- Ai Block END -->
            </div>



        </div>
        <?php endif; ?>


        <?php if ( $permittedFields['modelAccount'] && $changeCost ): ?>
        <!-- ******************** PRICES ******************** -->
        <div role="tabpanel" class="tab-pane in fade pt-1" id="pricesData" >
            <div class="row">
                <div class="col-xs-12">
                    <h4 class="text-center">
                        Внесите стоимость своих работ, по текущей модели.
                    </h4>
                </div>
                <?php $wholeTotal = 0; ?>
                <?php if ( $permittedFields['MA_design'] ): // Дизайнер?>
                <div class="col-xs-12">
                    <div class="form-group">
                        <div class="panel panel-default" style="position: relative;">
                            <div class="panel-heading" title="Стоимость эскиза">
                                <i class="far fa-image"></i>
                                <strong> Эскиз:</strong>
                            </div>
                            <table class="table">
                                <thead>
                                <tr class="thead11">
                                    <th>№</th>
                                    <th width="30%">Название</th>
                                    <th width="30%">Стоимость</th>
                                    <th>Статус</th>
                                </tr>
                                </thead>
                                <tbody id="">
                                <!-- // автозаполнение -->
                                <?php $pr_total = 0; $pr_paid = 0; $pr_notPaid = 0; $priceNum = 1; ?>
                                <?php foreach ( $modelPrices??[] as $modelPrice ): ?>
                                    <?php if ( (int)$modelPrice['is3d_grade'] !== 2 ) continue; ?>
                                    <?php $pr_total += $modelPrice['value']; ?>
                                    <tr>
                                        <td style="width: 30px"><?= $priceNum++ ?></td>
                                        <td><?= $modelPrice['cost_name'] ?></td>
                                        <td><?= $modelPrice['value'] ?></td>
                                        <td>
                                        <?php if ( (int)$modelPrice['status'] === 1 ): ?>
                                            <span class="label label-primary ">Зачислено!</span>
                                        <?php else: ?>
                                            <span class="label label-default ">Не зачислено!</span>
                                        <?php endif; ?>
                                        <?php if ( (int)$modelPrice['paid'] === 1 ): ?>
                                            <?php $pr_paid += $modelPrice['value']; ?>
                                            <span class="label label-success ">Оплачено!</span>
                                        <?php else: ?>
                                            <?php $pr_notPaid += $modelPrice['value']; ?>
                                            <span class="label label-default ">Не Оплачено!</span>
                                        <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr class="active text-bold">
                                    <td style="width: 30px"></td>
                                    <td>Всего: </td>
                                    <td><?= $pr_total; ?></td>
                                    <?php $wholeTotal += $pr_total; ?>
                                    <td>Оплачено: <?= $pr_paid; ?> / Не оплачено: <?= $pr_notPaid; ?></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif;?>

                <?php if ( $permittedFields['MA_modeller3D'] ): // 3Д моделлер?>
                <div class="col-xs-12">
                    <div class="form-group">
                        <div class="panel panel-default" style="position: relative;">
                            <div class="panel-heading" title="Стоимость 3D Модели">
                                <i class="fas fa-cube"></i>
                                <strong>3D Модель: Моделирование</strong>
                                <?php if ( !$this->isCredited($modelPrices, 1) ): ?>
                                    <button class="btn btn-sm btn-default pull-right" style="top:-5px !important; position:relative;" data-toggle="modal" data-target="#grade3DModal" type="button" title="Добавить оценку">
                                        <span class="glyphicon glyphicon-plus"></span>
                                    </button>
                                <?php endif; ?>
                            </div>
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
                                <tbody class="modeller3D">
                                <!-- // автозаполнение -->
                                <?php $pr_total = 0; $modelPriceStatus = 0; $modelPricePaid = 0; $priceNum = 0; ?>
                                <?php foreach ( $modelPrices??[] as $modelPrice ): ?>
                                    <?php if ( (int)$modelPrice['is3d_grade'] !== 1 ) continue; ?>
                                    <tr data-gradeID="<?= $modelPrice['gs_id'] ?>">
                                        <td style="width: 30px"><?= ++$priceNum ?></td>
                                        <td>
                                            <?php $mpTitle = $modelPrice['cost_name']; foreach ( $gradingSystem as $gsRow ) if ( $gsRow['id'] == $modelPrice['gs_id'] ) $mpTitle = $gsRow['description']; ?>
                                            <div class="cursorPointer lightUpGSRow" data-toggle="tooltip" data-placement="bottom" title="<?=$mpTitle?>" style="width: 100%">
                                                <?=$modelPrice['cost_name'] ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?= $modelPrice['value'] ?>
                                        </td>
                                        <?php $pr_total += $modelPrice['value']; ?>
                                        <td>
                                            <?php if ( !$this->isCredited($modelPrices, 1) ): ?>
                                                <input hidden class="hidden" value="<?= $modelPrice['id'] ?>"    name="ma3Dgs[mp3DIds][] ">
                                                <input hidden class="hidden" value="<?= $modelPrice['value'] ?>" name="ma3Dgs[gs3Dpoints][] ">
                                                <input hidden class="hidden" value="<?= $modelPrice['gs_id'] ?>" name="ma3Dgs[gs3Dids][] ">
                                            <?php endif; ?>
                                            <?php $modelPriceStatus = (int)$modelPrice['status'] ?>
                                            <?php $modelPricePaid = (int)$modelPrice['paid'] ?>
                                        </td>
                                        <td style="width:100px;">
                                            <?php if ( !$modelPriceStatus ): ?>
                                            <button class="btn btn-sm btn-default ma3DgsDell" type="button" onclick="deleteRow(this);" title="Удалить Оценку">
                                                <span class="glyphicon glyphicon-trash"></span>
                                            </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <tr class="active text-bold t-total">
                                        <td style="width: 30px"></td>
                                        <td>Всего: </td>
                                        <td><?= $pr_total; ?></td>
                                        <?php $wholeTotal += $pr_total; ?>
                                        <td>
                                            <?php if ( $priceNum ): ?>
                                                <?php if ( $modelPriceStatus === 1 ): ?>
                                                    <span class="label label-primary ">Зачислено!</span>
                                                <?php else: ?>
                                                    <span class="label label-default ">Не зачислено!</span>
                                                <?php endif; ?>
                                                <?php if ( $modelPricePaid === 1 ): ?>
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
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ( $permittedFields['MA_techCoord'] ): // Согласование с технологом?>
                <div class="col-xs-12">
                    <div class="form-group">
                        <div class="panel panel-default" style="position: relative;">
                            <div class="panel-heading" title="Стоимость Роста">
                                <i class="fas fa-user-check"></i>
                                <strong>3D Модель: Согласование с технологом</strong>
                            </div>
                            <table class="table">
                                <thead>
                                <tr class="thead11">
                                    <th>№</th>
                                    <th width="30%">Название</th>
                                    <th width="30%">Стоимость</th>
                                    <th>Статус</th>
                                </tr>
                                </thead>
                                <tbody id="">
                                <!-- // автозаполнение -->
                                <?php $pr_total = 0; $modelPriceStatus = 0; $modelPricePaid = 0; $priceNum = 0; ?>
                                    <?php foreach ( $modelPrices??[] as $modelPrice ): ?>
                                        <?php if ( (int)$modelPrice['is3d_grade'] !== 4 ) continue; ?>
                                        <?php $pr_total += $modelPrice['value']; ?>
                                        <tr>
                                            <td style="width: 30px"><?= ++$priceNum ?></td>
                                            <td><?= $modelPrice['cost_name'] ?></td>
                                            <td><?= $modelPrice['value'] ?></td>
                                            <td>
                                            <?php $modelPriceStatus = (int)$modelPrice['status'] ?>
                                            <?php $modelPricePaid = (int)$modelPrice['paid'] ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr class="active text-bold">
                                        <td style="width: 30px"></td>
                                        <td>Всего: </td>
                                        <td><?= $pr_total; ?></td>
                                        <?php $wholeTotal += $pr_total; ?>
                                        <td>
                                            <?php if ( $priceNum ): ?>
                                                <?php if ( $modelPriceStatus === 1 ): ?>
                                                    <span class="label label-primary ">Зачислено!</span>
                                                <?php else: ?>
                                                    <span class="label label-default ">Не зачислено!</span>
                                                <?php endif; ?>
                                                <?php if ( $modelPricePaid === 1 ): ?>
                                                    <span class="label label-success ">Оплачено!</span>
                                                <?php else: ?>
                                                    <span class="label label-default ">Не Оплачено!</span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ( $permittedFields['MA_techJew'] ): // Подпись технолога?>
                    <div class="col-xs-12">
                        <div class="form-group">
                            <div class="panel panel-default" style="position: relative;">
                                <div class="panel-heading" title="Стоимость Роста">
                                    <i class="fas fa-user-edit"></i>
                                    <strong>Подпись технолога</strong>
                                </div>
                                <table class="table">
                                    <thead>
                                    <tr class="thead11">
                                        <th>№</th>
                                        <th width="30%">Название</th>
                                        <th width="30%">Стоимость</th>
                                        <th>Статус</th>
                                    </tr>
                                    </thead>
                                    <tbody id="">
                                    <!-- // автозаполнение -->
                                    <?php $pr_total = 0; $modelPriceStatus = 0; $modelPricePaid = 0; $priceNum = 0; ?>
                                    <?php foreach ( $modelPrices??[] as $modelPrice ): ?>
                                        <?php if ( (int)$modelPrice['is3d_grade'] !== 7 ) continue; ?>
                                        <?php $pr_total += $modelPrice['value']; ?>
                                        <tr>
                                            <td style="width: 30px"><?= ++$priceNum ?></td>
                                            <td><?= $modelPrice['cost_name'] ?></td>
                                            <td><?= $modelPrice['value'] ?></td>
                                            <td>
                                            <?php $modelPriceStatus = (int)$modelPrice['status'] ?>
                                            <?php $modelPricePaid = (int)$modelPrice['paid'] ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr class="active text-bold">
                                        <td style="width: 30px"></td>
                                        <td>Всего: </td>
                                        <td><?= $pr_total; ?></td>
                                        <?php $wholeTotal += $pr_total; ?>
                                        <td>
                                            <?php if ( $priceNum ): ?>
                                                <?php if ( $modelPriceStatus === 1 ): ?>
                                                    <span class="label label-primary ">Зачислено!</span>
                                                <?php else: ?>
                                                    <span class="label label-default ">Не зачислено!</span>
                                                <?php endif; ?>
                                                <?php if ( $modelPricePaid === 1 ): ?>
                                                    <span class="label label-success ">Оплачено!</span>
                                                <?php else: ?>
                                                    <span class="label label-default ">Не Оплачено!</span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ( $permittedFields['MA_3dSupport'] ): // Поддержки?>
                <div class="col-xs-12">
                    <div class="form-group">
                        <div class="panel panel-default" style="position: relative;">
                            <div class="panel-heading" title="Стоимость поддержек для 3D Модели">
                                <span class="glyphicon glyphicon-compressed" aria-hidden="true"></span>
                                <strong>3D Модель: Поддержки</strong>
                            </div>
                            <table class="table">
                                <thead>
                                <tr class="thead11">
                                    <th>№</th>
                                    <th width="30%">Название</th>
                                    <th width="30%">Стоимость</th>
                                    <th>Статус</th>
                                </tr>
                                </thead>
                                <tbody id="">
                                <!-- // автозаполнение -->
                                <?php $pr_total = 0; $modelPriceStatus = 0; $modelPricePaid = 0; $priceNum = 0; ?>
                                    <?php foreach ( $modelPrices??[] as $modelPrice ): ?>
                                        <?php if ( (int)$modelPrice['is3d_grade'] !== 3 ) continue; ?>
                                        <?php $pr_total += $modelPrice['value']; ?>
                                        <tr>
                                            <td style="width: 30px"><?= ++$priceNum ?></td>
                                            <td><?= $modelPrice['cost_name'] ?></td>
                                            <td><?= $modelPrice['value'] ?></td>
                                            <td>
                                                <?php $modelPriceStatus = (int)$modelPrice['status'] ?>
                                                <?php $modelPricePaid = (int)$modelPrice['paid'] ?>
                                                <?php if ( $modelPriceStatus === 1 ): ?>
                                                    <span class="label label-primary ">Зачислено!</span>
                                                <?php else: ?>
                                                    <span class="label label-default ">Не зачислено!</span>
                                                <?php endif; ?>
                                                <?php if ( $modelPricePaid === 1 ): ?>
                                                    <span class="label label-success ">Оплачено!</span>
                                                <?php else: ?>
                                                    <span class="label label-default ">Не Оплачено!</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr class="active text-bold">
                                        <td style="width: 30px"></td>
                                        <td>Всего: </td>
                                        <td><?= $pr_total; ?></td>
                                        <?php $wholeTotal += $pr_total; ?>
                                        <td>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ( false ): // Рост $permittedFields['MA_3dPrinting'] ?>
                <div class="col-xs-12">
                    <div class="form-group">
                        <div class="panel panel-default" style="position: relative;">
                            <div class="panel-heading" title="Стоимость Роста">
                                <span class="glyphicon glyphicon-print" aria-hidden="true"></span>
                                <strong>3D Модель: Рост</strong>
                            </div>
                            <table class="table">
                                <thead>
                                    <tr class="thead11">
                                        <th>№</th>
                                        <th width="30%">Название</th>
                                        <th width="30%">Стоимость</th>
                                        <th>Статус</th>
                                    </tr>
                                </thead>
                                <tbody id="">
                                <!-- // автозаполнение -->
                                <?php $pr_total = 0; $modelPriceStatus = 0; $modelPricePaid = 0; $priceNum = 0; ?>
                                    <?php foreach ( $modelPrices??[] as $modelPrice ): ?>
                                        <?php if ( (int)$modelPrice['is3d_grade'] !== 5 ) continue; ?>
                                        <?php $pr_total += $modelPrice['value']; ?>
                                        <tr>
                                            <td style="width: 30px"><?= ++$priceNum ?></td>
                                            <td><?= $modelPrice['cost_name'] ?></td>
                                            <td><?= $modelPrice['value'] ?></td>
                                            <td>
                                            <?php $modelPriceStatus = (int)$modelPrice['status'] ?>
                                            <?php $modelPricePaid = (int)$modelPrice['paid'] ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr class="active text-bold">
                                        <td style="width: 30px"></td>
                                        <td>Всего: </td>
                                        <td><?= $pr_total; ?></td>
                                        <?php $wholeTotal += $pr_total; ?>
                                        <td>
                                            <?php if ( $priceNum ): ?>
                                                <?php if ( $modelPriceStatus === 1 ): ?>
                                                    <span class="label label-primary ">Зачислено!</span>
                                                <?php else: ?>
                                                    <span class="label label-default ">Не зачислено!</span>
                                                <?php endif; ?>
                                                <?php if ( $modelPricePaid === 1 ): ?>
                                                    <span class="label label-success ">Оплачено!</span>
                                                <?php else: ?>
                                                    <span class="label label-default ">Не Оплачено!</span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ( $permittedFields['MA_modellerJew'] ): // Доработка модели?>
                <div class="col-xs-12">
                    <div class="form-group">
                        <div class="panel panel-default" style="position: relative;">
                            <div class="panel-heading" title="Стоимость Доработки">
                                <span class="glyphicon glyphicon-wrench" aria-hidden="true"></span>
                                <strong>Доработка модели</strong>
                                <?php ;/*if ( !$this->isPayed($modelPrices??[], 6) ):*/ ?>
                                    <button class="btn btn-sm btn-default pull-right addModellerJewPrice" style="top:-5px !important; position:relative;" type="button" title="Добавить стоимость">
                                        <span class="glyphicon glyphicon-plus"></span>
                                    </button>
                                <?php ;/*endif;*/ ?>
                            </div>
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
                                <tbody class="modellerJewPrices">
                                <!-- // автозаполнение -->
                                <?php $pr_total = 0; $modelPriceStatus = 0; $modelPricePaid = 0; $priceNum = 0; $trustedPrices=[6,9,10]; ?>
                                    <?php foreach ( $modelPrices??[] as $modelPrice ): ?>
                                        <?php if ( !in_array( (int)$modelPrice['is3d_grade'], $trustedPrices) ) continue; ?>
                                        <?php $pr_total += $modelPrice['value']; ?>
                                        <?php $modelPriceStatus = (int)$modelPrice['status'] ?>
                                        <?php $modelPricePaid = (int)$modelPrice['paid'] ?>
                                        <tr>
                                            <td style="width: 30px"><?= ++$priceNum ?></td>
                                            <td>
                                                <?php if ( $modelPriceStatus === 1 ): // Зачислено (инпут не выводим)?>
                                                    <?= $modelPrice['cost_name'] ?>
                                                <?php else: ?>
                                                    <input class="form-control"  name="modellerJewPrice[cost_name][]" value="<?= $modelPrice['cost_name'] ?>" />
                                                <?php endif; ?>
                                                <input hidden class="hidden jewPriceName" value="<?= $modelPrice['cost_name'] ?>" />
                                            </td>
                                            <td>
                                                <?php if ( $modelPriceStatus === 1 ): // Зачислено (инпут не выводим)?>
                                                    <?= $modelPrice['value'] ?>
                                                <?php else: ?>
                                                    <input class="form-control"  name="modellerJewPrice[value][]" value="<?= $modelPrice['value'] ?>" />
                                                    <input hidden class="hidden" name="modellerJewPrice[id][]" value="<?= $modelPrice['id'] ?>" />
                                                <?php endif; ?>
                                                <input hidden class="hidden jewPriceID" value="<?= $modelPrice['id'] ?>" />
                                                <input hidden class="hidden jewPriceValue" value="<?= $modelPrice['value'] ?>" />
                                            </td>
                                            <td>
                                                <!-- Статус  -->
                                                <?php if ( $modelPriceStatus === 1 ): ?>
                                                    <span class="label label-primary ">Зачислено!</span>
                                                <?php else: ?>
                                                    <span class="label label-default ">Не зачислено!</span>
                                                <?php endif; ?>
                                                <?php if ( $modelPricePaid === 1 ): ?>
                                                    <span class="label label-success ">Оплачено!</span>
                                                <?php else: ?>
                                                    <span class="label label-default ">Не Оплачено!</span>

                                                <?php endif; ?>

                                            </td>
                                            <td style="width:100px;">
                                                <?php if ( $modelPricePaid !== 1 ): ?>
                                                    <?php if ( User::getID() === 53 ): // Жуковская ?>
                                                        <button type="button" data-toggle="" data-prices="single" data-priceID="<?=$modelPrice['id']?>" data-target="" class="btn btn-sm btn-success payJewPriceBtn"  title="Оплатить">
                                                            <span class="glyphicon glyphicon-usd"></span>
                                                        </button>
                                                    <?php endif; ?>
                                                    <button class="btn btn-sm btn-default maJewPriceDell" type="button" onclick="deleteRow(this);" title="Удалить Стоимость">
                                                        <span class="glyphicon glyphicon-trash"></span>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr class="active text-bold t-total">
                                        <td style="width: 30px"></td>
                                        <td>Всего: </td>
                                        <td><?= $pr_total; ?></td>
                                        <?php $wholeTotal += $pr_total; ?>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                            <table class="hidden">
                                <tr class="gs_protoModJewRow">
                                    <td style="width: 30px"></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td style="width:100px;">
                                        <button class="btn btn-sm btn-default" type="button" onclick="deleteRow(this);" title="Удалить Оценку">
                                            <span class="glyphicon glyphicon-trash"></span>
                                        </button>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ( User::getAccess() === 1 ): // Общая стоимость ?>
                <div class="col-xs-12">
                    <div class="form-group">
                        <div class="panel panel-default" style="position: relative;">
                            <div class="panel-heading" title="">
                                <i class="fas fa-hryvnia"></i>
                                <strong>Общая стоимость: <?= $wholeTotal; ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>
        <?php endif; ?>
    </div>
    <hr />
    <div class="row">
        <?php if ( User::permission('statuses') ): ?>
            <!-- Statuses -->
            <div class="col-xs-12 status" id="workingCenters">
                <p title="Текущий статус изделия" style="cursor: default;">
                    <span>
                        <span class="glyphicon glyphicon-ok"></span>
                        &#160;Текущий статус изделия:
                    </span>
                    <span class="label label-warning" style="font-weight: bold;font-size: medium;" title="<?= $row['status']['title']??'' ?>" >
                        <span style="color: #1C1C1C" class="glyphicon glyphicon-<?= $row['status']['glyphi']??''?>"></span>
                        <span id="currentStatus" ><?=$row['status']['name_ru']??''?></span>
                    </span>
                    <?php if ( $toDellLastStatus ): ?>
                        <button id="dellCurrentStatus" title="Удалить текущий статус" onclick="" class="btn btn-sm btn-default ml-2 mb-1">
                            <span class="glyphicon glyphicon-remove"></span>
                        </button>
                    <?php endif; ?>

                    <button id="openAll" title="Раскрыть Все" onclick="event.preventDefault()" style="margin-bottom: 10px" class="pull-right btn btn-sm btn-info"><span class="glyphicon glyphicon-menu-left"></span> Раскрыть Все</button>
                    <button id="closeAll" title="Закрыть Все" onclick="event.preventDefault()" style="margin-bottom: 10px" class="pull-right hidden btn btn-sm btn-primary"><span class="glyphicon glyphicon-menu-down"></span> Закрыть Все</button>
                <div class="clearfix"></div>
                </p>
                <?php if ( $toShowStatuses ): ?>
                    <div class="row">
                        <?php
                        $countWC = count($statusesWorkingCenters?:[]);
                        $columns = [0=>'', 1=>'', 2=>'', 3=>''];
                        $c = 0;
                        ob_start();
                        ?>
                        <?php foreach ( $statusesWorkingCenters??[] as $wcName => $workingCenter ) :?>
                            <div class="panel panel-info" style="position:relative;">
                                <div class="panel-heading">
                                    <?=$wcName?>
                                    <button title="Раскрыть" onclick="event.preventDefault()" data-status="0" class="btn btn-sm btn-info <?= User::getAccess() == 4 ? "" : "statusesChevron";?> "><span class="glyphicon glyphicon-menu-left"></span></button>
                                </div>
                                <div class="panel-body pb-0 statusesPanelBody <?= User::getAccess() == 4 ? "" : "statusesPanelBodyHidden";?> "> <?php // Раскрываем все свернутые статусы для производства. На сих старых компах глючит! ?>
                                    <?php foreach ( $workingCenter as $subUnit ): ?>
                                        <div class="list-group">
                                            <a class="list-group-item active"><?=$subUnit['descr']?></a>
                                            <?php foreach ( $subUnit['statuses'] as $status ) :?>
                                                <a class="list-group-item">
                                                    <input type="radio" <?=$status['check'];?> name="status" id="<?=$status['name_en'];?>" aria-label="..." value="<?=$status['id'];?>">
                                                    <label for="<?=$status['name_en'];?>" title="<?=$status['title'];?>">
                                                        <span class="glyphicon glyphicon-<?=$status['glyphi'];?>"></span>
                                                        <?=$status['name_ru'];?>
                                                    </label>
                                                </a>
                                            <?php endforeach; ?>
                                            <a title="Ответственный" class="list-group-item list-group-item-success"><?=$subUnit['user']?></a>
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
                <?php else:?>
                    <p class="bg-danger p1 text-bold text-justify">Изменение статусов недоступно! Предыдущий участок не выпустил модель, или модель не утверждена.</p>
                <?php endif; //$toShowStatuses?>
            </div>
            <!-- END Statuses -->
        <?php endif; //statuses?>
    </div>
    <hr />
    <input type="hidden" name="save" value="<?=$save?>"/>
    <?php if ( !$permittedFields['number_3d'] ): ?>
        <input type="hidden" id="num3d" name="number_3d" value="<?=$row['number_3d'];?>"/>
    <?php endif;?>
    <?php if ( !$permittedFields['vendor_code'] ): ?>
        <input type="hidden" id="vendor_code" name="vendor_code" value="<?=$row['vendor_code'];?>"/>
    <?php endif;?>
    <?php if ( !$permittedFields['model_type'] ): ?>
        <input type="hidden" id="modelType" name="model_type" value="<?=$row['model_type'];?>"/>
    <?php endif;?>
    <?php $encID = ($component===2) ? $id : 0; ?>
    <input type="hidden" name="id" value="<?= Crypt::strEncode($encID) ?>"/>
    <input type="hidden" name="edit" id="edit" value="<?=$component;?>"/>

    <div class="row">
        <div class="col-xs-4">
            <a class="btn btn-default pull-left" role="button" href="<?=$prevPage;?>">
                <span class="glyphicon glyphicon-triangle-left"></span>
                Назад
            </a>
        </div><!--end col-xs-6-->
        <div class="col-xs-4 text-center">
                <button class="btn btn-default submitButton">
                    <span class="glyphicon glyphicon-floppy-disk"></span>
                    Сохранить
                </button>
        </div><!--end col-xs-6-->
        <div class="col-xs-4">
            <?php if ( $component === 2 && User::permission('dellModel') ): //$_SESSION['user']['access'] < 3 ?>
            <a type="button" class="btn btn-danger pull-right" onclick="dell_fromServ(<?=$id;?>, false, false, 1);">
                <span class="glyphicon glyphicon-remove"></span>
                Удалить
            </a>
            <?php endif; ?>
        </div><!--end col-xs-6-->
    </div><!--end row-->
</form>

<img src="" id="imageBoxPrev" width="200px" class="img-thumbnail hidden"/>
<?php $switchTableRow = 'notes'; require _viewsDIR_."_AddEdit/includes/protoRows.php";?>



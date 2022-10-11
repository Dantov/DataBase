<?php
$navBar = $this->navBar;
$coll_silver = $navBar['collectionList']['silver'];
$coll_gold = $navBar['collectionList']['gold'];
$coll_diamond = $navBar['collectionList']['diamond'];
$coll_other = $navBar['collectionList']['other'];
?>
<div id="collectionsModal" data-iziModal-title="Выбрать Коллекцию">
    <div id="modalCollectionsContent" style="padding: 10px" class="hidden">
        <div class="row">

            <div class="col-xs-6 col-md-3" style="padding-right: 2px;">
                <ul class="list-group text-bold">
                    <a class="list-group-item list-group-item-danger">Серебро (<?= count($coll_silver)?>)</a>
                    <?php foreach ( $coll_silver as $id => $name ) :?>
                        <a class="list-group-item cursorPointer" href="/main/?coll_show=<?=$id?>"><?=$name?></a>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="col-xs-6 col-md-3" style="padding: 0 2px 0 2px; ">
                <ul class="list-group text-bold">
                    <a class="list-group-item list-group-item-success">Золото (<?= count($coll_gold)?>)</a>
                    <?php foreach ( $coll_gold as $id => $name ) :?>
                        <a class="list-group-item cursorPointer" href="/main/?coll_show=<?=$id?>"><?=$name?></a>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="col-xs-6 col-md-3" style="padding: 0 2px 0 2px; ">
                <ul class="list-group text-bold">
                    <a class="list-group-item list-group-item-info">Бриллианты (<?= count($coll_diamond)?>)</a>
                    <?php foreach ( $coll_diamond as $id => $name ) :?>
                        <a class="list-group-item cursorPointer" href="/main/?coll_show=<?=$id?>"><?=$name?></a>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="col-xs-6 col-md-3" style="padding-left: 2px;">
                <ul class="list-group pb-1 mb-1 text-bold">
                    <a class="list-group-item list-group-item-warning">Разные (<?= count($coll_other)?>)</a>
                    <a class="list-group-item cursorPointer" href="/main/?coll_show=-1">Все</a>
                    <?php foreach ( $coll_other as $id => $name ) :?>
                        <a class="list-group-item cursorPointer" href="/main/?coll_show=<?=$id?>"><?=$name?></a>
                    <?php endforeach; ?>
                </ul>
            </div>

        </div>
    </div>
</div>
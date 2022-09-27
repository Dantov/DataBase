<?php
if ( empty($this->navBar['collectionList']) )
    throw new \Exception('Collections list is empty!', 801);

$navBar = $this->navBar['collectionList'];
$coll_silver = $navBar['silver'];
$coll_gold = $navBar['gold'];
$coll_diamond = $navBar['diamond'];
$coll_other = $navBar['other'];
?>
<div id="collectionBlockForm" class="hidden">
    <div class="row">
        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3" style="padding-right: 7px;">
            <div class="list-group mb-0">
                <a class="list-group-item active text-bold">
                    Серебро <span class="badge"><?= count($coll_silver??[]) ?></span>
                </a>
                <?php foreach ( $coll_silver??[] as $collID => $name ) :?>
                    <?php $goldAI = ''; if ( (int)$collID === 22 || (int)$collID === 53 )  $goldAI = 'aiblock'; ?>
                    <a class="list-group-item cursorPointer" data-izimodal-close="#collectionsModal" elemToAdd="" coll="" <?=$goldAI?> collid="<?=$collID?>"><?=$name?></a>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3" style="padding-left: 7px; padding-right: 7px;">
            <div class="list-group mb-0">
                <a class="list-group-item list-group-item-warning text-bold">
                    Золото <span class="badge"><?= count($coll_gold??[]) ?></span>
                </a>
                <?php foreach ( $coll_gold??[] as $collID => $name ) :?>
                    <?php $goldAI = ''; if ( (int)$collID === 22 || (int)$collID === 53 )  $goldAI = 'aiblock'; ?>
                    <a class="list-group-item cursorPointer" data-izimodal-close="#collectionsModal" elemToAdd="" coll="" <?=$goldAI?> collid="<?=$collID?>"><?=$name?></a>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3" style="padding-left: 7px; padding-right: 7px;">
            <div class="list-group mb-0">
                <a class="list-group-item list-group-item-info text-bold">
                    Бриллианты <span class="badge"><?= count($coll_diamond??[]) ?></span>
                </a>
                <?php foreach ( $coll_diamond??[] as $collID => $name ) :?>
                    <?php $goldAI = ''; if ( (int)$collID === 22 || (int)$collID === 53 )  $goldAI = 'aiblock'; ?>
                    <a class="list-group-item cursorPointer" data-izimodal-close="#collectionsModal" elemToAdd="" coll="" <?=$goldAI?> collid="<?=$collID?>"><?=$name?></a>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3" style="padding-left: 7px;">
            <div class="list-group mb-0">
                <a class="list-group-item list-group-item-danger text-bold">
                    Разное <span class="badge"><?= count($coll_other??[]) ?></span>
                </a>
                <?php foreach ( $coll_other??[] as $collID => $name ) :?>
                    <?php $goldAI = ''; if ( (int)$collID === 22 || (int)$collID === 53 )  $goldAI = 'aiblock'; ?>
                    <a class="list-group-item cursorPointer" data-izimodal-close="#collectionsModal" elemToAdd="" coll="" <?=$goldAI?> collid="<?=$collID?>"><?=$name?></a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
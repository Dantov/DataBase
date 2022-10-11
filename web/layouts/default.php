<?php
use models\User;
use soffit\{HtmlHelper,Router};

$navBar = $this->navBar;
$coll_silver = $navBar['collectionList']['silver'];
$coll_gold = $navBar['collectionList']['gold'];
$coll_diamond = $navBar['collectionList']['diamond'];
$coll_other = $navBar['collectionList']['other'];
$session = $this->session;

// Перекинем массив Юзера в JS
$wsUserData['id'] = $session->user['id'];
$wsUserData['fio'] = $session->user['fio'];

$wsUserData = json_encode($wsUserData,JSON_UNESCAPED_UNICODE);
$wsUserDataJS = <<<JS
    let wsUserData = $wsUserData;
JS;
?>
<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->title ?></title>
    <?php if ( _DEV_MODE_ ) : ?>
        <link rel="icon" href="/web/picts/favicon_dev.ico?ver=<?=time()?>">
    <?php else: ?>
        <link rel="icon" href="/web/picts/favicon.ico?ver=<?=time()?>">
    <?php endif; ?>
    <link rel="stylesheet" href="/web/css/cssFW.css?ver=<?=time()?>">
    <link rel="stylesheet" href="/web/css/style.css?ver=<?=time()?>">
    <link rel="stylesheet" href="/web/css/style_adm.css?ver=<?=time()?>">
    <link rel="stylesheet" href="/web/css/bodyImg.css?ver=<?=time()?>">
    <link rel="stylesheet" href="/web/assets/css/bootstrap.min.css">
    <!-- <link rel="stylesheet" href="/web/css/bootstrap-theme.min.css"> -->
    <link rel="stylesheet" href="/web/assets/css/iziModal.min.css">
    <link rel="stylesheet" href="/web/assets/css/iziToast.min.css">
    <link rel="stylesheet" href="/web/assets/fontawesome-5.15.4/css/all.min.css">
    <?php $this->head() ?>
    <script src="/web/views/globals/js/const.js?ver=<?=time()?>"></script>
    <script><?=$wsUserDataJS?></script>
    <script src="/web/views/globals/js/webSocketConnect.js?ver=<?=time()?>"></script>
</head>

<body id="body" class="<?=$session->assist['bodyImg']?>">
<?php if(_DEV_MODE_) require "debug.php"; ?>
<?php $this->beginBody() ?>
    
    <div class="wrapper" id="content"> <!-- нужен что бы скрывать все для показа 3Д -->
        <nav class="navbar navbar-default br-0 border-radius-0" style="box-shadow: 0 0 5px #c2c2c2">
            <div class="container-fluid">
                <!-- Brand and toggle get grouped for better mobile display -->
                <div class="navbar-header">
                    <a class="navbar-brand p1" href="<?=HtmlHelper::URL('/main/')?>">
                        <img alt="Brand" width="40" height="40" src="<?= _webDIR_HTTP_ . "picts/huflogo.png" ?>">
                    </a>
                    <a class="navbar-brand"><?=_brandName_?></a>
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                </div>

                <!-- Collect the nav links, forms, and other content for toggling -->
                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                    <ul class="nav navbar-nav">
                        <li class="<?= $this->varBlock['activeMenu']??'' ?>">
                            <a href="<?=HtmlHelper::URL('/main/')?>">Main <span class="sr-only">(current)</span></a>
                        </li>
                        <?php if ( User::permission('addModel') || User::permission('nomtnclature') ): ?>
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                    <span class="glyphicon glyphicon-menu-hamburger"></span>
                                </a>
                                <ul class="dropdown-menu">
                                    <?php if ( User::permission('addModel') ): ?>
                                        <li>
                                            <a href="/model-new/"><span class="glyphicon glyphicon-file"></span>&#160; Добавить модель</a>
                                        </li>
                                    <?php endif;?>
                                    <?php if ( User::permission('nomtnclature') ): ?>
                                        <li><a href="/nomenclature/"><span class="glyphicon glyphicon-list-alt"></span>&#160; Номенклатура</a></li>
                                    <?php endif;?>
                                </ul>
                            </li>
                        <?php endif;?>
                        <li>
                            <a id="collSelect" data-izimodal-open="#collectionsModal" type="button" title="Выбрать Коллекцию" class="cursorPointer">
                                <i class="fas fa-gem"></i>
                                <?php if ( $searchFor=$session->getKey('searchFor') ): ?>
                                        <spans>Поиск по:<?=$searchFor?></span>
                                <?php else: ?>
                                        <span id="collectionName"><?=$session->assist['collectionName']?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <?php if ( ($session->assist['collection_id'] !== -1) && !($session->getKey('searchFor')) ): ?>
                            <li>
                                <a type="button" href="<?=HtmlHelper::URL('/main/',['coll_show'=>-1])?>" title="убрать коллекцию" ><span class="glyphicon glyphicon-remove"></span></a>
                            </li>
                        <?php endif; ?>
                    </ul>

                    <form class="navbar-form navbar-left" action="/globals/search=<?=$session->getKey('searchFor')?>" method="post" <?= $searchStyle??'' ?> >
                        <?php 
                            $countAmount = $session->getKey('countAmount'); 
                            $modelsCount = count($session->getKey('selectionMode')['models']??[]); 
                        ?>
                        <?php if ( $session->getKey('countAmount') || $modelsCount ): ?>
                            <span class="cursorArrow" title="Найдено позиций"><?= $countAmount??$modelsCount; ?></span>
                        <?php endif; ?>
                        <?php if ( $session->getKey('searchFor') || $modelsCount ): ?>
                            <a href="/globals/?search=resetSearch" class="btn btn-link" type="button" name="resetSearch" title="Сбросить поиск"><i class="fas fa-broom"></i></a>
                        <?php endif; ?>
                        <?php if ( !$this->isMobile ): ?>
                            <button class="btn btn-link" type="submit" name="search" title="Нажать для поиска">
                                <span class="glyphicon glyphicon-search"></span>
                            </button>
                        <?php endif; ?>
                        <div class="form-group">
                            <input type="text" class="form-control border-radius-0 topSearchInpt" title="Что искать" placeholder="Search..." name="searchFor" value="<?=$session->getKey('searchFor')?>" >
                            <div class="btn-group">
                                <button type="button" id="searchInBtn" class="btn btn-link dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Где искать">
                                    <span><?=$navBar['searchInStr'];?> </span><span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a type="button" onclick="main.searchIn(1);" title="Поиск во всей Базе">В Базе</a></li>
                                    <li><a type="button" onclick="main.searchIn(2);" title="Поиск в выбраной коллекции">В Коллекции</a></li>
                                </ul>
                            </div>
                        </div>
                    </form>

                    <ul class="nav navbar-nav navbar-right">
                        <!-- Notices -->

                        <?php /** Уведомления о новых моделях для 3Д */ ?>
                        <?php if ( User::permission('MA_modeller3D') ): ?>
                            <li class="dropdown" id="new3DPNBadge">
                                <a class="dropdown-toggle" title="Кол-во 3Д моделей в работу / в работе" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                            <span class="badge" style="background-color: #ffb317;!important;">
                                <i class="fab fa-modx"></i>&#160;
                                <span class="da_Badge"><?= $this->varBlock['models3DToWork'] ?></span>
                                <span class="">/ </span>
                                <span class="da_BadgeInWork"><?= $this->varBlock['models3DInWork'] ?></span>
                            </span>
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="pn_show"><span class="glyphicon glyphicon-eye-open"></span>&#160; Показать</a>
                                    </li>
                                    <li>
                                        <a class="pn_hide"><span class="glyphicon glyphicon-eye-close"></span>&#160; Спрятать</a>
                                    </li>
                                </ul>
                            </li>
                        <?php endif; ?>

                        <?php /** Уведомления о ремонтах */ ?>
                        <?php if ( User::permission('repairs') ): ?>
                            <li class="dropdown" id="repPNBadge">
                                <a class="dropdown-toggle" title="Кол-во ремонтов в работу" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                            <span class="badge" style="background-color: #565c70;!important;">
                                <i class="fas fa-tools"></i>&#160;
                                <span class="da_Badge"><?= $this->varBlock['repairsToWork'] ?></span>
                            </span>
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="pn_rep_show"><span class="glyphicon glyphicon-eye-open"></span>&#160; Показать</a>
                                    </li>
                                    <li>
                                        <a class="pn_rep_hide"><span class="glyphicon glyphicon-eye-close"></span>&#160; Спрятать</a>
                                    </li>
                                </ul>
                            </li>
                        <?php endif; ?>




                        <?php /** Push Notice origin */ ?>
                        <?php if ( User::getAccess() > 0 ): ?>
                            <li class="dropdown" id="noticesBadge">
                                <a class="dropdown-toggle" title="Текущие Уведомления" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                        <span class="badge" style="background-color: #4cc1be;!important;">
                            <i class="far fa-flag"></i>&#160;
                            <span class="pushNoticeBadge"></span>
                        </span>
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a title="Показать все уведомления" class="noticeShow"><span class="glyphicon glyphicon-eye-open"></span>&#160; Показать</a>
                                    </li>
                                    <li>
                                        <a title="Спрятать все уведомления" class="noticeHide"><span class="glyphicon glyphicon-eye-close"></span>&#160; Спрятать</a>
                                    </li>
                                    <li>
                                        <a title="" class="noticeCloseAll"><span class="glyphicon glyphicon-remove"></span>&#160; Убрать все</a>
                                    </li>
                                </ul>
                            </li>
                        <?php endif; ?>



                        <!-- User menu -->
                        <li class="dropdown">
                            <a class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                <span class="glyphicon glyphicon-<?=$navBar['glphsd']?>"></span>&#160;<?= User::getFIO() ?>&#160;
                                <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu">
                                <?php if ( User::permission('paymentManager') ): ?>
                                    <li>
                                        <a href="/payment-manager/"></span><i class="fas fa-hryvnia"></i>&#160; Менеджер Оплат</a>
                                    </li>
                                <?php endif; ?>
                                <?php if ( User::permission('userPouch') ): ?>
                                    <li>
                                        <a href="/user-pouch/"><span class="glyphicon glyphicon-piggy-bank" aria-hidden="true"></span>&#160; Кошелек Работника</a>
                                    </li>
                                <?php endif; ?>
                                <?php if ( User::permission('statistic') ): ?>
                                    <li>
                                        <a href="<?=HtmlHelper::URL('statistic');?>"><span class="glyphicon glyphicon-stats"></span>&#160; Статистика</a>
                                    </li>
                                <?php endif; ?>
                                <li>
                                    <a href="/options/"><span class="glyphicon glyphicon-cog"></span>&#160; Опции</a>
                                </li>
                                <li class="">
                                    <a href="/help/"><i class="far fa-question-circle"></i>&#160; Помощь</a>
                                </li>
                                <li role="separator" class="divider"></li>
                                <li><a href="/auth/?a=exit"><span class="glyphicon glyphicon-log-out"></span>&#160; Выход</a></li>
                            </ul>
                        </li>
                    </ul>
                </div><!-- /.navbar-collapse -->

            </div><!-- /.container-fluid -->
        </nav>

        <?php
            $container = 'container';
            if ( $this->varBlock['container']??'' === 2 )
            {
                $container = 'containerFullWidth';
            } elseif ( $session->assist['containerFullWidth'] == 1 )
            {
                $container = 'containerFullWidth';
            }
        ?>
        <div class="<?=$container?> content">
            <?=$content;?>
        </div><!--container-->
        
        <footer class="footer">
            <div class="container">
                <?php if ( User::permission('addModel') ): ?>
                    <a href="/model-new/" title="Добавить модель" class="btn btn-primary">
                        <span class="glyphicon glyphicon-file"></span>
                        <?php if ( !$this->isMobile ): ?>
                            <strong> Добавить модель</strong>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>
                <i class="" style="position: absolute; right: 0; margin-right: 15px; margin-top: 10px"><a href="/versions/" title="Список изменений">Powered by Soffit FW ver. <?=$this->currentVersion?></a> &#160; <sapn title="dantrobin@gmail.com">developed by Vadym Bykov</sapn></i>
            </div>
            <script src="/web/assets/js_lib/jquery-3.2.1.min.js"></script>
            <script src="/web/assets/js_lib/bootstrap.min.js"></script>
            <script src="/web/assets/js_lib/iziModal.min.js"></script>
            <script src="/web/assets/js_lib/iziToast.min.js"></script>

            <?php if ( _DEV_MODE_ ): // Зависимость от jquery и iziModal?>
                <div id="alertDebug" aria-hidden="true" aria-labelledby="alertDebug" role="dialog" class="iziModal">
                    <div id="alertDebugContent" class="hidden p2"></div>
                </div>
            <script defer src="/web/views/globals/js/debug.js?ver=<?=time()?>"></script>
            <?php endif; ?>
            <script defer src="/web/views/globals/js/AlertResponse.js?ver=<?=time()?>"></script>
			<script defer src="/web/views/globals/js/NavBar.js?ver=<?=time()?>"></script>
			<?php if ($session->assist['PushNotice'] == 1): ?>
				<script defer src="/web/views/globals/js/pushNotice.js?ver=<?=time() ?>"></script>
			<?php endif; ?>
            <?php if (User::permission('repairs')): ?>
                <script defer src="/web/views/globals/js/RepairsPN.js?ver=<?=time() ?>"></script>
            <?php endif; ?>
			<?php if (User::permission('MA_modeller3D')): ?>
                <script defer src="/web/views/globals/js/new3DPN.js?ver=<?=time() ?>"></script>
            <?php endif; ?>
            <script defer src="/web/views/main/js/main.js?ver=<?=time()?>"></script>
            <script defer src="/web/views/main/js/ProgressModal.js?ver=<?=time()?>"></script>
        </footer>
    </div><!--content Wrapper-->
    
    <div id="new3DNoticeWrapp" class="row notices_wrapper"></div>
    <div id="RepairsPNWrapp" class="row notices_wrapper"></div>
    <div id="pushNoticeWrapp" class="row notices_wrapper"></div>
    <div id="alertResponseModal" aria-hidden="true" aria-labelledby="alertResponseModal" role="dialog" class="iziModal">
        <div id="alertResponseContent" style="padding: 10px" class="hidden"></div>
    </div>
    
    <?php if (isset($this->blocks['3DPanels'])) {echo $this->blocks['3DPanels'];}?>
    <?php require _globDIR_."/collectionsModal.php" ?>
    <script defer src="/web/views/globals/js/CollectionsModal.js?ver=<?=time()?>"></script>
    <?php $this->endBody() ?>
</body>
</html>
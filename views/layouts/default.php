<?php
use Views\_Globals\Models\User;
$navBar = $this->navBar;
$coll_silver = $navBar['collectionList']['silver'];
$coll_gold = $navBar['collectionList']['gold'];
$coll_diamond = $navBar['collectionList']['diamond'];
$coll_other = $navBar['collectionList']['other'];
$session = $this->session;
// Перекинем массив Юзера в JS
$wsUserData = [];
$wsUserData['id'] = $_SESSION['user']['id'];
$wsUserData['fio'] = $_SESSION['user']['fio'];
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
        <link rel="icon" href="/web/favicon.ico?ver=110">
    <?php else: ?>
        <link rel="icon" href="/favicon.ico?ver=110">
    <?php endif; ?>
    <link rel="stylesheet" href="/web/css/cssFW.css?ver=<?=time();?>">
    <link rel="stylesheet" href="/web/css/style.css?ver=<?=time();?>">
    <link rel="stylesheet" href="/web/css/style_adm.css?ver=<?=time();?>">
    <link rel="stylesheet" href="/web/css/bodyImg.css?ver=<?=time();?>">
    <link rel="stylesheet" href="/web/css/bootstrap.min.css">
    <!--<link rel="stylesheet" href="/web/css/bootstrap-theme.min.css">-->
    <link rel="stylesheet" href="/web/css/iziModal.min.css">
    <link rel="stylesheet" href="/web/css/iziToast.min.css">
    <link rel="stylesheet" href="/web/fontawesome5.9.0/css/all.min.css">
    <? $this->head() ?>
    <script src="/Views/_Globals/js/const.js?ver=<?=time()?>"></script>
    <script><?=$wsUserDataJS?></script>
    <script src="/Views/_Globals/js/webSocketConnect.js?ver=<?=time()?>"></script>
</head>

<body id="body" class="<?=$_SESSION['assist']['bodyImg']?>">
<?php $this->beginBody() ?>
	<div class="wrapper" id="content"> <!-- нужен что бы скрывать все для показа 3Д -->

        <nav class="navbar navbar-default nav-bar-marg">
            <div class="container-fluid">

                <div class="navbar-header">
                    <a class="navbar-brand"><?= _brandName_ ?></a>
                </div>

                <div class="navbar-collapse">

                    <ul class="nav nav-pills navbar-left inlblock" id="navnav">
                        <li role="presentation" class="<?=$this->varBlock['activeMenu']?>"><a href="/main">База</a></li>
                            <div class="btn-group">
                                <?php if ( User::permission('addModel') || User::permission('nomtnclature') ): ?>
                                <button type="button" class="btn btn-link topdividervertical dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-menu-hamburger"></span></button>
                                <ul class="dropdown-menu">
                                    <?php if ( User::permission('addModel') ): ?>
                                        <li>
                                            <a href="/add-edit/?id=0&component=1"><span class="glyphicon glyphicon-file"></span>&#160; Добавить модель</a>
                                        </li>
                                    <?php endif;?>
                                    <?php if ( User::permission('nomtnclature') ): ?>
                                        <li><a href="/nomenclature/"><span class="glyphicon glyphicon-list-alt"></span>&#160; Номенклатура</a></li>
                                    <?php endif;?>
                                </ul>
                                <?php endif;?>
                            </div>
                        <li role="presentation">
                            <button id="collSelect" data-izimodal-open="#collectionsModal" type="button" title="Выбрать Коллекцию" style="font-size: 18px; padding: 5px 8px 0 8px;" class="btn btn-link dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-gem"></i>
                            </button>
                        </li>
                    </ul>

                    <!-- /main/search= -->
                    <form action="/globals/search=<?=$_SESSION['searchFor']?>" method="post" <?=$searchStyle?> class="navbar-form navbar-left topSearchForm">
                        <?php if ( trueIsset( $session->getKey('countAmount') ) ) : ?>
                            <span class="cursorArrow" title="Найдено позиций"><?=$session->getKey('countAmount')?></span>
                        <?php endif; ?>
                        <div class="input-group">
                        <span class="input-group-btn">
                            <?php if ( $session->getKey('searchFor') ): ?>
                                <!-- /main/?search=resetSearch= -->
                                <a href="/globals/?search=resetSearch" class="btn btn-link" type="button" name="resetSearch" title="Сбросить поиск"><i class="fas fa-broom"></i></a>
                            <?php endif; ?>
                            <button class="btn btn-link" type="submit" name="search" title="Нажать для поиска">
                                <span class="glyphicon glyphicon-search"></span>
                            </button>
                        </span>
                            <input type="text" class="form-control topSearchInpt" title="Что искать" placeholder="Поиск..." name="searchFor" value="<?=$_SESSION['searchFor']?>">
                            <div class="input-group-btn">
                                <button type="button" id="searchInBtn" class="btn btn-link dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Где искать">
                                    <span><?=$navBar['searchInStr'];?> </span><span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a type="button" onclick="main.searchIn(1);" title="Поиск во всей Базе">В Базе</a></li>
                                    <li><a type="button" onclick="main.searchIn(2);" title="Поиск в выбраной коллекции">В Коллекции</a></li>
                                </ul>
                            </div><!-- /btn-group -->
                        </div><!-- /input-group -->
                    </form>

                    <form class="navbar-form topuserform navbar-right">
						<?php /** Уведомления о новых моделях для 3Д */ ?>
                        <?php if ( User::permission('MA_modeller3D') ): ?>
                            <div class="btn-group" id="new3DPNBadge">
                                <button title="Кол-во 3Д моделей в работу / в работе" type="button" class="btn btn-link topdividervertical dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="badge" style="background-color: #ffb317;!important;">
                                        <i class="fab fa-modx"></i>&#160;
                                        <span class="da_Badge"><?= $this->varBlock['models3DToWork'] ?></span>
                                        <span class="">/ </span>
                                        <span class="da_BadgeInWork"><?= $this->varBlock['models3DInWork'] ?></span>
                                    </span>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="pn_show"><span class="glyphicon glyphicon-eye-open"></span>&#160; Показать</a>
                                        <a class="pn_hide"><span class="glyphicon glyphicon-eye-close"></span>&#160; Спрятать</a>
                                    </li>
                                </ul>
                            </div>
                        <?php endif; ?>
                        <?php /** Уведомления о ремонтах */ ?>
                        <?php if ( User::permission('repairs') ): ?>
                            <div class="btn-group" id="repPNBadge">
                                <button title="Кол-во ремонтов в работу" type="button" class="btn btn-link topdividervertical dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="badge" style="background-color: #565c70;!important;">
                                        <i class="fas fa-tools"></i>&#160;
                                        <span class="da_Badge"><?= $this->varBlock['repairsToWork'] ?></span>
                                    </span>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="pn_rep_show"><span class="glyphicon glyphicon-eye-open"></span>&#160; Показать</a>
                                        <a class="pn_rep_hide"><span class="glyphicon glyphicon-eye-close"></span>&#160; Спрятать</a>
                                    </li>
                                </ul>
                            </div>
                        <?php endif; ?>
                        <?php /** Уведомления о моделях для утверждения дизайна */ ?>
                        <?php if ( User::getAccess() === 10 ): ?>
                            <div class="btn-group" id="designApproveBadge">
                                <button title="Уведомления о моделях для утверждения дизайна" type="button" class="btn btn-link topdividervertical dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="badge" style="background-color: #35a0da;!important;">
                                        <span class="glyphicon glyphicon-leaf"></span>&#160;
                                        <span class="da_Badge"><?= $this->varBlock['designApproveModels'] ?></span>
                                    </span>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="da_Show" href="/main/?regStat=35"><span class="glyphicon glyphicon-leaf"></span>&#160; Показать все</a>
                                    </li>
                                    <li>
                                        <a class="da_Show" href="/main/?regStat=none"><span class="glyphicon glyphicon-remove"></span>&#160; Сбросить</a>
                                    </li>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if ( User::getAccess() > 0 ): ?>
						<div class="btn-group" id="noticesBadge">
							<button title="Текущие Уведомления" type="button" class="btn btn-link topdividervertical dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<span class="badge" style="background-color: #4cc1be;!important;">
                                    <i class="far fa-flag"></i>&#160;
                                    <span class="pushNoticeBadge"></span>
                                </span>
							</button>
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
						</div>
                        <?php endif; ?>
                        <div class="btn-group">
                            <button type="button" class="btn btn-link topdividervertical dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="glyphicon glyphicon-<?=$navBar['glphsd']?>"></span>&#160;<?= User::getFIO() ?>&#160;<span class="caret"></span>
                            </button>
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
                                        <a href="<?=$navBar['navbarStatsUrl'];?>"><span class="glyphicon glyphicon-stats"></span>&#160; Статистика</a>
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
                        </div>
                    </form>

                </div><!-- /.navbar-collapse -->
            </div><!-- /.container-fluid -->
        </nav>
        <!-- Блок коллекций -->
        <div id="collection_block" coll_block class="" style="margin-top:70px; left: 50%; margin-right: -50%; transform: translate(-50%); ">
            <div class="row collection_blockRow" coll_block>
                <div class="col-xs-12 col-sm-6 col-md-3" style="max-height: 100%; padding: 0 5px 0 5px;">
                    <div coll_block class=" collItem_TOP">
                        Серебро ( <?= count($coll_silver) ?> )
                    </div>
                    <?php
                    foreach ( $coll_silver as $id => $name )
                    {
                        ?>
                        <a href="/main/?coll_show=<?=$id?>">
                            <div coll_block class=" collItem"><?= $name ?></div>
                        </a>
                        <?php
                    }
                    ?>
                </div>
                <div class="col-xs-12 col-sm-6 col-md-3" style="border-left: 1px solid #2F4F4F; max-height: 100%; padding: 0 5px 0 5px;">
                    <div coll_block class=" collItem_TOP">
                        Золото ( <?= count($coll_gold) ?> )
                    </div>
                    <?php
                    foreach ( $coll_gold as $id => $name )
                    {
                        ?>
                        <a href="/main/?coll_show=<?=$id?>">
                            <div coll_block class=" collItem"><?= $name ?></div>
                        </a>
                        <?php
                    }
                    ?>
                </div>
                <div class="col-xs-12 col-sm-6 col-md-3" style="border-left: 1px solid #2F4F4F; max-height: 100%; padding: 0 5px 0 5px;">
                    <div coll_block class=" collItem_TOP">
                        Бриллианты ( <?= count($coll_diamond) ?> )
                    </div>
                    <?php
                    foreach ( $coll_diamond as $id => $name )
                    {
                        ?>
                        <a href="/main/?coll_show=<?=$id?>">
                            <div coll_block class=" collItem"><?= $name ?></div>
                        </a>
                        <?php
                    }
                    ?>
                </div>
                <div class="col-xs-12 col-sm-6 col-md-3" style="border-left: 1px solid #2F4F4F; max-height: 100%; padding: 0 5px 0 5px;">
                    <div coll_block class=" collItem_TOP" >
                        Разные ( <?= count($coll_other) ?> )
                    </div>
                    <a href="/main/?coll_show=-1">
                        <div coll_block class="collItem">
                            Все
                        </div>
                    </a>
                    <?php
                    foreach ( $coll_other as $id => $name )
                    {
                        ?>
                        <a href="/main/?coll_show=<?=$id?>">
                            <div coll_block class=" collItem"><?= $name ?></div>
                        </a>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
        <!-- END Блок коллекций -->

        <?php
            $container = 'container';
            if ( $this->varBlock['container'] === 2 )
            {
                $container = 'containerFullWidth';
            } elseif ( $_SESSION['assist']['containerFullWidth'] == 1 )
            {
                $container = 'containerFullWidth';
            }
        ?>
        <div class="<?=$container?> content">
            <?=$content;?>
        </div><!--container-->
<?php
    if ( _DEV_MODE_ )
    {
        debug($_GET,'$_GET');
        debug($this->getQueryParams(),'QueryParams');
        debug($_SESSION,'$_SESSION');
        debug($_COOKIE,'$_COOKIE');
        debug( Views\vendor\core\Config::get(), "Config");
    }
?>
        <footer class="footer" style="box-shadow: 0 -1px 5px rgba(0,0,0,.075)">
            <div class="container">
                <?php if ( User::permission('addModel') ): ?>
                    <a href="/add-edit/?id=0&component=1" class="btn btn-primary">
                        <span class="glyphicon glyphicon-file"></span>
                        <strong> Добавить модель</strong>
                    </a>
                <?php endif; ?>
                <i class="" style="position: absolute; right: 0; margin-right: 15px; margin-top: 10px"><a href="/versions/" title="Список изменений">ver. <?=$this->currentVersion?></a> &#160; разработка - Вадим Быков</i>
            </div>
            <script src="/web/js_lib/jquery-3.2.1.min.js"></script>
            <script src="/web/js_lib/bootstrap.min.js"></script>
            <script src="/web/js_lib/iziModal.min.js"></script>
            <script src="/web/js_lib/iziToast.min.js"></script>

            <?php if ( _DEV_MODE_ ): // Зависимость от jquery и iziModal?>
                <div id="alertDebug" aria-hidden="true" aria-labelledby="alertDebug" role="dialog" class="iziModal">
                    <div id="alertDebugContent" class="hidden p2"></div>
                </div>
                <script src="/Views/_Globals/js/debug.js?ver=<?=time()?>"></script>
            <?php endif; ?>
            <script defer src="/Views/_Globals/js/alertResponse.js?ver=<?=time()?>"></script>
			<script defer src="/Views/_Globals/js/NavBar.js?ver=<?=time()?>"></script>
			<?php if ($_SESSION['assist']['PushNotice'] == 1): ?>
				<script defer src="/Views/_Globals/js/pushNotice.js?ver=<?=time() ?>"></script>
			<?php endif; ?>
            <?php if (User::permission('repairs')): ?>
                <script defer src="/Views/_Globals/js/RepairsPN.js?ver=<?=time() ?>"></script>
            <?php endif; ?>
			<?php if (User::permission('MA_modeller3D')): ?>
                <script defer src="/Views/_Globals/js/new3DPN.js?ver=<?=time() ?>"></script>
            <?php endif; ?>
            <script defer src="/Views/_Main/js/main.js?ver=<?=time()?>"></script>
            <script defer src="/Views/_Main/js/ProgressModal.js?ver=<?=time()?>"></script>

        </footer>

    </div><!--content-->
	<div id="new3DNoticeWrapp" class="row notices_wrapper"></div>
	<div id="RepairsPNWrapp" class="row notices_wrapper"></div>
	<div id="pushNoticeWrapp" class="row notices_wrapper"></div>
    <div id="alertResponseModal" aria-hidden="true" aria-labelledby="alertResponseModal" role="dialog" class="iziModal">
        <div id="alertResponseContent" style="padding: 10px" class="hidden"></div>
    </div>
    <?php if (isset($this->blocks['3DPanels'])) echo $this->blocks['3DPanels']; ?>
    <?php require _globDIR_."includes/CollectionsModal.php" ?>
    <script defer src="/Views/_Globals/js/CollectionsModal.js?ver=<?=time()?>"></script>
    <?php $this->endBody() ?>
</body>
</html>
<?php
use Views\_Globals\Models\User;
?>
<nav class="navbar navbar-default">
    <div class="container-fluid">

        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
            <a class="navbar-brand p1" href="#">
                <img alt="Brand" width="40" height="40" src="<?= _webDIR_HTTP_ . "picts/huflogo.png" ?>">
            </a>
            <a class="navbar-brand"><?= _brandName_ ?></a>

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
                <li class="<?=$this->varBlock['activeMenu']?>">
                    <a href="/main">Main <span class="sr-only">(current)</span></a>
                </li>
                <li>
                    <a id="collSelect" data-izimodal-open="#collectionsModal" type="button" title="Выбрать Коллекцию" style="font-size: 18px;" class="btn dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-gem"></i>
                    </a>
                </li>

                <?php if ( User::permission('addModel') || User::permission('nomtnclature') ): ?>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                        <span class="glyphicon glyphicon-menu-hamburger"></span>
                    </a>
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
                </li>
                <?php endif;?>
            </ul>

            <form class="navbar-form navbar-left" action="/globals/search=<?=$_SESSION['searchFor']?>" method="post" <?=$searchStyle?> >
                <?php if ( trueIsset( $session->getKey('countAmount') ) ) : ?>
                    <span class="cursorArrow" title="Найдено позиций"><?=$session->getKey('countAmount')?></span>
                <?php endif; ?>
                <?php if ( $session->getKey('searchFor') ): ?>
                    <!-- /main/?search=resetSearch= -->
                    <a href="/globals/?search=resetSearch" class="btn btn-link" type="button" name="resetSearch" title="Сбросить поиск"><i class="fas fa-broom"></i></a>
                <?php endif; ?>
                <button class="btn btn-link" type="submit" name="search" title="Нажать для поиска">
                    <span class="glyphicon glyphicon-search"></span>
                </button>
                <div class="form-group">
                    <input type="text" class="form-control" title="Что искать" placeholder="Search..." name="searchFor" value="<?=$_SESSION['searchFor']?>" >
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
                </li>
            </ul>
        </div><!-- /.navbar-collapse -->

    </div><!-- /.container-fluid -->
</nav>




<!-- OLD -->
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
                            <?php endif; ?>
                        </ul>
                    <?php endif;?>
                </div>
                <li role="presentation">

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
                                <a class="da_Show" href="/main/?regStat=35&showall"><span class="glyphicon glyphicon-leaf"></span>&#160; Показать все</a>
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
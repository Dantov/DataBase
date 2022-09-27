<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>

    <link href="/web/css/bootstrap.min.css" rel="stylesheet">
    <link href="/web/css/bootstrap-theme.min.css" rel="stylesheet">
</head>

<body style="">
<div class="container">

    <div class="jumbotron" style="margin-top: 15px">
        <?php if ( $this->err_lvl > 1 ) { ?>
            <h1>Type:   <b><?= $this->errorCodes[$errno]; ?></b> code: <i><?= $code ?></i></h1>
            <p>Message: <b><?= $message; ?></b> </p>
            <p>Path: <?= $errfile; ?> </p>
            <p>Line: <?= $errline; ?> </p>
            <p>File: <b><?= $info->getFilename(); ?></b></p>
            <p><?php //debug($trace); ?></p>

            <div style="background: #f7f7f7; padding: 10px;">
            <table class="table table-condensed table-hover">
                <thead>
                    <tr>
                        <th align="center">№</th>
                        <th align="center">code:</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $active = '';
                    $bold = [];
                    $bold['open'] = "";
                    $bold['close'] = "";
                    for ( $i = $startLine; $i < $endLine; $i++ ) {
                        if ( $i == $errline-1 ) {
                            $active = 'class="success"';
                            $bold['open'] = "<b>";
                            $bold['close'] = "</b>";
                        }
                ?>
                    <tr <?= $active ?> >
                        <td style=" border-top: 0px"><?= $i+1 ?></td>
                        <td style=" border-top: 0px"><?= $bold['open'] . htmlspecialchars($lines[$i]) . $bold['close'] ?></td>
                    </tr>
                <?php
                        $active = '';
                        $bold['open'] = "";
                        $bold['close'] = "";
                    }
                ?>
                </tbody>
            </table>
            </div>
            <?php debug($fullTrace,'Full Trace'); ?>
            <p></p>
            <p> <?=  "PHP ver. " . PHP_VERSION . " (" . PHP_OS . ")" ?> </p>
            <p> <?php debug($_SESSION,'$_SESSION'); ?> </p>
        <?php } else { ?>
            <h2>Внутренняя ошибка сервера! <b><?= $code ?></b></h2>
            <p>Сообщите разработчику.</p>
            <p>Или попробуйте вернуться на <a href="<?= _views_HTTP_ ?>" class="">Главную</a></p>
        <?php } ?>
    </div>

</div>
<script src="/web/js_lib/jquery-3.2.1.min.js"></script>
<script src="/web/js_lib/bootstrap.min.js"></script>
</body>

</html>

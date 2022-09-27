<?php
    $errno = $_GET['errno'];
    $errtext = $_GET['errtext'];
?>
<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?=$errtext;?></title>
        <link rel="stylesheet" href="/web/css/bootstrap.min.css">
        <link rel="icon" href="/web/favicon.ico?ver=110">
    </head>
    <body>
        <div class="container">
            <div class="row">
                <div class="col-xs-2">
                </div>
				<div class="col-xs-8">
                    <p></p>
                    <div class="alert alert-danger" role="alert">
                        <h2 class="center-block">Ошибка: Невозможно установить соединение с MySQL.</h2>
                        <p>Код: <?=$errno;?></p>
                        <p>Описание: <?=$errtext;?></p>
                    </div>
                    <div class="alert alert-info" role="alert">
                        <h4>
                            Попробейте вернутья на <b><a href="/main/">Главную страницу</a></b>.<br />
                            Убедитесь что сервер включен.
                        </h4>
                    </div>
                </div>
                <div class="col-xs-2"></div>
            </div>
            <script src="/web/js_lib/bootstrap.min.js"></script>
        </div>
    </body>
</html>
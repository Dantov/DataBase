<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->title ?></title>
    <link rel="icon" href="/favicon.ico?ver=106">
    <link rel="stylesheet" href="/web/css/style.css?ver=<?=time()?>">
    <link rel="stylesheet" href="/web/css/style_adm.css?ver=<?=time()?>">
    <link rel="stylesheet" href="/web/css/bootstrap.min.css">
    <? $this->head() ?>
</head>
<body>
<? $this->beginBody() ?>
    <div class="container content">
        <?=$content;?>
    </div>
<? $this->endBody() ?>
</body>
</html>
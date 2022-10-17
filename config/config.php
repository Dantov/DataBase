<?php
return [

    'basePath' => dirname(__DIR__),
    'uploadPath' => '/Stock',
    'cachePath' => '/runtime/cache',
    'layout' => 'default',
    'defaultController' => 'main',
    'version' => '2.5',
    'dataCompression' => true,
    'assistUpdate' => 38,

    /**
     *  mode
     *  0 - продакшн Без E_NOTICE,
     *  1 - продакшн Без E_NOTICE и E_Warning,
     *  2 - DEV all Errors,
     *  3 - DEV без E_NOTICE,
     */
    'errors' => [
        'enable' => true, // включает перехват ошибок фреймворком DTW.  false - отключает
        'logs'   => '/runtime/logs', // false - отключает логи
        'mode'   => _DEV_MODE_ ? 2 : 0,
    ],
	'https' => (_PROTOCOL_ === 'https' ? true : false), //false
    'csrf' => false, // валидация данных для форм и JS
    'classes' => [  // подключаемые классы
        'valitron' => 'libs\classes\valitron\src\Validator',
        'validator' => '\libs\classes\Validator',
    ],
    'db' => require_once "db_config.php",
    'libraries' => [
        'jquery' => true,
        'bootstrap' => 'bootstrap3',
        'fontAwesome' => true,
    ],
    'css' => [
        'css/stylesTest.css?ver='.time(),
        'css/style.css?ver='.time(),
    ],
    'js' => [
        'js/scrpt.js?ver='.time(),
    ],
    'jsOptions' => [
        'position' => 'endBody',
    ],
    'alias' => require_once 'alias.php',
];
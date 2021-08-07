<?php

return [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'uploadPath' => '/Stock',
    'cachePath' => '/runtime/cache',
    'layout' => 'default',
    'defaultController' => 'main',
    'version' => '2.203',
    'dataCompression' => true,
	'assistUpdate' => 2,

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
        'mode'   => _DEV_MODE_ ? 3 : 0,
    ],
    'csrf' => false, // валидация данных для форм и JS
    'classes' => [  // подключаемые классы
        //'cache' => 'dtw\Cache',
        'appCodes' => 'Views\vendor\libs\classes\AppCodes',
        'validator' => 'Views\vendor\libs\classes\valitron\src\Validator',
    ],
    'db' => require_once "db_config.php",
    'libraries' => [
        'jquery' => true,
        'bootstrap' => 'bootstrap3',
        'fontAwesome' => true,
    ],
    'css' => [
        'css/stylesTest.css?ver='.time(),
        'css/style.css?ver=105',
    ],
    'js' => [
        'js/scrpt.js?ver='.time(),
    ],
    'jsOptions' => [
        'position' => 'endBody',
    ],
    //'alias' => require_once 'aliases.php',
];
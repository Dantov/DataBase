<?php

if (!defined('_WORK_PLACE_')) define('_WORK_PLACE_', false); // true - работа false - дом

if ( _DEV_MODE_ )
{
    define('_brandName_', '3D модели "ХЮФ" Developer mode');
    define('DATABASE', 'ca455095_db'); //ca455095_dev
    define('MYSQLHOST', 'localhost'); // ca455095.mysql.tools

} else {
    define('_brandName_', '3D модели "ХЮФ"');
    define('DATABASE', 'ca455095_db'); 
    define('MYSQLHOST', 'localhost'); //ca455095.mysql.tools
}

define('_stockDIR_', _webDIR_.'Stock/');
define('_WEB_VIEWS_', _webDIR_.'views/');
define('_globDIR_', _webDIR_.'views/globals/');// подключить php скрипты

define('_stockDIR_HTTP_', _HOST_ROOT_.'web/Stock/'); // http://192.168.0.245/HUF_DB/web/Stock/

define('_HOST_views_', _HOST_web_.'views/'); // для ссылок

/*
echo '<pre>';
print_r(_PROTOCOL_);
echo '<br>';
print_r(_rootDIR_);
echo '<br>';
print_r(_rootDIR_HTTP_);
echo '<br>';
print_r($config);
echo '</pre>';
exit('XXX');
*/
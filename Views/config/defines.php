<?php
ini_set('date.timezone', 'Europe/Kiev');

if (!defined('_DEV_MODE_')) {
    define('_DEV_MODE_', true);
}
if (!defined('_WORK_PLACE_')) {
    define('_WORK_PLACE_', false); // true - работа false - дом
} 

define('_rootDIR_', filter_input(INPUT_SERVER, 'DOCUMENT_ROOT').'/');  // подключить скрипты $_SERVER['DOCUMENT_ROOT']

define('_stockDIR_', _rootDIR_.'Stock/');
define('_viewsDIR_', _rootDIR_.'Views/');  // подключить скрипты
define('_globDIR_', _viewsDIR_.'_Globals/');  // подключить скрипты

define('_CONFIG_', _viewsDIR_.'config/');

define('_coreDIR_', _viewsDIR_.'vendor/');
define('_vendorDIR_', _rootDIR_.'vendor/');

//define('_rootDIR_HTTP_', 'https://'.$_SERVER['HTTP_HOST'].'/'); // для ссылок
define('_rootDIR_HTTP_', 'http://'.filter_input(INPUT_SERVER, 'HTTP_HOST').'/'); // для ссылок $_SERVER['HTTP_HOST']
define('_webDIR_HTTP_', _rootDIR_HTTP_ . 'web/'); // для ссылок

define('_views_HTTP_', _rootDIR_HTTP_.'Views/'); // для ссылок
define('_glob_HTTP_', _views_HTTP_.'/_Globals/'); // для ссылок
define('_stockDIR_HTTP_', _rootDIR_HTTP_.'Stock/'); // http://192.168.0.245/HUF_DB/Stock/


if ( _DEV_MODE_ )
{
    define('_brandName_', '3D модели "ХЮФ" Developer mode');
    define('DATABASE', 'ca455095_db');
    define('MYSQLHOST', 'localhost');

} else {
    define('_brandName_', '3D модели "ХЮФ"');
    define('DATABASE', 'ca455095_db');
    define('MYSQLHOST', 'localhost');
}
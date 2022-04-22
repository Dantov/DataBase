<?php
ini_set('date.timezone', 'Europe/Kiev');

if (!defined( '_DEV_MODE_') ) define('_DEV_MODE_', true);
if (!defined( '_WORK_PLACE_') ) define('_WORK_PLACE_', false); // true - работа false - дом

define('_rootDIR_', $_SERVER['DOCUMENT_ROOT'].'/');  // подключить скрипты

define('_stockDIR_', _rootDIR_.'Stock/');
//define('_stockDIR_', implode('/', array_splice(explode('/',_rootDIR_),0,-2) ).'/HufDB.stock/Stock/');
define('_viewsDIR_', _rootDIR_.'views/');  // подключить скрипты
define('_globDIR_', _viewsDIR_.'_Globals/');  // подключить скрипты

define('_CONFIG_', _viewsDIR_.'config/');

define('_coreDIR_', _viewsDIR_.'vendor/');
define('_vendorDIR_', _rootDIR_.'vendor/');

//define('_rootDIR_HTTP_', 'https://'.$_SERVER['HTTP_HOST'].'/'); // для ссылок
define('_rootDIR_HTTP_', 'http://'.$_SERVER['HTTP_HOST'].'/'); // для ссылок
define('_webDIR_HTTP_', _rootDIR_HTTP_ . 'web/'); // для ссылок

define('_views_HTTP_', _rootDIR_HTTP_.'views/'); // для ссылок
define('_glob_HTTP_', _views_HTTP_.'/_Globals/'); // для ссылок
define('_stockDIR_HTTP_', _rootDIR_HTTP_.'Stock/'); // http://192.168.0.245/HUF_DB/Stock/
//define('_stockDIR_HTTP_', 'https://hufdb.stock/Stock/'); // http://192.168.0.245/HUF_DB/Stock/


if ( _DEV_MODE_ )
{
    define('_brandName_', '3D модели "ХЮФ" Developer mode');
    define('DATABASE', 'huf_models_dev');

} else {
    define('_brandName_', '3D модели "ХЮФ"');
    define('DATABASE', 'huf_models');
}
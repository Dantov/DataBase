<?php
ini_set('date.timezone', 'Europe/Kiev');


define('_rootDIR_', $_SERVER['DOCUMENT_ROOT'].'/');  // подключить скрипты

define('_stockDIR_', _rootDIR_.'Stock/');
define('_viewsDIR_', _rootDIR_.'Views/');  // подключить скрипты
define('_globDIR_', _viewsDIR_.'_Globals/');  // подключить скрипты

define('_CONFIG_', _viewsDIR_.'config/');

define('_coreDIR_', _viewsDIR_.'vendor/');
define('_vendorDIR_', _rootDIR_.'vendor/');

define('_rootDIR_HTTP_', 'http://'.$_SERVER['HTTP_HOST'].'/'); // для ссылок
define('_webDIR_HTTP_', _rootDIR_HTTP_ . 'web/'); // для ссылок

define('_views_HTTP_', _rootDIR_HTTP_.'views/'); // для ссылок
define('_glob_HTTP_', _rootDIR_HTTP_.'views/Glob_Controllers/'); // для ссылок
define('_stockDIR_HTTP_', _rootDIR_HTTP_.'Stock/'); // http://192.168.0.245/HUF_DB/Stock/


if ( _DEV_MODE_ ) //explode('/', _rootDIR_)[4] == 'HUF-DB-DEV'
{
    //define('_DEV_MODE_', true);
    //define('_titlePage_', 'HUF-3d Developing mode');
    
    define('_brandName_', '3D модели "ХЮФ" Developer mode');
    define('DATABASE', 'huf_models_dev');

} else {
    //define('_DEV_MODE_', false);
    //define('_titlePage_', 'HUF 3D models');
    
    define('_brandName_', '3D модели "ХЮФ"');
    define('DATABASE', 'huf_models');
}
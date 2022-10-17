<?php
if (!defined('_DEV_MODE_')) define('_DEV_MODE_', true);

// full path to root dir: /var/www/sitename/
//define('_rootDIR_', filter_input(INPUT_SERVER, 'DOCUMENT_ROOT').'/');  
define('_rootDIR_', dirname(dirname(__DIR__)).'/');  

define('_CONFIG_', _rootDIR_.'config/');
define('_webDIR_', _rootDIR_ . 'web/'); // for php includes
define('_pagesDIR_', _rootDIR_.'Pages/'); 
define('_vendorDIR_', _rootDIR_.'vendor/');
define('_soffitDIR_', _vendorDIR_. 'Soffit/');
define('_coreDIR_', _soffitDIR_ . 'core/');
define('_libsDIR_', _soffitDIR_ . 'libs/');

$https = 'off';
if (filter_has_var(INPUT_SERVER, 'HTTPS'))
	$https = filter_input(INPUT_SERVER, 'HTTPS');

//define('_PROTOCOL_', strtolower(explode('/',filter_input(INPUT_SERVER, 'SERVER_PROTOCOL'))[0]) );
define('_PROTOCOL_', ($https === 'on' ? 'https' : 'http') );

// EXAMPLE: http://soffit.fw/ OR https://soffit.fw/
define('_rootDIR_HTTP_', _PROTOCOL_.'://'.filter_input(INPUT_SERVER, 'HTTP_HOST').'/'); // для совместимости, удалить после
define('_HOST_ROOT_', _rootDIR_HTTP_);

//For JS/CSS includes
define('_webDIR_HTTP_', _rootDIR_HTTP_ . 'web/'); // для совместимости, удалить после
define('_HOST_web_', _webDIR_HTTP_); // для ссылок
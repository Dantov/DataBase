<?php
use Views\vendor\core\Autoloader;
use Views\vendor\core\Application;

require_once __DIR__ . '/config/defines.php';
require_once __DIR__ . '/vendor/libs/functions.php';

/*
if ( _DEV_MODE_ )
{
    //debug($_SERVER,'$_SERVER');
    debug(_rootDIR_,'_rootDIR_');
    debug(_coreDIR_,'_coreDIR_');
    debug(_stockDIR_,'_stockDIR_');
    debug(_vendorDIR_,'_vendorDIR_');
    phpinfo();
    exit;
}
*/

require_once _coreDIR_ . "core/Autoloader.php";
new Autoloader();

require_once _vendorDIR_ . "autoload.php";

$config = require_once _CONFIG_ . 'config.php';
(new Application($config))->run();
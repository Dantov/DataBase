<?php
use Views\vendor\core\Autoloader;
use Views\vendor\core\Application;

require_once __DIR__ . '/config/defines.php';
require_once __DIR__ . '/vendor/libs/functions.php';

require_once _coreDIR_ . "core/Autoloader.php";
new Autoloader();

require_once _vendorDIR_ . "autoload.php";

$config = require_once _CONFIG_ . 'config.php';

try {
    (new Application($config))->run();
} catch (Exception $e ) {
    echo "Error in entry point. <br>";
    echo "<b>Mess: </b>" . $e->getMessage() . PHP_EOL;
    echo "<b>Code: </b>" . $e->getCode() . "<br>" . PHP_EOL;
    echo "<b>In file: </b>" . $e->getFile() . "<br>" . PHP_EOL;
    echo "<b>On line: </b>" . $e->getLine() . "<br>" . PHP_EOL;
}
<?php
ini_set('date.timezone', 'Europe/Kiev');

use soffit\{Autoloader,Application};

if (!defined('_DEV_MODE_')) define('_DEV_MODE_', false);

require_once dirname(__DIR__) . "/vendor/Soffit/core/Autoloader.php";
require_once dirname(__DIR__) . "/vendor/autoload.php";

require_once _CONFIG_ . 'defines.php';
$config = require_once _CONFIG_ . 'config.php';


new Autoloader($config['alias']);
(new Application($config))->run();
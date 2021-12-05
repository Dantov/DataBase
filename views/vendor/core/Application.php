<?php
namespace Views\vendor\core;
use Views\vendor\core\Errors\ErrorHandler;
use Views\vendor\libs\classes\AppCodes;

class Application
{

    public $controllerPath = '';
    public $controllerName = '';

    protected $config = [];

    /**
     * Application constructor.
     * @param array $config
     * @throws \Exception
     */
    public function __construct($config=[])
    {
//        $d = new \DateTime();
//        debug(__CLASS__ . " created in " . $d->format("d.m.Y - H:i:s u") );

        if ( is_array($config) && !empty($config) ) $this->config = $config;

        Config::initConfig($config);
		
		//https redirect
		if ( Config::get('https') === true )
		{
			if ( !isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off' )
			{
				header("Location: https://". $_SERVER['HTTP_HOST'] . "/");
				exit;
			}
		} elseif ( Config::get('https') === false && (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') )
		{
			header("Location: http://". $_SERVER['HTTP_HOST'] . "/");
			exit;
		}

        new ErrorHandler($config['errors']);

        Router::parseRout($_SERVER['REQUEST_URI']);
        $this->controllerPath = Router::getRout();
        $this->controllerName = Router::getControllerName();


    }
    public function __destruct()
    {
//        $d = new \DateTime();
//        debug(__CLASS__ . " destr in " . $d->format("d.m.Y - H:i:s u") );
    }


    /**
     * @throws \Exception
     */
    protected function getController()
    {
        $class = $this->controllerPath;
        if ( !file_exists( _rootDIR_ .'/'. str_replace('\\','/', $class) . '.php' ) ) 
        {
            if ( !_DEV_MODE_ )
            {
                Router::parseRout( "/" . $this->config['defaultController'] . "/");
                $class = $this->controllerPath = Router::getRout();
                $this->controllerName = Router::getControllerName();
//                $class = $this->controllerPath = "Views\_Main\Controllers\MainController";
//                $this->controllerName = "main";
                if ( !file_exists( _rootDIR_ .'/'. str_replace('\\','/', $class) . '.php' ) )
                    throw new \Exception( AppCodes::getMessage(AppCodes::PAGE_NOT_FOUND)['message'] , AppCodes::PAGE_NOT_FOUND);

            } else {
                throw new \Exception( __CLASS__ . " - 'Controller " . $class . "' not found!" , 111);
            }

        }

        $controller = new $class($this->controllerName);

        if ( method_exists($controller, 'setQueryParams') ) $controller->setQueryParams(Router::getParams());
        if ( method_exists($controller, 'beforeAction') ) $controller->beforeAction();
        if ( method_exists($controller, 'action') )
        {
            $controller->action();
        } else {
            throw new \Exception("Метод action() не найден в контроллере ". $class ."!", 503 );
        }

        if ( method_exists($controller, 'afterAction') ) $controller->afterAction();
    }


    /**
     * Запуск приложения
     * @throws \Exception
     */
    public function run()
    {
        Registry::init();
        $this->getController();
    }
}
<?php
namespace soffit;

use libs\classes\AppCodes;

class Application
{

    public string $controllerPath = '';
    public string $controllerName = '';

    protected array $config = [];

    /**
     * Application constructor.
     * @param array $config
     * @throws \Exception
     */
    public function __construct( array $config=[] )
    {
//        $d = new \DateTime();
//        debug(__CLASS__ . " created in " . $d->format("d.m.Y - H:i:s u") );
        
        if ( !empty($config) ) $this->config = $config;

        Config::initConfig($config);
		
        //https redirect
        /*
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
        */

        //debug(Config::get(),'',1);
        new ErrorHandler($config['errors']);

        if ( filter_has_var(INPUT_SERVER, 'REQUEST_URI') )
        {
            Router::parseRout( filter_input(INPUT_SERVER, 'REQUEST_URI') );
        } else {
            throw new \Error('URI ERROR!', 500);
        }
        
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
        $classFile = _rootDIR_ . str_replace('\\','/', Router::getRoutFile()) . '.php';
        //debug($class,'$class');
        //debug($classFile,'file',1);
        if ( !file_exists($classFile) )
        {
            if ( !_DEV_MODE_ )
            {
                Router::parseRout( "/" . $this->config['defaultController'] . "/");
                $class = $this->controllerPath = Router::getRout();
                $this->controllerName = Router::getControllerName();
                $classFile = _rootDIR_ . str_replace('\\','/', Router::getRoutFile()) . '.php';

                if ( !file_exists($classFile) )
                    throw new \Exception( AppCodes::getMessage(AppCodes::PAGE_NOT_FOUND)['message'] , AppCodes::PAGE_NOT_FOUND);

            } else {
                throw new \Exception( __CLASS__ . " - 'Controller " . $class . "' not found!" , 101);
            }

        }
        $controller = new $class($this->controllerName);

        if ( method_exists($controller, 'setQueryParams') ) $controller->setQueryParams(Router::getParams());
        if ( method_exists($controller, 'beforeAction') ) $controller->beforeAction();
        if ( method_exists($controller, 'action') )
        {
            //debug($controller,'controller',1);
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
        $reg = Registry::init();
        $reg->sessions = 'soffit\Sessions';
        $reg->request = 'soffit\Request';

        $this->getController();
    }
}
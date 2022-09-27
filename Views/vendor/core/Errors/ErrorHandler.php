<?php
namespace Views\vendor\core\Errors;

use Views\vendor\core\Registry;

class ErrorHandler
{

    protected $err_lvl;
    protected $logs = false;
    protected $logsPath = '';

    protected $defConfig = [];

    protected $errorCodes = [
        E_ERROR => 'E_ERROR',
        E_WARNING => 'E_WARNING',
        E_PARSE => 'E_PARSE',
        E_NOTICE => 'E_NOTICE',
        E_CORE_ERROR => 'E_CORE_ERROR',
        E_CORE_WARNING => 'E_CORE_WARNING',
        E_COMPILE_ERROR => 'E_COMPILE_ERROR',
        E_COMPILE_WARNING => 'E_COMPILE_WARNING',
             E_USER_ERROR => 'E_USER_ERROR',
           E_USER_WARNING => 'E_USER_WARNING',
            E_USER_NOTICE => 'E_USER_NOTICE',
                 E_STRICT => 'E_STRICT',
        E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
               E_DEPRECATED => 'E_DEPRECATED',
          E_USER_DEPRECATED => 'E_USER_DEPRECATED',
                "exception" => "Exception",
    ];

    public function __construct( array $err_level )
    {
        $enable = $err_level['enable'] ?? false;
        $logs   = $err_level['logs']   ?? false;
        $mode   = $err_level['mode']   ?? 1;
        
        if ( $enable !== true ) {
            return;
        }
        
        if ( $logs !== false )
        {
            $this->logs = true;
            $this->logsPath = _rootDIR_ . $logs;            
            if ( !file_exists($this->logsPath) ) {
                mkdir($this->logsPath,0777,true);
            }
        }
        $this->err_lvl = $mode;
        $err_types = E_ALL;
        
        switch ($mode)
        {
            // production mode w/o E_NOTICE
            case 0:
                $err_types = E_ALL & ~E_NOTICE & ~E_STRICT;
                error_reporting(0);    
                break;
            case 1:
                $err_types = E_ALL & ~E_NOTICE & ~E_STRICT & ~E_WARNING;
                error_reporting(0);    
                break;
            case 2:
                $err_types = E_ALL;
                error_reporting(-1);    
                break;
            case 3:
                $err_types = E_ALL & ~E_NOTICE & ~E_STRICT;
                error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
                break;
            default:
                $err_types = E_ALL & ~E_NOTICE & ~E_STRICT & ~E_WARNING;
                error_reporting(0);  
                break;
        }
        

        set_error_handler( [$this,'errorHandler'], $err_types );
        register_shutdown_function([$this,'fatalErrorHandler']);
        set_exception_handler([$this,'exceptionHandler']);
    }

    public function exceptionHandler($e)
    {
        
        // пишем в лог, если включено
        if ( $this->logs ) $this->errorsLog('exception', $e->getMessage(), $e->getFile(), $e->getLine() );

        $this->displayError('exception', $e->getMessage(), $e->getFile(), $e->getLine(), $e->getCode(), $e->getTrace());
    }

    public function fatalErrorHandler()
    {
        
        $error = error_get_last();
        //print_r($error);
        if ( !empty($error) && ($error['type'] & ( E_ERROR | E_PARSE | E_COMPILE_ERROR | E_CORE_ERROR )) )
        {
            // пишем в лог, если включено
            if ( $this->logs ) $this->errorsLog($error['type'], $error['message'], $error['file'], $error['line']);

            $this->displayError($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }

    /**
     * @param $errno
     * @param $message
     * @param $errfile
     * @param $errline
     * @return bool
     * @throws \Exception
     */
    public function errorHandler($errno, $message, $errfile, $errline)
    {
        
        // пишем в лог, если включено
        if ( $this->logs ) $this->errorsLog($errno, $message, $errfile, $errline);

        $this->displayError($errno, $message, $errfile, $errline);
        return true;
    }

    protected function displayError($errno, $message, $errfile, $errline, $code=500, $trace=null)
    {
        http_response_code($code);

        // ob_list_handlers() - список открытых буферов
        // перед выводом сообщения об ошибке их все нужно закрыть
        // иначе будет выброшено содержимое буфера вместе с ошибкой!
        if ( $this->err_lvl !== 0 )
        {
            while ( ob_list_handlers() )
            {
                ob_end_clean();
            }
        }
        $fullTrace = $trace;
        if ( isset($trace[0]) )
        {
            $t = $trace[0];
            if ( isset($t['line']) )
            {
                $trace = $trace[0];
            } elseif ( isset($trace[1]) )
            {
                $trace = $trace[1];
            }
        }
        //$trace = isset($trace[0]['line']) ? $trace[0] : $trace[1];

        $errfile = $trace['file'] ?? $errfile;
        $errline = $trace['line'] ?? $errline;

        $lines = file($errfile);
        $startLine = ($errline - 10) < 0 ? 0: $errline - 10;
        $endLine = ($errline + 9) > count($lines)-1 ? count($lines)-1: $errline + 9;

        $info = new \SplFileInfo($errfile);

        if ( $this->err_lvl === 0 && $code == 404 )
        {
            require _viewsDIR_ . 'errors/404.php';
        } else {
            require _viewsDIR_ . 'errors/errorDisplay.php';
        }

        die;
    }

    /**
     * @param $errno
     * @param $message
     * @param $errfile
     * @param $errline
     * @throws \Exception
     */
    protected function errorsLog($errno, $message, $errfile, $errline)
    {
        
        $type = isset($this->errorCodes[$errno]) ? $this->errorCodes[$errno] : "";

        // Не верно! На ранних этапах загрузки приложения еще нет ID пользователя, чтобы полезть за ним в БД.
        // Будем брать только из сессии.
        //$gen = new \Views\_Globals\Models\General();
        //$gen->connectDBLite();
        //$user = $gen->getUser();
        $uIP = 'no ip';
        if ( filter_has_var(INPUT_SERVER, 'REMOTE_ADDR') )
        {
            $uIP = filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
        }
        $userGuest = [
            'id' => '0',
            'IP' => $uIP,
            'fio' => 'Undefined',
        ];
        $reg = Registry::init();
        $user = $reg->sessions->getKey('user')??$userGuest;
        if ( !isset($user['IP']) ) $user['IP'] = $uIP;
        
        $text  = "\n===========================================\n";
        $text .= "New Error from:{$user['fio']} uID:{$user['id']} IP:{$user['IP']}\n";
        $text .= "Date: [". date('Y-m-d H:i:s') ."] \n";
        $text .= "Type: [". $type ."] \n";
        $text .= "Message: ". $message ." \n";
        $text .= "Line: ". $errline ." \n";
        $text .= "File: ". $errfile ." \n";
        $text .= "===========================================\n";

        error_log($text, 3, $this->logsPath."/errors.log");
    }


}
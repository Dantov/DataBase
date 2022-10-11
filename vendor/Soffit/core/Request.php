<?php
namespace soffit;

/**
 * Description of Request
 *
 * @author Dantov
 */
class Request 
{
    public array $headers = [];
    protected array $server = [];
    protected array $post = [];
    protected array $get = [];

    public function __construct()
    {
        $this->server = $this->filterServer();
        if ( $this->isPost() ) $this->post = $this->filterPost();
        if ( $this->isGet() ) $this->get = $this->filterGet();
    }
    
    public function __get( string $name )
    {
        if ( $name === 'post' )
            return $this->post;

        if ( $name === 'get' )
            return $this->get;

        if ( $name === 'server' )
            return $this->server;
    }

    /**
     * Заполняет массив заголовков
     * @return array - массив заголовков
     */
    public function getHeaders()
    {
        if ($this->headers === []) {

            if (function_exists('getallheaders')) {
                $headers = getallheaders();
                foreach ($headers as $name => $value) {
                    $this->headers[$name] = $value;
                }
            } elseif (function_exists('http_get_request_headers')) {
                $headers = http_get_request_headers();
                foreach ($headers as $name => $value) {
                    $this->headers[$name] = $value;
                }
            } else {
                foreach ($this->server as $name => $value) {
                    if (strncmp($name, 'HTTP_', 5) === 0) {
                        $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                        $this->headers[$name] = $value;
                    }
                }
            }
        }
        return $this->headers;
    }
    
    /**
     * Проверяет если данные из Ajax
     * @return bool
     */
    public function isAjax() : bool
    {
        if ( filter_has_var(INPUT_SERVER, 'HTTP_X_REQUESTED_WITH' ) )
            $httpX = filter_input(INPUT_SERVER,'HTTP_X_REQUESTED_WITH');

        if ( ($httpX??'') === 'XMLHttpRequest' ) {
            return true;
        }

        return false;
    }

    public function isPost() : bool 
    {
        if ( filter_has_var(INPUT_SERVER, 'REQUEST_METHOD' ) )
            $post = filter_input(INPUT_SERVER,'REQUEST_METHOD');        

        if ( ($post??'') === 'POST' ) return true;
        return false;
    }
    
    public function isGet() : bool
    {
        if ( filter_has_var(INPUT_SERVER, 'REQUEST_METHOD' ) )
            $get = filter_input(INPUT_SERVER,'REQUEST_METHOD');

        if ( ($get??'') === 'GET' ) return true;
        return false;
    }

    public function post( string $name ) : mixed
    {
        if ( !$this->isPost() ) return null;

        if ( filter_has_var(INPUT_POST, $name) ) 
            return $_POST[$name];

        return null;
    }

    public function get( string $name ) : mixed
    {
        if ( !$this->isGet() ) return null;

        if ( filter_has_var(INPUT_GET, $name) ) 
            return $_GET[$name];

        return null;
    }

    /**
     * переход на др. страницу
     * @param string $url
     */
    public function redirect( string $url, array $params = []) : void
    {
        $paramsStr = '';
        if ( !empty($params) )
        {
            $paramCount = 0;
            foreach ($params as $paramName => $paramValue) 
            {
                if ( $paramCount === 0 )
                {
                    $paramsStr .= "?$paramName=$paramValue";
                     ++$paramCount;
                    continue;
                }
                $paramsStr .= "&$paramName=$paramValue";
                ++$paramCount;
            }
        }

        $first = substr($url, 0, 1);
        if ( $first == '/' || $first == '\\' ) {
            $url = ltrim($url,'/');
            $url = _rootDIR_HTTP_ . $url;
        } else {
            $url = _rootDIR_HTTP_ . $url;
        }
        header("Location:" . $url . $paramsStr);
        exit;
    }

    protected function filterServer() : array
    {
        if (!empty($this->server)) return $this->server;

        foreach( $_SERVER as $key => $value )
        {
            if ( filter_has_var( INPUT_SERVER, $key ) )
                $this->server[$key] = filter_input(INPUT_SERVER,$key);
        }
        return $this->server;
    }

    protected function filterGet() : array
    {
        if (!empty($this->get)) return $this->get;

        foreach( $_GET as $key => $value )
        {
            if ( filter_has_var( INPUT_GET, $key ) )
                $this->get[$key] = filter_input(INPUT_GET,$key);
        }
        return $this->get;
    }

    protected function filterPost() : array
    {
        if (!empty($this->post)) return $this->post;

        foreach( $_POST as $key => $value )
        {
            if ( filter_has_var( INPUT_POST, $key ) )
                $this->post[$key] = filter_input(INPUT_POST,$key);
        }
        return $this->post;
    }
}
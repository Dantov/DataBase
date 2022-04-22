<?php
namespace Views\vendor\core;


class Controller
{

    /**
     * @string $title
     * задаётся в контроллере или в файле страницы
     * */
    public $title = '';

    /**
     * @string $layout
     * Название шаблона задаётся в контроллере
     * */
    public $layout = '';

    public $phpFilesPack = [];
    public $jsPack = [];
    public $jsFilesPack = [];

    public $HEAD = 'head';
    public $BEGINBody = 'beginBody';
    public $ENDBody = 'endBody';

    /**
     * @string $layoutPath
     * дефолтный путь к шаблону
     * */
    public $layoutPath = _rootDIR_."Views/layouts/";

    /**
     * @array $varBlock
     * $blocks = []; нужен для передачи переменных из вида в шаблон
     * */
    public $varBlock = [];

    public $blocks = [];
    /**
     * @var array
     * содержит имена открытых блоков
     * каждый вызов endBlock() берет имя первого элемента массива
     * создает в $blocks такой же и помещает туда ob_get_clean()
     */
    protected $blockNames = [];

    /**
     * @string - имя контроллера
     */
    public $controllerName;
    protected $queryParams = [];

    /**
     * содержит методы для работы с сессиями
     * @var null|Sessions
     */
    public $session = null;
    
    /**
     * содержит методы для обработки запросов GET POST AJAX FILES
     * @var
     */
    public $request = null;

    /**
     * @var array - массив заголовков
     */
    public $headers = [];

    public function __construct($controllerName)
    {
        $this->controllerName = $controllerName;
        $this->request = new Request();
        $this->session = new Sessions();
    }

    public function setQueryParams($params)
    {
        if ( is_array($params) ) $this->queryParams = $params;
    }
    public function getQueryParams()
    {
        return $this->queryParams;
    }
    public function getQueryParam($param)
    {
        if ( !is_string($param) || empty($param) ) return null;
        if ( array_key_exists($param, $this->queryParams) ) return $this->queryParams[$param];
        return null;
    }
    public function isQueryParam(string $param) : bool
    {
        if ( empty($param) ) return false;
        if ( array_key_exists($param, $this->queryParams) ) return true;
        return false;
    }

    public function setTitle()
    {
        if ( empty( $this->title ) )  $this->title = "Powered by Dantov's Framework";
    }

    /**
     * Устанавливает шаблон из конфиг файла приложения, или из контроллера, если был задан в нем
     * @param string $layout
     * @throws \Exception
     */
    protected function setLayout( string $layout='' ) : void
    {
        if ( !empty($layout) ) {
            $this->layout = $layout;
        } elseif ( empty($this->layout) ) {
            $layout = Config::get('layout');
            if ( !empty($layout) && is_string($layout) )
            {
                $this->layout = $layout;
            } else {
                throw new \Exception( "Layout is empty! You need to set it in config, or by own hands in controller." );
            }
        }
    }

    /**
     * @param $filename
     * @param array $vars
     * @return mixed
     * @throws \Exception
     */
    public function render($filename, $vars=[])
    {
        if ( !empty($vars) && is_array($vars) ) extract($vars);

        ob_start();
        {
            require_once _rootDIR_ . 'Views/' .'_'. $this->controllerName . '/includes/'. $filename .'.php';
            $content = ob_get_contents();
        }
        ob_end_clean();

        return $this->renderLayout($content);
    }


    /**
     * обновить текущую старницу
     */
    public function refresh()
    {
        $params = '';
        if ( !empty($this->queryParams) )
        {
            $params = '?';
            foreach ( $this->queryParams as $paramName => $value )
            {
                $params .= $paramName . '=' . $value;
            }
        }
        header('Location: ' . _rootDIR_HTTP_ . $this->controllerName . '/' . $params );
        exit;
    }

    /**
     * переход на др. страницу
     * @param string $url
     */
    public function redirect($url='')
    {
        if ( !empty($url) ) 
        {
            $first = substr($url, 0, 1);
            if ( $first == '/' || $first == '\\' ) {
                $url = ltrim($url,'/');
                $url = _rootDIR_HTTP_ . $url;
            } else {
                $url = _rootDIR_HTTP_ . $url;
            }
            header("Location:" . $url);
            exit;
        }
    }

    /**
     * @param $content
     * @return mixed
     * @throws \Exception
     */
    protected function renderLayout($content)
    {
        $this->setLayout();
        $this->setTitle();

        $this->layoutPath .= $this->layout . '.php';
        if ( !file_exists( $this->layoutPath ) )
        {
            throw new \Exception("Шаблон <i>" . $this->layout . "</i> не найден в /views/layouts/");
        }

        return require_once $this->layoutPath;
    }

    public function startBlock($name)
    {
        if ( empty($name) ) return;
        $name .= "";
        $this->blockNames[$name] = $name;
        ob_start();
    }
    public function endBlock()
    {
        $name = array_shift($this->blockNames);
        if ( empty($name) ) return;
        $this->blocks[$name] = ob_get_clean();
    }



    // надо создать trait Для инклюдов
    /**
     * @param $fileName
     * @param $path
     * @param $vars
     * @param string $position
     * @throws \Exception
     */
    public function includePHPFile($fileName, $vars=[], $position='', $path='')
    {
        if ( empty($fileName) || !is_string($fileName) ) return;
        if ( !$position ) $position = $this->ENDBody;

        if ( !empty($path) )
        {
            $primalDir = $path;
            if ( !file_exists($path.$fileName) )
                throw new \Exception('Файл "' . $fileName . '" не найден в ' . $path,311);
        } else {
            $primalDir = _viewsDIR_ .'_'. $this->controllerName . '/includes/';
            if ( !file_exists($primalDir.$fileName) )
                throw new \Exception('Файл "' . $fileName . '" не найден в папе подключений текущего контроллера.',311);
        }

        $php['position'] = $position;
        $php['php'] = $primalDir.$fileName;

        if ( !empty($vars) && is_array($vars) ) $php['vars'] = $vars;

        $this->phpFilesPack[] = $php;
    }

    /**
     * @param $js
     * @param array $options
     * @param string $position
     * @throws \Exception
     */
    public function includeJS($js, $options=[], $position='')
    {
        if ( empty($js) || !is_string($js) ) return;
        if ( !is_array($options) )
            throw new \Exception('Опции должен быть массивом - ',2);
        if ( !$position ) $position = $this->ENDBody;

        $script['js'] = $js;
        $script['position'] = $position;

        $optionsStr = '';
        foreach ($options as $key => $option) {
            if ( $key === 'id' ) $optionsStr .= ' id="'.$option.'" ';
            switch ($option)
            {
                case 'defer':
                    $optionsStr .= 'defer ';
                    break;
                case 'async':
                    $optionsStr .= 'async ';
                    break;
            }
        }
        $script['options'] = $optionsStr;

        $this->jsPack[] = $script;
    }

    /**
     * @param $fileName
     * @param array $options
     * @param string $position
     * @throws \Error
     * @throws \Exception
     */
    public function includeJSFile($fileName, $options=[], $position='')
    {
        if ( empty($fileName) ) return;
        if ( !$position ) $position = $this->ENDBody;
        if ( !is_array($options) )
            throw new \Exception('Опции должен быть массивом - ',2);

        if ( isset( $options['path'] ) && !empty($options['path']) )
        {
            $http = explode('/',$options['path']);
            unset($http[0], $http[1], $http[2]);
            $http = implode('/',$http);

            $primalDir = _rootDIR_. $http;
            $httpPath = $options['path'];
            //debug($primalDir,'$primalDir');
            //debug($httpPath,'$primalDir',1);
            if ( !file_exists($primalDir.$fileName) )
                throw new \Exception('Файл "' . $fileName . '" не найден по указанному пути: ' . $httpPath,311);
        } else {
            $primalDir = _viewsDIR_ .'_'. $this->controllerName . '/js/';
            $httpPath = _views_HTTP_ .'_'. $this->controllerName . '/js/';
            if ( !file_exists($primalDir.$fileName) )
                throw new \Exception('Файл "' . $fileName . '" не найден в папе скриптов текущего контроллера.',311);
        }

        $script['position'] = $position;
        $script['src'] = $httpPath.$fileName;

        $optionsStr = '';
        foreach ($options as $key => $option) {
            if ( $key === 'id' ) $optionsStr .= ' id="'.$option.'" ';
            if ( $key === 'class' ) $optionsStr .= ' class="'.$option.'" ';

            switch ($option)
            {
                case 'defer':
                    $optionsStr .= 'defer ';
                    break;
                case 'async':
                    $optionsStr .= 'async ';
                    break;
                case 'timestamp':
                    $script['src'] .= "?v=" . time();
                    break;
            }
        }
        $script['options'] = $optionsStr;

        $this->jsFilesPack[] = $script;
    }

    /**
     * Имена методов совпадают с местом их подключения в шаблоне
     */
    public function head()
    {
        $method = explode('::',__METHOD__)[1];
        $this->includes($method);
    }
    public function beginBody()
    {
        $method = explode('::',__METHOD__)[1];
        $this->includes($method);
    }
    public function endBody()
    {
        $method = explode('::',__METHOD__)[1];
        $this->includes($method);
    }
    /**
     * Подключает файлы и скрипты в нужных позициях. В методах head() beginBody() endBody()
     * @param $method - имя метода в котором запустить цикл. Является так же, позицией подключаемого файла или скрипта
     */
    protected function includes($method) : void
    {
        foreach ($this->phpFilesPack as $pack)
        {
            if ( $method !== $pack['position'] ) continue;
            $this->includePHP($pack);
        }

        foreach ($this->jsPack as $pack)
        {
            if ( $method !== $pack['position'] ) continue;
            echo '<script '.$pack['options'].'>'.$pack['js'].'</script>';
        }

        foreach ($this->jsFilesPack as $pack)
        {
            if ( $method !== $pack['position'] ) continue;
            echo '<script '.$pack['options'].' src="'.$pack['src'].'"></script>';
        }
    }
    /**
     * Подключает php файлы, в методе includes($method)
     * Этот метод нужен что бы распакованные переменные extract($pack['vars']),
     * которые идут для подкл. файла, нормально инкапсулировались.
     * @param $pack - писок php файлов для подключения
     */
    protected function includePHP( $pack ) : void
    {
        if ( !empty($pack['vars']) && is_array($pack['vars']) ) extract($pack['vars']);
        require $pack['php'];
    }
}
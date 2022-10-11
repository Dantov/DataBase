<?php
namespace soffit;

/*
 * класс для испрользования в видах
 * создание ссылок, форм, скриптов
 * */
class HtmlHelper
{

    /**
     * Properties
     */
    protected $badChars = ['"', ',', '\\', '|', '<', '>'];
    protected $activeTagName;
    protected $activeTagAttributes = [];
    protected $activeTagText;



    /**
     * Static Properties
     * @var string
     * содержит открывающий тег формы со всеми атрибутами
     * */
    protected static $attributes = '';
    protected static $enctype = "application/x-www-form-urlencoded";
    protected static $action = "";
    protected static $method = "POST";

    protected static $definedURLParams = [];

    /*
     * поставим дефолтные данные на старте новой формы
     */
    protected static function setDefaultValues()
    {
        self::$attributes = '';
        self::$enctype = "application/x-www-form-urlencoded";
        self::$action = "";
        self::$method = "POST";
    }

    public static function defineURLParams( array $params = [] ) : void
    {
        if (!empty($params)) 
            self::$definedURLParams = $params;
    }

    public static function URL( string $url, array $params = []) : string
    {
        $url = str_replace('\\', '/', trim($url));
        $urlTrimmed = trim($url,'/');
        // в константах в конце нет слэша, ставим его здесь
        $url = $urlTrimmed;

        // Если один слешь - подставим текущий контроллер
        if ( empty($url) )
        {
            $url = _rootDIR_HTTP_ . Router::getControllerNameOrigin() . '/';
        } else {
            $url = _rootDIR_HTTP_ . $url . '/';
        }

        $paramsStr = '';
        $paramCount = 0;
        $definedURLParams = self::$definedURLParams;
        foreach ($params as $paramName => $paramValue) 
        {
            if ( array_key_exists($paramName, $definedURLParams) )
                unset($definedURLParams[$paramName]);

            if ( $paramCount === 0 )
            {
                $paramsStr .= "?$paramName=$paramValue";
                 ++$paramCount;
                continue;
            }
            $paramsStr .= "&$paramName=$paramValue";
            ++$paramCount;
        }
        foreach ($definedURLParams as $dParamName => $dParamValue) 
            $paramsStr .= "&$dParamName=$dParamValue";

        return $url .= $paramsStr;
    }


    protected function isOnlyLetters( string $str ) : bool
    {
        // "/^[a-zA-Z0-9]+$/" - буквы и цифры
        if ( !preg_match("/^[a-zA-Z]+$/", $str) )
           return false;

        return true;
    }
    protected function checkAttrValue( string $value ) : bool
    {
        // проверить каждый символ поля
        $symbols = preg_split('//u',$value,-1,PREG_SPLIT_NO_EMPTY);
        foreach ( $symbols as $symbol )
        {
            if ( in_array($symbol, $this->badChars) )
                return false;
        }

        return true;
    }
    protected function flushTagData()
    {
        $this->activeTagName = '';
        $this->activeTagAttributes = [];
        $this->activeTagText = '';
    }

    /**
     * Создадим произвольный тег
     * @param string $name
     * @return HtmlHelper
     * @throws \Exception
     */
    public function tag( string $name ) : HtmlHelper
    {
        if ( !empty($this->activeTagName) )
            throw new \Exception("Can't create multiple tags in one time in " . __CLASS__ );

        if ( !trueIsset($name) )
            throw new \Exception("Tag name can't be empty in " . __METHOD__ );

        // only letters
        if ( !$this->isOnlyLetters($name) )
            throw new \Exception('Wrong tag name in ' . __METHOD__ );

        $this->activeTagName = $name;
        return $this;
    }

    /**
     * Установим в тег аттрибуты
     * @param array $attributes
     * @return HtmlHelper
     * @throws \Exception
     */
    public function setAttr( array $attributes ) : HtmlHelper
    {

        foreach ( $attributes as $attr => $value )
        {
            if ( !$this->checkAttrValue($attr) )
                throw new \Exception('Wrong attribute name in ' . __METHOD__ , 500);

            if ( !$this->checkAttrValue($value) )
                throw new \Exception('Wrong attribute value in ' . __METHOD__ , 500);

            $this->activeTagAttributes[] = $attr.'="'.$value.'" ';
        }

        return $this;
    }

    public function setTagText( string $text ) : HtmlHelper
    {
        $this->activeTagText .= $text;

        return $this;
    }

    /**
     * Завершим создание тега
     * @return string
     * @throws \Exception
     */
    public function create() : string
    {
        if ( empty($this->activeTagName) )
            throw new \Exception("Have no tag names to create in " . __CLASS__ );

        $tag = '<' . $this->activeTagName;

        if ( count($this->activeTagAttributes) )
            $tag .= ' ';
        foreach ( $this->activeTagAttributes as $attrStr )
            $tag .= $attrStr . " ";

        $tag = trim($tag) . '>' . $this->activeTagText . '</' . $this->activeTagName . '>';

        $this->flushTagData();
        return $tag;
    }

    /**
     * a( $attributes = array )
     * ссылки
     * @param $text
     * @param string $url
     * @param array $attributes
     */
    public static function a( $text, $url='', $attributes=[] )
    {
        $url = self::URL($url);

        $href = 'href="'.$url.'"';
        $a = '<a '.$href;
        $attribStr = '';

        foreach ( $attributes as $attr => $val ) {
            $attribStr .= $attr.'="'.$val.'" ';
        }

        $a .= self::drawAttributes($attributes).'>';
        $a .= $text;
        $a .= '</a>';

        echo $a;
    }


    /**
     * избавляет от дублирования кода
     * @param array $attributes
     * @return string
     */
    public static function drawAttributes(array $attributes) {
        $attribStr = '';

        if ( !empty($attributes) ) {
            foreach ( $attributes as $attr => $val )
                $attribStr .= $attr.'="'.$val.'" ';
        }
        return $attribStr;
    }

    public static function setEnctype($enctype)
    {
        if ( is_string($enctype) && !empty($enctype) )
        {
            self::$enctype = $enctype;
        }
    }

    /*
     * стартует форму
     * @param string $action - url файла обработчика
     * @param array $attributes - доп аттрибуты
     * return object - методы по добалению полей
     * */
    /*
    public static function beginForm($action, $attributes=[]) {

        self::setDefaultValues();
        if ( !empty($action) ) self::$action = $action;
        if ( !empty($attributes) && is_array($attributes) )
        {
            foreach ( $attributes as $attr => $val ) {
                switch (strtolower($attr))
                {
                    case "action":
                        self::$action = $val;
                        continue;
                        break;
                    case "method":
                        self::$method = $val;
                        continue;
                        break;
                    case "enctype":
                        self::$enctype = $val;
                        continue;
                        break;
                }
                self::$attributes .= $attr.'="'.$val.'" ';
            }
        }

        ob_start();
        return new ActiveForm();
    }

    protected static function openFormTag()
    {
        $form = '<form ' . 'action="'.self::$action.'" ' . 'method="'.self::$method.'" ' . 'enctype="'. self::$enctype .'" ' . self::$attributes. '>';

        return $form;
    }
    /*
     * завершает форму
     * выводит на экран
     * return void
     * */
    /*
    public static function endForm() {
        $content = ob_get_contents();
        ob_end_clean();

        $inpt_csrf_ = '';
        // _csrf_ запишем в печеньку
        if ( AppProperties::getConfig()['csrf'] === true )
        {
            if ( !isset($_COOKIE['_csrf_']) ) {
                $_csrf_ = uniqid('csrf_').randomStringChars('en','12','all');
                setcookie("_csrf_", $_csrf_, time() + (3600*24), '/', $_SERVER['HTTP_HOST']);
            }
            $inpt_csrf_ = '<input type="hidden" name="_csrf_" value="'.$_COOKIE['_csrf_'].'" />';
        }

        echo self::openFormTag();
        echo $content;
        echo $inpt_csrf_;
        echo '</form>';
    }
    */


}
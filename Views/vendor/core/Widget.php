<?php

namespace Views\vendor\core;

/*
 * класс для создания виджетов
 */

use errors\NotFoundException;

class Widget
{

    public function __set($name, $value)
    {
        if ($this->$name) $this->$name = $value;
    }
    public function __isset($name)
    {
        if ( isset($this->$name) ) return true;
        return false;
    }

    protected function __construct($config)
    {
        foreach ($config as $name => $value)
        {
            if ( isset($this->$name) ) $this->$name = $value;
            //debug($name, $name);
        }
    }

    public static function begin($config)
    {
        ob_start();
    }
    public static function end()
    {
        return ob_get_clean();
    }

    public static function widget($config=[])
    {
        
        try {
            //$config['_class_'] = get_called_class();
            //$class = get_called_class();
            $widget = new static($config);
            $widget->init();
            $out = $widget->run();
        } catch (\Exception $e) {
            throw $e;
        }

        return $out;
    }

    public function init()
    {

    }

    /*
     * @var $path string - путь к файлу
     * подключит файл представления (какой нибудь вид с html кодом) для его отображения здесь
     */
    protected function render($path)
    {
        if ( file_exists($path) )
        {
            require $path;
        } else {
            trigger_error('Файл виджета ' . $path . ' не найден',E_USER_ERROR);
            //throw new NotFoundException('Файл виджета' . $path . ' не найден');
        }
    }

    /*
     * должен быть определен пользователем
     */
    public function run()
    {

    }


}
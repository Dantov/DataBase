<?php

class ClassNotFoundException extends \Exception
{

    public function __construct( $class = "", $code = 500, Throwable $previous = null )
    {
        $message = "Класс не найден " . $class;
        parent::__construct( $message, $code, $previous );
    }


}
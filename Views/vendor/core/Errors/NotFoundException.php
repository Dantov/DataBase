<?php

class NotFoundException extends Exception
{
    public function __construct( $message = "Страница не найдена", $code = 404 )
    {
        parent::__construct( $message, $code );
    }

}
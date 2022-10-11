<?php
namespace soffit\errors;
use libs\classes\AppCodes;

class NotFoundException extends Exception
{
    public function __construct( $message = "Страница не найдена", $code = 404 )
    {
        parent::__construct( $message, $code );
    }

}
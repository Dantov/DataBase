<?php
namespace Views\_Help\Controllers;
use Views\_Globals\Controllers\GeneralController;

class HelpController extends GeneralController
{
    public string $title = "ХЮФ 3Д Помощь";
    
    public function action()
    {

    	
        return $this->render('help');
    }

}
<?php
namespace controllers;


class HelpController extends GeneralController
{
    public string $title = "ХЮФ 3Д Помощь";
    
    public function action()
    {

    	
        return $this->render('help');
    }

}
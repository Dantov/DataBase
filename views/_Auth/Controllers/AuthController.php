<?php
namespace Views\_Auth\Controllers;

use Views\vendor\core\{
    Controller, Cookies, Config
};

class AuthController extends Controller
{

    public $action = '';

    public $title = 'ХЮФ 3Д :: Вход';
    public $layout = 'auth';


    public function beforeAction()
    {
        $session = $this->session;
        
        $action = $this->getQueryParam('a');
        switch ( $action )
        {
            case "exit":
                $this->actionExit();
                break;
            default:
                $this->action = $action;
                break;
        }

        if( $session->getKey('access') === true ) $this->redirect('/');
        if ( (int)Cookies::getOne('meme_sessA') === 1 ) $this->redirect('/');
    }


    /**
     * @throws \Exception
     */
    public function action()
    {
        $access = 0;
        $userRow = [];

        if ( isset($_POST['submit']) )
        {
            $general = new \Views\_Globals\Models\General();
            $connection = $general->connectDBLite();

            $login = htmlspecialchars( strip_tags( trim($_POST['login']) ), ENT_QUOTES );
            $login = mysqli_real_escape_string($connection, $login);

            $userRow = $general->findOne(" SELECT * FROM users WHERE login='$login' ");

            if ( $userRow ) {
                $access = 1; //правильный логин
                $pass = trim($_POST['pass']);

                if ( $userRow['pass'] === $pass ) {
                    $access = 2; //правильный пароль
                } else {
                    $this->session->setFlash('wrongPass',' не верен!');
                    //$wrongPass = ' не верен!';
                }
            } else {
                $this->session->setFlash('wrongLog',' не верен!');
                //$wrongLog = ' не верен!';
            }
        }

        if ( $access === 2 ) 
        {
            $this->actionEnter($userRow);
        }

        //$this->includePHPFile('enterModal.php');
        //$this->includeJSFile('EnterModal.js', ['defer','timestamp']);

        $compacted = compact(['login']);
        return $this->render('auth', $compacted);
    }

    protected function actionEnter($userRow)
    {
        $session = $this->session;

        $user['id'] 	= $userRow['id'];
        $user['access'] = $userRow['access'];
        $user['fio']	= $userRow['fio'];
        $session->setKey('user', $user);

        $session->setKey('access', true);

        $assist['maxPos']		    = 24; 		// кол-во выводимых позиций по дефолту
        $assist['regStat']         = "Нет"; 	// выбор статуса по умоляанию
        $assist['byStatHistory']   = 0;  	// искать в истории статусов
        $assist['wcSort']          = []; 	    // выбор рабочего участка по умоляанию
        $assist['searchIn']        = 1;
        $assist['reg']             = "number_3d"; // сорттровка по дефолту
        $assist['startfromPage']   = (int)0; 		// начальная страница пагинации
        $assist['page']            = (int)0; 		// устанавливаем первую страницу
        $assist['drawBy_']         = 1; 		// 2 полоски, 1 квадратики
        $assist['sortDirect']      = "DESC"; 	// по умолчанию
        $assist['collectionName']  = "Все Коллекции";
        $assist['collection_id']   = -1;		// все коллекции
        $assist['containerFullWidth'] = 2;		// на всю ширину
        $assist['PushNotice']      = 1;		// показываем уведомления
        $assist['update']          = Config::get('assistUpdate');
        $assist['bodyImg']         = 'bodyimg0'; // название класса
        $session->setKey('assist', $assist);
        
        $selectionMode['activeClass'] = "";
        $selectionMode['models'] = [];
        $session->setKey('selectionMode', $selectionMode);
        $session->setKey('lastTime', 0);

        // если установлен флажок на "запомнить меня" пишем все в печеньки
        if ( isset($_POST['memeMe']) ? 1 : 0 )
        {
            $expired = time()+(3600*24*30);
            Cookies::set("meme_sessA", 1, $expired);

            foreach( $user as $key => $value )
            {
                Cookies::set("user[$key]", $value, $expired);
            }
            foreach( $assist as $key => $value )
            {
                if ( $key == 'wcSort' ) continue;
                Cookies::set("assist[$key]", $value, $expired);
            }
        }

        $this->redirect('/main/');
    }

    protected function actionExit()
    {
        if ( Cookies::getAll() )
        {
            Cookies::dellAllCookies();
        }
        $this->session->destroySession();
        $this->redirect('/auth/');
    }

}
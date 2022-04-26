<?php
namespace Views\_Auth\Controllers;

use Views\_Globals\Models\General;
use Views\vendor\core\{
    ActiveQuery, Controller, Cookies, Config
};
use Views\vendor\libs\classes\Validator;

class AuthController extends Controller
{

    public $action = '';

    protected $users = [];
    protected $user = [];

    public $title = 'ХЮФ 3Д :: Вход';
    public $layout = 'auth';


    public function beforeAction()
    {
        $action = $this->getQueryParam('a');

        switch ($action) {
            case "exit":
                $this->actionExit();
                break;
            default:
                $this->action = $action; // simply so...
                break;
        }
        if ($this->session->getKey('access') === true) $this->redirect('/');
        if ((int)Cookies::getOne('meme_sessA') === 1) $this->redirect('/');
    }


    /**
     * @throws \Exception
     */
    public function action()
    {
        $submit = filter_has_var(INPUT_POST, 'submit');
        $haveLogin = filter_has_var(INPUT_POST, 'login');
        $havePass = filter_has_var(INPUT_POST, 'pass');

        if ( $submit && $haveLogin && $havePass )
        {
            (new General())->connectDBLite(); // необходимость

            $aq = new ActiveQuery(['users']);
            $this->users = $aq->users->select(['*'])->asArray()->exe();

            if ( $this->checkLogin($_POST['login']) )
            {
                if ( $this->checkPassword($_POST['pass']) )
                    $this->actionEnter($this->user);

            } else {
                $this->session->setFlash('wrongLog', ' не верен!');
            }
        }

        $compacted = compact([]);
        return $this->render('auth', $compacted);
    }

    /**
     * @param $password
     * @return bool
     * @throws \Exception
     */
    protected function checkPassword($password) : bool
    {
        if ( !isset($this->user['pass']) )
            return false;

        $v = new Validator();
        $pass = $v->ValidateField('password', $password);
        if ( password_verify($pass, $this->user['pass']) )
        {
            return true;
        } else {
            $this->session->setFlash('wrongPass', ' не верен!');
        }

        return false;
    }

    /**
     * @param $login
     * @return bool
     * @throws \Exception
     */
    protected function checkLogin($login) : bool
    {
        $v = new Validator();
        $login = $v->ValidateField('login', $login);

        if ($v->getLastError())
            return false;

        foreach ( $this->users as $user )
        {
            if ( isset($user['login']) )
            {
                //hash_equals();
                //Крайне важно задавать строку с пользовательскими данными вторым аргументом, а не первым.
                if ( hash_equals($user['login'], $login) )
                {
                    $this->user = $user;
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param $userRow
     * @throws \Exception
     */
    protected function actionEnter($userRow)
    {
        $session = $this->session;

        $user['id'] = $userRow['id'];
        $user['access'] = $userRow['access'];
        $user['fio'] = $userRow['fio'];
        $session->setKey('user', $user);

        $session->setKey('access', true);

        $assist['maxPos'] = 48;        // кол-во выводимых позиций по дефолту
        $assist['regStat'] = "Нет";    // выбор статуса по умоляанию
        $assist['byStatHistory'] = 0;    // искать в истории статусов
        $assist['wcSort'] = [];        // выбор рабочего участка по умоляанию
        $assist['searchIn'] = 1;
        $assist['reg'] = "number_3d"; // сорттровка по дефолту
        $assist['startfromPage'] = (int)0;        // начальная страница пагинации
        $assist['page'] = (int)0;        // устанавливаем первую страницу
        $assist['drawBy_'] = 1;        // 2 полоски, 1 квадратики
        $assist['sortDirect'] = "DESC";    // по умолчанию
        $assist['collectionName'] = "Все Коллекции";
        $assist['collection_id'] = -1;        // все коллекции
        $assist['containerFullWidth'] = 2;        // на всю ширину
        $assist['PushNotice'] = 1;        // показываем уведомления
        $assist['update'] = Config::get('assistUpdate');
        $assist['bodyImg'] = 'bodyimg0'; // название класса
        $session->setKey('assist', $assist);

        $selectionMode['activeClass'] = "";
        $selectionMode['models'] = [];
        $session->setKey('selectionMode', $selectionMode);
        $session->setKey('lastTime', 0);

        // если установлен флажок на "запомнить меня" пишем все в печеньки
        if (isset($_POST['memeMe']) ? 1 : 0) {
            $expired = time() + (3600 * 24 * 30);
            Cookies::set("meme_sessA", 1, $expired);

            foreach ($user as $key => $value) {
                Cookies::set("user[$key]", $value, $expired);
            }
            foreach ($assist as $key => $value) {
                if ($key == 'wcSort') continue;
                Cookies::set("assist[$key]", $value, $expired);
            }
        }

        $this->redirect('/main/');
    }

    protected function actionExit()
    {
        if (Cookies::getAll()) {
            Cookies::dellAllCookies();
        }
        $this->session->destroySession();

        //delete user row in usersOnline table, maybe...

        $this->redirect('/auth/');
    }

}
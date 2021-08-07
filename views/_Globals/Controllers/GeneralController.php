<?php
namespace Views\_Globals\Controllers;
use Views\_Globals\Models\{General, User};
use Views\vendor\core\{
    Controller, Cookies, Config, db\Database
};
use Views\vendor\libs\classes\AppCodes;

class GeneralController extends Controller
{

    public $currentVersion = '';
    public $navBar;

    /**
     * GeneralController constructor.
     * @param $controllerName
     * @throws \Exception
     */
    public function __construct($controllerName)
    {
        parent::__construct($controllerName);

        $this->currentVersion = Config::get('version');

        $this->accessControl();
        $this->navBarController();

        $wp = _WORK_PLACE_ ? 'true' : 'false';
        $js = <<<JS
            const _WORK_PLACE_ = {$wp};
JS;
        $this->includeJS($js,[],$this->HEAD);
    }

    /**
     * @throws \Exception
     * Гарантированно выйдем из БД
     */
    public function afterAction()
    {
        (Database::instance())->destroy();
    }

    protected function accessControl()
    {
        $session = $this->session;

        if ( Cookies::getOne('meme_sessA') )
        {
            if ( !$session->getKey('access') )
            {
                $session->setKey('access', Cookies::getOne('meme_sessA') );
                if ( $assistCookies = Cookies::getOne('assist') )
                {
                    $assist = [];
                    foreach ( $assistCookies?:[] as $key => $value) $assist[$key] = $value;
                    $session->setKey('assist', $assist);
                }
            }
            if ( !$session->getKey('user') )
            {
                if ( $userCookies = Cookies::getOne('user') )
                {
                    $user = [];
                    foreach ($userCookies?:[] as $key => $value) $user[$key] = $value;
                    $session->setKey('user', $user);
                }
            }
        }

        $access = $session->getKey('access');
        $assist = $session->getKey('assist');

        if( $access !== true || $assist['update'] !== Config::get('assistUpdate') ) $this->redirect('/auth/?a=exit');
    }

    /**
     * @throws \Exception
     */
    protected function navBarController()
    {
        $navBar = [];

        $navBar['userid'] = $_SESSION['user']['id'];
        $navBar['userFio'] = $_SESSION['user']['fio'];
        $navBar['userAccess'] = $_SESSION['user']['access'];

        $navBar['glphsd'] = 'user';
        if ( $navBar['userFio'] == 'Участок ПДО' ) $navBar['glphsd'] = 'paperclip';

        $searchIn = (int)$_SESSION['assist']['searchIn'];
        if ( $searchIn === 1 ) $navBar['searchInStr'] = "В Базе";
        if ( $searchIn === 2 ) $navBar['searchInStr'] = "В Коллекции";

        $navBar['searchStyle']='style="margin-left:100px;"';
        $navBar['topAddModel'] = 'hidden';
        $navBar['navbarStatsShow'] = "hidden";
        $navBar['navbarStatsUrl'] = '';

        if ( $navBar['userAccess'] == 1 || $navBar['userAccess'] == 2 )
        {
            $navBar['searchStyle'] = '';
            $navBar['topAddModel'] = '';
            $navBar['navbarStatsUrl'] = _rootDIR_HTTP_ . "Statistic/";
            $navBar['navbarStatsShow'] = "";
        }

        $navBar['navbarDevShow'] = 'hidden';
        $navBar['navbarDevUrl'] = '';
        if ( $navBar['userid'] == 1 || $navBar['userid'] == 4 ) //быков дзюба
        {
            $navBar['navbarDevShow'] = '';
            $navBar['navbarDevUrl'] = _rootDIR_HTTP_ . 'hufdb-new';
        }

        $general = new General();
        $general->connectDBLite();

        $collections_arr = $general->findAsArray(" SELECT id,name FROM service_data WHERE tab='collections' ORDER BY name ");

        $navBar['collectionList'] = $this->getCollections($collections_arr);

        $this->navBar = $navBar;

        $this->varBlock['designApproveModels'] = $general->getDesignApproveModels();
		
		// Показать ремонты моделлерам
        if ( User::permission('repairs') )
            $this->varBlock['repairsToWork'] = $general->countRepairsToWork();
		
		// Для ПДО показать все не завершенные ремонты
		if ( User::permission('repairs') && User::getAccess() == 8 )
            $this->varBlock['repairsToWork'] = $general->countRepairsToShow();
		
		// Показать модели в работу
		if ( User::permission('MA_modeller3D') )
            $this->varBlock['models3DToWork'] = $general->countModels3DToWork();
    }

    protected function getCollections($coll_res)
    {
        $collectionListDiamond = [];
        $collectionListGold = [];
        $collectionListSilver = [];
        $collectionOther = [];

        foreach( $coll_res as &$collection )
        {
            $haystack = mb_strtolower($collection['name']);

            if ( stristr( $haystack, 'сереб' ) || stristr( $haystack, 'silver' ) )
            {
                $collectionListSilver[ $collection['id'] ] = $collection['name'];
                continue;
            }
            if ( stristr( $haystack, 'золото' ) || stristr( $haystack, 'невесомость циркон' ) || stristr( $haystack, 'невесомость с ситалами' ) || stristr( $haystack, 'gold' ) )
            {
                $collectionListGold[$collection['id']] = $collection['name'];
                continue;
            }
            if ( stristr( $haystack, 'брилл' ) || stristr( $haystack, 'diam' ) )
            {
                $collectionListDiamond[$collection['id']] = $collection['name'];
                continue;
            }
            $collectionOther[$collection['id']] = $collection['name'];
        }

        $res['silver'] = $collectionListSilver;
        $res['gold'] = $collectionListGold;
        $res['diamond'] = $collectionListDiamond;
        $res['other'] = $collectionOther;

        return $res;
    }


    /**
     * @param $e \object
     * @throws \Exception
     */
    protected function serverError_ajax($e)
    {
        try {
            if ( _DEV_MODE_ )
            {
                $err = [
                    'message'=>$e->getMessage(),
                    'code'=>$e->getCode(),
                    'file'=>$e->getFile(),
                    'line'=>$e->getLine(),
                    'trace'=>$e->getTrace(),
                    'previous'=>$e->getPrevious(),
                ];
                exit(json_encode(['error' => $err]));
            } else {
                exit(json_encode(['error' => AppCodes::getMessage(AppCodes::SERVER_ERROR)]));
            }
        } catch ( \Exception $eAppCodes ) {
            if ( _DEV_MODE_ ) {
                $errArrCodes = [
                    'code' => $eAppCodes->getCode(),
                    'message' => $eAppCodes->getMessage(),
                ];
                exit(json_encode(['error' => $errArrCodes]));
            } else {
                exit(json_encode(['error' => ['message'=>'Server Error', 'code'=>500]]));
            }
        }
    }

}
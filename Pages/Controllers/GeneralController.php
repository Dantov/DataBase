<?php
namespace controllers;

use models\{General, User, Statistic};
use soffit\{Router, Controller, Cookies, Config};
use libs\classes\AppCodes;

class GeneralController extends Controller
{

    public string $currentVersion = '';
    
    public array $navBar;

    public bool $isMobile = false;
    public bool $isDesktop = false;

    protected array $browsers = [];
    public string $browser = '';

    /**
     * GeneralController constructor.
     * @param $controllerName
     * @throws \Exception
     */
    public function __construct( string $controllerName='' )
    {
        
        parent::__construct( Router::getControllerName() );

        $this->currentVersion = Config::get('version');
        $this->isMobile       = $this->isMobileCheck();
        $this->isDesktop      = !$this->isMobile;

        $this->accessControl();
        $this->navBarController();

        $stat = new Statistic();
        $stat->setUserOnline();
        $stat->removeExpiredUsers();
        
        $wp = _WORK_PLACE_ ? 'true' : 'false';
        $isMb = $this->isMobile ? 'true' : 'false';

        $js = <<<JS
            const _IS_MOBILE_  = {$isMb};
            const _IS_DESKTOP_ = !_IS_MOBILE_;
            const _WORK_PLACE_ = {$wp};
JS;
        $this->includeJS($js,[],$this->HEAD);
    }

    public function isMobileCheck()
    {
        $ua = '';
        if (filter_has_var(INPUT_SERVER,'HTTP_USER_AGENT'))
            $ua = filter_input(INPUT_SERVER,'HTTP_USER_AGENT');
        
        //$ua = $_SERVER['HTTP_USER_AGENT']??" ";
        return stripos($ua,'mobile') !== false ? true : false;
    }

    public function currentBrowser()
    {

    }

    /**
     * @throws \Exception
     * Гарантированно выйдем из БД
     */
    public function afterAction()
    {
        
    }

    protected function accessControl()
    {
        if ( Cookies::getOne('meme_sessA') )
        {
            $this->unpackCookies();
        }
        
        $access = $this->session->getKey('access');
        $assist = $this->session->getKey('assist');   
        
        if ($access !== true || ( ($assist['update']??0) !== Config::get('assistUpdate'))) {
            $this->redirect('/auth', ['a' => 'exit']);
        }
    }
    
    protected function unpackCookies() 
    {
        $session = $this->session;
        
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

    /**
     * @throws \Exception
     */
    protected function navBarController()
    {
        $session = $this->session;
        $user = $session->getKey('user');
        $assist = $session->getKey('assist');
        
        $navBar = [];
        $navBar['userid'] = $user['id']??'';
        $navBar['userFio'] = $user['fio']??'';
        $navBar['userAccess'] = $user['access']??'';

        $navBar['glphsd'] = 'user';
        if ($navBar['userFio'] == 'Участок ПДО') {
            $navBar['glphsd'] = 'paperclip';
        }

        $searchIn = $assist['searchIn']??'';
        if ((int)$searchIn === 1) {
            $navBar['searchInStr'] = "В Базе";
        }
        if ((int)$searchIn === 2) {
            $navBar['searchInStr'] = "В Коллекции";
        }

        $navBar['searchStyle']='style="margin-left:100px;"';
        $navBar['topAddModel'] = 'hidden';
        $navBar['navbarStatsShow'] = "hidden";
        $navBar['navbarStatsUrl'] = '';

        if ( $navBar['userAccess'] == 1 || $navBar['userAccess'] == 2 )
        {
            $navBar['searchStyle'] = '';
            $navBar['topAddModel'] = '';
            $navBar['navbarStatsUrl'] = _rootDIR_HTTP_ . "statistic/";
            $navBar['navbarStatsShow'] = "";
        }

        $navBar['navbarDevShow'] = 'hidden';
        $navBar['navbarDevUrl'] = '';

        $general = new General();
        /*
        $aq = new \soffit\ActiveQuery('service_data');
        $collections_arr1 = $aq->SERVICE_DATA->select(['id','name'])
                ->where(['tab','=','collections'])->orderBy('name')->exe();
        */
        
        $collections_arr = $general->getServiceData(tabs:['collections'], direction:'ASC');
        $navBar['collectionList'] = $this->getCollections($collections_arr['collections']);

        $this->navBar = $navBar;
        $this->varBlock['designApproveModels'] = $general->getDesignApproveModels();
		
		// Показать ремонты моделлерам
                if (User::permission('repairs')) {
            $this->varBlock['repairsToWork'] = $general->countRepairsToWork();
        }

        // Для ПДО показать все не завершенные ремонты
        if (User::permission('repairs') && User::getAccess() == 8) {
            $this->varBlock['repairsToWork'] = $general->countRepairsToShow();
        }

        // Показать модели в работу
		if ( User::permission('MA_modeller3D') )
        {
            if ( User::getAccess() === 10 )
            {
                $this->varBlock['models3DToWork'] = $general->countModels3DToWork(true);
                $this->varBlock['models3DInWork'] = $general->countModels3DInWork(true);
            } else {
                $this->varBlock['models3DToWork'] = $general->countModels3DToWork();
                $this->varBlock['models3DInWork'] = $general->countModels3DInWork();
            }
        }

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
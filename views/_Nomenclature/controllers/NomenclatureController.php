<?php
namespace Views\_Nomenclature\Controllers;

use Views\_Nomenclature\Models\{
    NomenclatureModel, GradingSystemModel, UserCodes, UsersModel
};
use Views\_Globals\Controllers\GeneralController;
use Views\_Globals\Models\User;
use Views\vendor\core\Crypt;
use Views\vendor\libs\classes\AppCodes;

class NomenclatureController extends GeneralController
{
    protected $view = 'nomenclature';
    protected $tab = 1;

    public $title ="Номенклатура::ХЮФ";


    /**
     * @throws \Exception
     */
    public function beforeAction()
    {
        $request = $this->request;

        if ($request->isAjax())
        {
            try
            {
                /** NOMENCLATURE **/
                if (trueIsset($request->post('val')) && trueIsset($request->post('tab')))
                {
                    $row_tab = $request->post('tab');
                    $row_id = (int)$request->post('id');
                    $dell = (int)$request->post('dell');
                    $row_value = $request->post('val');

                    if (trueIsset($dell)) $this->actionDell($row_id, $row_value, $row_tab);

                    if (trueIsset($row_id)) {
                        $this->edit($row_id, $row_tab, $row_value);
                    } else {
                        $this->add($row_value, $row_tab);
                    }
                }

                /** GRADING SYSTEM **/
                if (trueIsset($request->post('gsShow')) && trueIsset($request->post('showGSID')))
                {
                    echo json_encode( $this->actionShowGSPos( (int)$request->post('showGSID') ) );
                }
                if (trueIsset($request->post('gsEdit')) && trueIsset($request->post('editGS_ID')))
                {
                    echo json_encode( $this->actionEditGSPos() );
                }

                /** USERS **/
                if ( trueIsset($request->post('getPresets')) )
                    $this->actionGetPresets( $request->post('getPresets'));

                if (trueIsset($request->post('userShow')) && trueIsset($request->post('showUserID')))
                    exit(json_encode( $this->actionShowUserPos( $request->post('showUserID') ) ) );

                if ( trueIsset($request->post('userAddEdit')) )
                    $this->actionEditUserPos( $request->post('userAddEdit') );

                if ( trueIsset($request->post('userDell')) && trueIsset($request->post('userID')) && trueIsset($request->post('userMTProd')) )
                    echo json_encode( $this->actionDellUserPos( (int)$request->post('userID'), $request->post('userMTProd') ) );

            } catch (\Error | \Exception $e) {
                if ( _DEV_MODE_ )
                {
                    exit( json_encode([
                        'error'=>[
                            'message'=>$e->getMessage(),
                            'code'=>$e->getCode(),
                            'file'=>$e->getFile(),
                            'line'=>$e->getLine(),
                            'trace'=>$e->getTrace(),
                            'previous'=>$e->getPrevious(),
                        ]
                    ]) );
                } else {
                    exit( json_encode([
                        'error'=>[
                            'message'=>AppCodes::getMessage(AppCodes::SERVER_ERROR)['message'],
                            'code'=>$e->getCode(),
                        ],
                    ]) );
                }
            }
            exit;
        }

        if ($request->get('tab') === 'gs')
        {
            $this->view = 'grading_system';
            $this->tab = 5; // система оценок
        }
        if ($request->get('tab') === 'users')
        {
            $this->view = 'users';
            $this->tab = 6; // Users
        }
    }


    /**
     * @throws \Exception
     */
    public function action()
    {
        $compact = [];

        switch ($this->tab)
        {
            case 1:
                $compact = $this->actionNom();
                break;
            case 5:
                $compact = $this->actionGS();
                break;
            case 6:
                $compact = $this->actionUsers();
                break;
        }

        return $this->render($this->view, $compact);
    }

    /**
     * Номенклатура
     * @throws \Exception
     */
    protected function actionNom()
    {
        $nom = new NomenclatureModel();

        $nom->permittedFields();

        $compact = $nom->getData();


        $this->includePHPFile('nom_incl.php');
        $this->includeJSFile('nomenclature.js',['defer','timestamp']);

        return $compact;
    }

    /**
     * Система оценок
     * @throws \Exception
     *
     */
    protected function actionGS()
    {
        $gs = new GradingSystemModel();
        $data = $gs->getData();

        $this->includeJSFile('gs.js',['defer','timestamp']);
        $this->includePHPFile('gsEditModal.php');

        return compact('data');
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function actionUsers() : array
    {
        $usersModel = new UsersModel();

        $users = $usersModel->sortUsersLocations();
        $workingCentersDB = $usersModel->getWorkingCentersDB();

        $workingCentersDBJS = json_encode($workingCentersDB,JSON_UNESCAPED_UNICODE);
        $js = <<<JS
         let workingCentersDB = $workingCentersDBJS;
JS;
        $this->includeJS($js);

        $allPermissions = [];
        if ( User::getAccess() === 1 ) $allPermissions = $usersModel->getAllPermissions();
        $this->includeJSFile('users.js', ['defer','timestamp'] );
        $this->includePHPFile('userEditModal.php', compact(['workingCentersDB','allPermissions']) );

        return compact('users', 'workingCentersDB');
    }

    /**
     * @param $row_id
     * @param $row_val
     * @param $row_tab
     * @throws \Exception
     */
    protected function actionDell($row_id, $row_val, $row_tab)
    {
        $nom = new NomenclatureModel();
        $arr = $nom->dell($row_id, $row_val, $row_tab);

        echo json_encode($arr);
        exit;
    }

    /**
     * @param $row_id
     * @param $row_tab
     * @param $row_value
     * @throws \Exception
     */
    protected function edit($row_id, $row_tab, $row_value)
    {
        $nom = new NomenclatureModel();
        $arr = $nom->edit($row_id, $row_tab, $row_value);

        echo json_encode($arr);
        exit;
    }

    /**
     * @param $row_value
     * @param $row_tab
     * @throws \Exception
     */
    protected function add($row_value, $row_tab)
    {
        $nom = new NomenclatureModel();
        $arr = $nom->add($row_value, $row_tab);

        echo json_encode($arr);
        exit;
    }

    /**
     * Выдача данных оценки
     * @param int $id
     * @return array
     * @throws \Exception
     */
    protected function actionShowGSPos( int $id ) : array
    {
        $gs = new GradingSystemModel();

        return $gs->getGSRowByID($id);
    }

    /**
     * Редактирование оценки
     * @return array
     * @throws \Exception
     */
    protected function actionEditGSPos() : array
    {
        $request = $this->request;
        $gs = new GradingSystemModel();

        $description = $request->post('description');
        $basePercent = (float)$request->post('basePercent');
        $examples = $request->post('examples');
        $editGS_ID = (int)$request->post('editGS_ID');

        return $gs->editGSPos( $description, $basePercent, $examples, $editGS_ID );
    }

    /**
     * @param int $getP
     * @throws \Exception
     */
    protected function actionGetPresets( int $getP )
    {
        if ( $getP !== 1 )
            exit(json_encode(['error'=>AppCodes::getMessage(AppCodes::SERVER_ERROR)]));

        $usersModel = new UsersModel();
        $userRes['presets'] = $usersModel->userRulesPreset();
        $delKeys1 = ['mt_admin','mt_design'];
        $delKeys2 = ['mt_admin','mt_design','mt_tech'];

        $uAcc = User::getAccess();
        if ($uAcc === 11 || $uAcc === 122 )
        {
            foreach ( $userRes['presets'] as $key => &$val )
            {
                unset($val['permissions']);
                if ( in_array($key, ($uAcc === 11) ? $delKeys1 : $delKeys2) )
                    unset($userRes['presets'][$key]);
            }
        }

        exit(json_encode($userRes));
    }

    /**
     * Редактирование юзера
     * @param string $showUserID
     * @return array
     * @throws \Exception
     */
    protected function actionShowUserPos( string $showUserID ) : array
    {
        $usersModel = new UsersModel();
        $sortedUsers = $usersModel->sortUsersLocations();

        $showUserID = Crypt::strDecode($showUserID);

        $userRes = [];
        foreach ( $sortedUsers as &$user )
        {
            if ( $showUserID === $user['id'] )
            {
                $userRes = $user;
                break;
            }
        }

        if ( empty($userRes) )
            exit(json_encode(['error'=>UserCodes::getMessage(UserCodes::NO_SUCH_USER), 'code'=>UserCodes::NO_SUCH_USER]));


        // Пока только админ может раздавать разрешения, остальные только пресетами
        if ( User::getAccess() === 1 )
            $usersModel->addUserPermissions( $userRes );

        $userRes['presets'] = $usersModel->userRulesPreset();
        $delKeys1 = ['mt_admin','mt_design'];
        $delKeys2 = ['mt_admin','mt_design','mt_modell','mt_tech'];
        if ( User::getAccess() === 11 || User::getAccess() === 122 )
        {
            $i = 0;
            foreach ( $userRes['presets'] as $key => &$val )
            {
                unset($val['permissions']);
                if ( in_array($key, (User::getAccess() === 11) ? $delKeys1 : $delKeys2) )
                {
                    $userRes['presets'][$i++] = $val;
                    unset($userRes['presets'][$key]);
                }
            }
        }

        $userRes['id'] = Crypt::strEncode($userRes['id']);
        return $userRes;
    }

    /**
     * Редактирование юзера
     * @param int $addEdit
     * @throws \Exception
     */
    protected function actionEditUserPos( int $addEdit )
    {
        $request = $this->request;

        $usersModel = new UsersModel();
        $addEdit = $addEdit === 1 ? true : false;
        $usersModel->editUserData($request->post, $addEdit);
    }

    /**
     * Редактирование юзера
     * @param int $userID
     * @param string $userMTProd
     * @return array
     * @throws \Exception
     */
    protected function actionDellUserPos( int $userID, string $userMTProd ) : array
    {
        $usersModel = new UsersModel();
        return $usersModel->dellUserData( $userID, $userMTProd );
    }

}
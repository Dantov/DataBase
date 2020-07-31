<?php

namespace Views\_Nomenclature\Models;

use Views\_AddEdit\Models\Handler;
use Views\_Globals\Models\User;
use Views\vendor\core\Crypt;
use Views\vendor\libs\classes\AppCodes;

class UsersModel extends Handler
{


    /**
     * UsersModel constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct();
        $this->connectDBLite();
    }

    /**
     * @throws \Exception
     */
    public function getAllPermissions()
    {
        return $this->findAsArray("SELECT id,name,description FROM permissions");
    }


    /**
     * @throws \Exception
     */
    public function sortUsersLocations()
    {
        $workingCentersDB = $this->getWorkingCentersDB();
        $users = $this->getUsers(true);
        $presets = $this->userRulesPreset();

        foreach ( $users as &$user )
        {
            $uLocations = explode(',', $user['location']);
            $uLocationsNames = [];
            foreach ($workingCentersDB as $workingCenters)
            {
                foreach ($workingCenters as $workingCenter)
                {
                    if ( in_array( $workingCenter['id'], $uLocations) )
                    {
                        $uLocationsNames[$workingCenter['name']] .= $workingCenter['descr'] . ", ";
                    }
                }
            }
            $user['locNames'] = $uLocationsNames;

            foreach ($presets as $preset)
            {
                if ( (int)$user['access'] === $preset['id'] )
                {
                    $user['role']['name'] = $preset['name'];
                    $user['role']['description'] = $preset['description'];
                }
            }
        }

        return $users;
    }

    /**
     * @param array $user
     * @throws \Exception
     */
    public function addUserPermissions(array &$user ) : void
    {
        $allPermissions = $this->getAllPermissions();
        $userPermissions = $this->findAsArray("SELECT permission_id as id FROM user_permissions WHERE user_id='{$user['id']}'");

        $user['permissions'] = [];
        foreach ($userPermissions as $permID)
        {
            foreach ($allPermissions as $permission)
            {
                if ( $permission['id'] == $permID['id'] ) $user['permissions'][$permID['id']] = $permission;
            }
        }
    }

    /**
     * @param array $data
     * Пост данные
     * @param bool $add
     * Флаг добавления нового юзера
     *
     * @return void
     * @throws \Exception
     */
    public function editUserData( array $data, bool $add = false ) : void
    {
        /** Проверка доступа для редактирования данных юзера **/
        if ( !User::permission('nomUsers_edit') )
            exit(json_encode(['error' => AppCodes::getMessage(AppCodes::PERMISSION_DENIED)]));

        /** А есть ли такой юзер? **/
        $userID = null;
        if ( !$add )
        {
            $userID = (int)Crypt::strDecode($data['editUser_ID']);
            if ( !$this->checkID( $userID,'users' ) )
                exit(json_encode(['error' => UserCodes::getMessage(UserCodes::NO_SUCH_USER)]));
        }

        /** Берем старый доступ для проверки, может ли текущий юзер изменять его данные **/
        $editingUserAccess = (int)$this->findOne("SELECT access FROM users WHERE id='$userID'",'access');
        $accEditArr = [ 11 => [1], 122 => [11,1,2] ];
        if ( array_key_exists(User::getAccess(), $accEditArr) )
            if ( in_array($editingUserAccess, $accEditArr[User::getAccess()]) )
                exit(json_encode(['error' => AppCodes::getMessage(AppCodes::PERMISSION_DENIED)]));

        /** Берем Имя Фамилию отчество **/
        $userFirstName  = mysqli_escape_string($this->connection, htmlentities( trim($data['userFirstName']  ),  ENT_QUOTES) );
        if ( empty($userFirstName) )
            exit(json_encode(['error' => UserCodes::getMessage(UserCodes::FIRST_NAME_EMPTY)]));

        $userSecondName = mysqli_escape_string($this->connection, htmlentities( trim($data['userSecondName'] ), ENT_QUOTES) );
        $userThirdName  = mysqli_escape_string($this->connection, htmlentities( trim($data['userThirdName']  ),  ENT_QUOTES) );

        /** Создаем ФИО **/
        $fio = $userFirstName;
        if ( !empty($userSecondName) )
        {
            $arrChars = preg_split('//u',$userSecondName, -1, PREG_SPLIT_NO_EMPTY);
            $fio .= " " . mb_strtoupper($arrChars[0],'UTF-8') . ".";
            if ( !empty($userThirdName) )
            {
                $arrChars = preg_split('//u',$userThirdName, -1, PREG_SPLIT_NO_EMPTY);
                $fio .=  mb_strtoupper($arrChars[0],'UTF-8') . ".";
            }
        }
        $fullFio = $userFirstName . " " . $userSecondName . " " . $userThirdName;

        /** Берем логин пароль**/
        $userLog = mysqli_escape_string($this->connection, htmlentities( trim($data['userLog']  ),  ENT_QUOTES) );
        $userPass = mysqli_escape_string($this->connection, htmlentities( trim($data['userPass']  ),  ENT_QUOTES) );
        if ( !empty($userLog) )
        {
            $uLogs = $this->findAsArray("SELECT id,login FROM users");
            foreach ( $uLogs as $uLog )
                if ( ($uLog['login'] == $userLog) && ($userID !== (int)$uLog['id']) )
                    exit(json_encode(['error' => UserCodes::getMessage(UserCodes::LOGIN_MATCH)]));

        } else {
            exit(json_encode(['error' => UserCodes::getMessage(UserCodes::LOGIN_EMPTY)]));
        }
        if ( empty($userPass) )
            exit(json_encode(['error' => UserCodes::getMessage(UserCodes::PASSWORD_EMPTY)]));

        /** Раб. участки **/
        $wcList = array_unique($data['wcList']??[]);
        foreach ( $wcList as $wcID )
        {
            $found = false;
            foreach ( $this->getWorkingCentersSorted() as $wc )
            {
                if ( (int)$wcID === (int)$wc['id'] )
                {
                    $found = true;
                    break;
                }
            }
            if ( !$found )
                exit(json_encode(['error' => UserCodes::getMessage(UserCodes::WORKING_CENTER_NOT_FOUND)]));
        }
        $location = implode(',', $wcList);

        /** Пресет **/
        $preset = $this->userRulesPreset( $data['userMTProd'] );

        /** массив вставки ЮЗЕРА**/
        $userRow = [
            [
                'id'=>$userID ? $userID : '',
                'login'=>$userLog,
                'pass'=>$userPass,
                'fio'=>$fio,
                'fullFio'=>$fullFio,
                'location'=>$location,
                'access'=>$preset['id'],
            ]
        ];

        $result = [
            'userInsUpd' => false,
            'PermInsUpd' => false,
        ];
        // Вернет -1 если ничего не сделано
        $lastID = $this->insertUpdateRows($userRow,'users');
            if ( $lastID !== -1 )
                $result['userInsUpd'] = true;

        /** Вносим разрешения user_permissions, только если пресет изменился**/
        if ( $editingUserAccess != $preset['id'] )
        {
            $permArr = [
                'id' => '',
                'user_id' => !$add ? $userID : $lastID,
                'permission_id' => '',
                'date' => date('Y-m-d'),
            ];
            $permissionsPreset = $preset['permissions'];
            foreach ($permissionsPreset as $key => $value)
            {
               $permArr['permission_id'] = $value;
               $permissionsPreset[$key] = $permArr;
            }

            // удалим старые разрешения, если редактируем
            if ( !$add )
                //$this->baseSql("DELETE FROM user_permissions WHERE user_id='$userID'");
                $this->deleteFromTable("user_permissions",'user_id',$userID);
            if ( $this->insertUpdateRows($permissionsPreset,'user_permissions') !== -1 )
                $result['PermInsUpd'] = true;
        } elseif( trueIsset($data['deletedPermlist']) ) {

            /** Если пришли разрешения на удаление **/
            //$deletedPerms = (int)Crypt::strDecode($data['deletedPermlist']);
            $deletedPerms = $data['deletedPermlist'];

            $ids = '';
            foreach ( $deletedPerms as $id )
                if ( !empty($id) ) $ids .= $id . ',';

            if (!empty($ids))
            {
                $ids = '(' . rtrim($ids,',') . ')';
                if ( $this->baseSql( "DELETE FROM user_permissions WHERE user_id='$userID' AND permission_id IN $ids" ) )
                    $result['PermInsUpd'] = true;
            }
        }

        /** Отдельно Вносим разрешения user_permissions, если они были добавлены**/
        if ( trueIsset($data['addPermList']) )
        {
            $addPermList = $data['addPermList'];

            $permArr = [
                'id' => '',
                'user_id' => !$add ? $userID : $lastID,
                'permission_id' => '',
                'date' => date('Y-m-d'),
            ];
            $addingPermissions = [];
            $allPerms = $this->getAllPermissions();
            foreach ( $addPermList as $addingPermID )
            {
                foreach ( $allPerms as $permission )
                {
                    if ( (int)$permission['id'] === (int)$addingPermID )
                    {
                        $permArr['permission_id'] = (int)$addingPermID;
                        $addingPermissions[] = $permArr;
                    }
                }
            }
            //debug($addingPermissions,'$addingPermissions',1,1);
            if ( $this->insertUpdateRows($addingPermissions,'user_permissions') !== -1 )
                $result['PermInsUpd'] = true;
        }


        /** RESULTS **/
        if ( $add && $result['userInsUpd'] && $result['PermInsUpd'] )
            exit(json_encode(['success' => UserCodes::getMessage(UserCodes::USER_PERMISSIONS_ADD_SUCCESS)]));

        if ( !$add && $result['userInsUpd'] && $result['PermInsUpd'] )
            exit(json_encode(['success' => UserCodes::getMessage(UserCodes::USER_PERMISSIONS_EDIT_SUCCESS)]));

        if ( !$add && !$result['userInsUpd'] && $result['PermInsUpd'] )
            exit(json_encode(['success' => UserCodes::getMessage(UserCodes::PERMISSIONS_EDIT_SUCCESS)]));

        if ( !$add && $result['userInsUpd'] && !$result['PermInsUpd'] )
            exit(json_encode(['success' => UserCodes::getMessage(UserCodes::USER_EDIT_SUCCESS)]));

        if ( !$add && !$result['userInsUpd'] && !$result['PermInsUpd'] )
            exit(json_encode(['success' => UserCodes::getMessage(UserCodes::NOTHING_DONE)]));
    }

    /**
     * @param string|int $preset
     * @return array|int
     * @throws \Exception
     */
    public function userRulesPreset( $preset='' ) : array
    {
        $allPerm = $this->findAsArray("SELECT id FROM permissions");
        foreach ($allPerm as $key => $value) $allPerm[$key] = $value['id'];

        $rulesPreset = [
            'mt_admin' => [
                'id'=> 1,
                'name'=> 'Админ',
                'description'=> 'Доступ ко всему.',
                'permissions' => $allPerm,
                ],
            'mt_moder' => [
                'id'=> 122,
                'name'=> 'Модератор',
                'description'=> 'Для редактирования пользователей.',
                'permissions' =>[35,36,38,40,41,42],
            ],
            'mt_design' => [
                'id'=> 11,
                'name'=> 'Дизайнер юв. изделий',
                'description'=> 'Создание/ред. 3д моделей. Всем поля редактирования 3д моделей. Редактирование пользователей. Доступ к кошельку.',
                'permissions' =>[1,2,3,4,5,6,7,8,9,10,13,15,16,17,18,19,20,21,22,23,24,25,27,28,29,31,32,33,35,36,37,38,40,41,42,45,46,47,48,49,50,52],
            ],
            'mt_modell' => [
                'id'=> 2,
                'name'=> '3D модельер юв. изделий',
                'description'=> 'Создание/ред. 3д моделей. Доступ ко всем полям редактирования своей 3д модели. Доступ к кошельку.',
                'permissions' =>[1,2,3,4,5,6,8,9,10,13,15,16,17,18,19,20,21,22,23,24,25,27,28,29,31,32,33,35,36,37,38,52,53],
            ],
            'mt_modellHM' => [
                'id'=> 5,
                'name'=> 'Модельер-доработчик',
                'description'=> 'Доработка и ремонт мастер моделей. Изменение статусов, загрузка картинок. Доступ к кошельку.',
                'permissions' =>[7,12,15,19,20,21,23,24,26,27,28,32,33,35,38,45,51],
            ],
            'mt_oper' => [
                'id'=> 3,
                'name'=> 'Оператор 3D принтера',
                'description'=> 'Изменение статусов. Доступ к кошельку.',
                'permissions' =>[11,24,28,32,33,35,36,38,45,50,49],
            ],
            'mt_prod' => [
                'id'=> 4,
                'name'=> 'Производство юв. изделий',
                'description'=> 'Изменение статусов изделий, вставок',
                'permissions' =>[24,28,45],
            ],
            'mt_tech' => [
                'id'=> 7,
                'name'=> 'Технолог юв. изделий',
                'description'=> 'Изменение статусов изделий. Доступ к кошельку.',
                'permissions' =>[28,32,33,36,38,45,48],
            ],
            'mt_pdo' => [
                'id'=> 8,
                'name'=> 'ПДО',
                'description'=> 'Изменение статусов, вставок, оценка артикула',
                'permissions' => [3,7,10,13,21,22,23,24,27,28,35,36,38,45],
            ],
            'mt_guest' => [
                'id'=> 0,
                'name'=> 'Гость',
                'description'=> 'Просмотр базы',
                'permissions' => [],
            ],
        ];

        if ( is_string($preset) && !empty($preset) )
            if ( array_key_exists($preset, $rulesPreset) )
                return $rulesPreset[$preset];

        if ( is_int($preset) )
            foreach ( $rulesPreset as $rulePreset )
                if ( in_array($preset, $rulePreset) )
                    return $rulePreset['permissions'];

        return $rulesPreset;
    }

    /**
     * @param int $userID
     * @param string $userMTProd
     * @return array
     * @throws \Exception
     */
    public function dellUserData( int $userID, string $userMTProd ) : array
    {
        if ( !User::permission('nomUsers_edit') ) return ['error' => self::PERMISSION_DENIED ];

        $access_old = (int)$this->findOne("SELECT access FROM users WHERE id='$userID'")['access'];
        if ( $access_old === 1 && (User::getAccess() !== 1) ) return ['error' => self::PERMISSION_DENIED ];

        if ( !$this->checkID( $userID,'users' ) ) return ['error' => self::NO_SUCH_USER ];
        
        $this->baseSql("DELETE FROM user_permissions WHERE user_id='$userID'");
        if ($this->baseSql("DELETE FROM users WHERE id='$userID'") ) return ['success' => $userID];

        return ['error' => self::INSERT_UPDATE_FAIL ];
    }
}
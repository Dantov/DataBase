<?php
/**
 * Date: 05.01.2021
 * Time: 22:22
 */

namespace Views\vendor\libs\classes;

use Views\vendor\core\db\Database;

class Validator
{

    protected $connection;

    protected static $lastError = '';
    protected static $errors = [];

    protected $fieldRules = [];

    protected $badChars = ['\'', '.', ',', '\\', '/', '"', '%','&','?','*','|','^', '<', '>', ':',';','`'];

    /**
     * Table name bad chars
     * @var array
     */
    private $tnbc = ['\'','"', ',', '\\','/', '|', '<', '>','+','-','?','&'];

    /**
     * Validator constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->connection = Database::instance()->getConnection();
    }

    public function getAllErrors()
    {
        return self::$errors;
    }
    public function getLastError()
    {
        return self::$lastError;
    }
    /**
     * Удаляем всю инф. об ошибках
     * что бы валидировать новые поля
     * @return bool
     */
    public function reset() : bool
    {
        self::$lastError = '';
        self::$errors = [];
        if ( empty(self::$lastError) && empty(self::$errors) )
            return true;

        return false;
    }
    /**
     * Описаны возможные правила
     * @param string $ruleName
     * @param string $field
     * @param string $value
     * @return array
     */
    protected function rulesErrorText(  string $ruleName, string $field, string $value )
    {
        $rules = [
            'min' => "Значение поля '" . $field . "' не должно быть меньше " . $value . ". ",
            'max' => "Значение поля '" . $field . "' не должно быть больше " . $value . ". ",
            'minLength' => "Кол-во символов в поле '" . $field . "' не должно быть меньше " . $value . ". ",
            'maxLength' => "Кол-во символов в поле '" . $field . "' не должно быть больше " . $value . ". ",
            'required' => "Поле '" . $field . "' обязательно к заполнению. ",
            'readonly' => "Поле '" . $field . "' нельзя изменять. ",
            'int' => "Значение поля '" . $field . "' должно быть целое число. ",
            'unsigned' => "Значение поля '" . $field . "' не может быть отрицательным. ",
            'double' => "Значение поля '" . $field . "' должно быть дробное число. ",
            'forbiddenChars' => "Значение поля '" . $field . "' содержит не допустимые символы. ",
            'acceptedChars' => "Значение поля '" . $field . "' содержит не . ",
        ];

        if ( array_key_exists($ruleName, $rules) )
            return $rules[$ruleName];

        return $rules;
    }

    /**
     *
     * @param string $field
     * @return array
     * @throws \Exception
     */
    protected function fieldRules( string $field = '' )
    {
        if ( !empty($this->fieldRules) )
        {
            if ( $field )
            {
                if ( array_key_exists($field,$this->fieldRules) )
                    return $this->fieldRules[$field];
            } else {
                throw new \Exception('Field  "' . $field . '" not found in rules!',213);
            }
        }

        $this->fieldRules = [
            'number_3d'=> [
                'name'=> '№ 3Д',
                'rules' => [
                    'required' => true,
                    'minLength' => 7,
                    'maxLength' => 24,
                    'forbiddenChars' => true,
                ],
            ],
            'vendor_code' => [
                'name'=> 'Артикул',
                'rules' => [
                    'maxLength' => 64,
                ],
            ],
            'author' => [
                'name'=> 'Автор',
                'rules' => [
                    'required' => true,
                    'maxLength' => 64,
                ],
            ],
            'modeller3d' => [
                'name'=> '3д-модельер',
                'rules' => [
                    'required' => true,
                    'maxLength' => 64,
                ],
            ],
            'jewelerName' => [
                'name'=> 'Модельер-доработчик',
                'rules' => [
                    'maxLength' => 64,
                ],
            ],
            'model_type' => [
                'name'=> 'Тип модели',
                'rules' => [
                    'required' => true,
                    'maxLength' => 64,
                ],
            ],
            'size_range' => [
                'name'=> 'Размерный ряд',
                'rules' => [
                    'maxLength' => 128,
                ],
            ],
            'model_weight' => [
                'name'=> 'Вес',
                'rules' => [
                    'required' => true,
                    'double' => true,
                    'min' => 0.05,
                    'max' => 100,
                ],
            ],
            'print_cost' => [
                'name'=> 'Стоимость печати',
                'rules' => [
                    'min' => 0,
                    'max' => 1000,
                ],
            ],
            'model_cost' => [
                'name'=> 'Стоимость модели',
                'rules' => [
                    'min' => 0,
                    'max' => 1000,
                ],
            ],
            'description' => [
                'name'=> 'Описание',
                'rules' => [
                    'maxLength' => 2048,
                ],
            ],
            'status' => [
                'name'=> 'Статус',
                'rules' => [
                    'min' => 1,
                    'max' => 106,
                ],
            ],

            // Массивы
            'labels' => [
                'name'=> 'Метки',
                'rules' => [
                    'max' => 9,
                ],
                'fieldsRules' => [
                    'name' => [
                        'name'=> 'Метки',
                        'rules' => [
                            'maxLength' => 128,
                            'minLength' => 3,
                        ],
                    ],
                ],
            ],
            'collections' => [
                'name'=> 'Коллекции',
                'rules' => [
                    'required' => true,
                    'min' => 1,
                    'max' => 10,
                ],
                'fieldsRules' => [
                    'name' => [
                        'name'=> 'Имя коллекции',
                        'rules' => [
                            'required' => true,
                            'maxLength' => 128,
                            'minLength' => 3,
                        ],
                    ],
                ],
            ],
            'mats' => [
                'name'=> 'Материалы',
                'rules' => [
                    'required' => true,
                    'max' => 20,
                ],
                'fieldsRules' => [
                    'part' => [
                        'name'=> 'Материалы: деталь',
                        'rules' => [
                            'maxLength' => 64,
                        ],
                    ],
                    'type' => [
                        'name'=> 'Материалы: Металл',
                        'rules' => [
                            'required' => true,
                            'maxLength' => 64,
                        ],
                    ],
                    'probe' => [
                        'name'=> 'Материалы: Проба',
                        'rules' => [
                            'required' => true,
                            'maxLength' => 64,
                        ],
                    ],
                    'metalColor' => [
                        'name'=> 'Материалы: Цвет металла',
                        'rules' => [
                            'required' => true,
                            'maxLength' => 64,
                        ],
                    ],
                    'covering' => [
                        'name'=> 'Материалы: Покрытие',
                        'rules' => [
                            'maxLength' => 64,
                        ],
                    ],
                    'area' => [
                        'name'=> 'Материалы: Место',
                        'rules' => [
                            'maxLength' => 64,
                        ],
                    ],
                    'covColor' => [
                        'name'=> 'Материалы: Цвет покрытия',
                        'rules' => [
                            'maxLength' => 64,
                        ],
                    ],
                    'handling' => [
                        'name'=> 'Материалы: Обработка',
                        'rules' => [
                            'maxLength' => 64,
                        ],
                    ],
                    'count' => [
                        'name'=> 'Материалы: К-во',
                        'rules' => [
                            'required' => true,
                            'min' => 1,
                            'max' => 128,
                        ],
                    ],
                ],
            ],
            'gems' => [
                'name'=> 'Камни',
                'rules' => [
                    'max' => 20,
                ],
                'fieldsRules' => [
                    'gems_names' => [
                        'name'=> 'Камни: Сырьё',
                        'rules' => [
                            'required' => true,
                            'maxLength' => 64,
                        ],
                    ],
                    'gems_cut' => [
                        'name'=> 'Камни: Огранка',
                        'rules' => [
                            'required' => true,
                            'maxLength' => 64,
                        ],
                    ],
                    'gems_sizes' => [
                        'name'=> 'Камни: Размеры',
                        'rules' => [
                            'required' => true,
                            'maxLength' => 24,
                        ],
                    ],
                    'value' => [
                        'name'=> 'Камни: Кол-во',
                        'rules' => [
                            'required' => true,
                            'int' => true,
                            'unsigned' => true,
                            'min' => 1,
                            'max' => 1000,
                        ],
                    ],
                    'gems_color' => [
                        'name'=> 'Камни: Цвет',
                        'rules' => [
                            'required' => true,
                            'maxLength' => 128,
                        ],
                    ],
                ],
            ],
            'vc_links' => [
                'name'=> 'Ссылки',
                'rules' => [
                    'max' => 20,
                ],
                'fieldsRules' => [
                    'vc_names' => [
                        'name'=> 'Ссылки: Название',
                        'rules' => [
                            'required' => true,
                            'maxLength' => 64,
                        ],
                    ],
                    'vc_3dnum' => [
                        'name'=> 'Ссылки: Артикул/Номер 3D',
                        'rules' => [
                            'required' => true,
                            'maxLength' => 64,
                        ],
                    ],
                    'descript' => [
                        'name'=> 'Ссылки: Описание',
                        'rules' => [
                            'maxLength' => 128,
                        ],
                    ],
                ],
            ],
        ];

        if ( $field )
        {
            if ( array_key_exists($field,$this->fieldRules) )
                return $this->fieldRules[$field];
        } else {
            throw new \Exception('Field  "' . $field . '" not found in rules!',213);
        }

        return $this->fieldRules;
    }

    /**
     * Проверка мультистрок
     * @param string $fieldNameGlobal
     * @param array $batchRows
     * @throws \Exception
     */
    public function validateFields( string $fieldNameGlobal, &$batchRows ) : void
    {
        if (!$batchRows) return;

        //Проверка кол-ва
        $this->validateField( $fieldNameGlobal, count($batchRows) );

        $rulesGlobal = $this->fieldRules($fieldNameGlobal)['fieldsRules'];

        foreach ( $batchRows as $fieldNameLocal => &$value )
        {
            if ( $fieldNameLocal === 'id' ) continue;
            if ( array_key_exists($fieldNameLocal, $rulesGlobal) )
            {
                $localFieldRules = $rulesGlobal[$fieldNameLocal];
            } else {
               throw new \Exception("No rule for this field", 655);
            }

//            debugAjax($rulesGlobal, 'validate: $rulesGlobal');
//            debugAjax($fieldNameLocal, '$fieldNameLocal');
//            debugAjax($localFieldRules, 'validate: $localFieldRules', END_AB);

            if ( is_array($value) )
            {
                foreach ( $value as &$v )
                {
                    if ( (int)$v === -1 ) continue;
                    $v = $this->validateField( $fieldNameLocal, $v, $localFieldRules );
                }

            } else {
                $value = $this->validateField( $fieldNameLocal, $value, $localFieldRules );
            }

        }

    }

    protected function baseValidate( string $str ) : string
    {
        $str = trim($str);
        $str = strip_tags($str);
        return mysqli_real_escape_string($this->connection, $str);
    }

    /**
     * @param string $fieldName
     * @param string $fieldValue
     * @param array $rules Список правил
     * @return string
     * @throws \Exception
     */
    public function validateField( string $fieldName, string $fieldValue, array $rules = [] )
    {
        $fieldValue = $this->baseValidate($fieldValue);

        if ( empty($rules) )
            $rules = $this->fieldRules($fieldName);

        $this->validate($fieldValue, $rules);

        return $fieldValue;
    }

    protected function validate( $fieldValue, array $rules ) : bool
    {
//        debugAjax($fieldValue, 'validate: $fieldValue');
//        debugAjax($rules, 'validate: $rules');

//        if ( $name == 'gems' )
//            debugAjax($rules['rules'], 'validate: $rules[rules]', END_AB);

        foreach ( $rules['rules']??[] as $rule => $value )
        {
            switch ($rule)
            {
                case "required":
                    {
                        if ( $value ) // Установлено в true
                            if ( empty($fieldValue) )
                                $this->setErrorText($rule, $rules['name'], $value);
                    } break;
                case "double":
                    {
                        $fieldValue = (double)$fieldValue;
                    } break;
                case "int":
                    {
                        $fieldValue = (int)$fieldValue;
                    } break;
                case "unsigned":
                    {
                        if ( $fieldValue < 0 )
                            $this->setErrorText($rule, $rules['name'], $value);
                    } break;
                case "min":
                    {
                        if ( $fieldValue < $value )
                            $this->setErrorText($rule, $rules['name'], $value);
                    } break;
                case "max":
                    {
                        if ( $fieldValue > $value )
                            $this->setErrorText($rule, $rules['name'], $value);
                    } break;
                case "minLength":
                    {
                        if ( mb_strlen($fieldValue) < $value )
                            $this->setErrorText($rule, $rules['name'], $value);
                    } break;
                case "maxLength":
                    {
                        if ( mb_strlen($fieldValue) > $value )
                            $this->setErrorText($rule, $rules['name'], $value);
                    } break;
                case "forbiddenChars":
                    {
                        // проверить каждый символ поля
                        $symbols = $arrChars = preg_split('//u',$fieldValue,-1,PREG_SPLIT_NO_EMPTY);
                        foreach ( $symbols as $symbol )
                        {
                            if ( in_array($symbol, $this->badChars) )
                            {
                                $this->setErrorText($rule, $rules['name'], $value);
                                break;
                            }
                        }
                    } break;
            }
        }

        return true;
    }

    private function setErrorText( string $rule, string $ruleName, $value )
    {
        self::$lastError = $this->rulesErrorText($rule, $ruleName, $value);
        self::$errors[] = self::$lastError;
    }


    public function validateTableName( string $tName ) : bool
    {
        $tName = $this->baseValidate($tName);

        // проверить каждый символ поля
        $symbols = preg_split('//u',$tName,-1,PREG_SPLIT_NO_EMPTY);
        foreach ( $symbols as $symbol )
        {
            if ( in_array($symbol, $this->tnbc) )
                return false;
        }

        return true;
    }

}
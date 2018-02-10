<?php

namespace mygroupdev\user\models\form;

use mygroupdev\user\Finder;
use mygroupdev\user\helpers\Password;
use mygroupdev\user\models\User;
use mygroupdev\user\traits\ModuleTrait;

use yii\helpers\Html;
use yii\helpers\ArrayHelper;

/**
 * Class SignInForm
 * @package mygroupdev\user\models\forms
 * @author Dmitry Dmytruk <my.group.dev@gmail.com>
 */
class SignInForm extends \yii\base\Model
{
    use ModuleTrait;

    /**
     * @var string email телефон или имя пользователя
     */
    public $login;

    /**
     * @var string Пароль користувача
     */
    public $password;

    /**
     * @var string Следует ли помнить пользователя
     */
    public $rememberMe = false;

    /**
     * @var \mygroupdev\user\models\User
     */
    protected $user;

    /**
     * @var Finder
     */
    protected $finder;

    /**
     * @param Finder $finder
     * @param array  $config
     */
    public function __construct(Finder $finder, $config = [])
    {
        $this->finder = $finder;
        parent::__construct($config);
    }

    /**
     * @brief Получает всех пользователей для создания раскрывающегося списка в режиме отладки.
     *
     * @return array
     */
    public static function loginList()
    {
        /** @var \mygroupdev\user\Module $module */
        $module = \Yii::$app->getModule('user');

        /** @var User $userModel */
        $userModel = $module->modelMap['User'];

        return ArrayHelper::map($userModel::find()->where(['blocked_at' => null])->all(), 'username', function ($user) {
            return sprintf('%s (%s)', Html::encode($user->username), Html::encode($user->email));
        });
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'login'      => \Yii::t('user', 'Login'),
            'password'   => \Yii::t('user', 'Password'),
            'rememberMe' => \Yii::t('user', 'Remember me next time'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            'loginTrim' => ['login', 'trim'],
            'requiredFields' => [['login'], 'required'],
            'confirmationValidate' => [
                'login',
                function ($attribute) {
                    if ($this->user !== null) {
                        $confirmationRequired = $this->module->enableConfirmation
                            && !$this->module->enableUnconfirmedLogin;
                        if ($confirmationRequired && !$this->user->getIsConfirmed()) {
                            $this->addError($attribute, \Yii::t('user', 'You need to confirm your email address'));
                        }
                        if ($this->user->getIsBlocked()) {
                            $this->addError($attribute, \Yii::t('user', 'Your account has been blocked'));
                        }
                    }
                }
            ],
            'rememberMe' => ['rememberMe', 'boolean'],
        ];
        if (!$this->module->debug) {
            $rules = array_merge($rules, [
                'requiredFields' => [['login', 'password'], 'required'],
                'passwordValidate' => [
                    'password',
                    function ($attribute) {
                        if ($this->user === null || !Password::validate($this->password, $this->user->password_hash)) {
                            $this->addError($attribute, \Yii::t('user', 'Invalid login or password'));
                        }
                    }
                ]
            ]);
        }
        return $rules;
    }

    /**
     * @brief Проверяет, совпадает ли хэш заданного пароля с сохраненным хешем в базе данных.
     * Он всегда будет успешным, если модуль находится в режиме DEBUG.
     * @param $attribute
     * @param $params
     */
    public function validatePassword($attribute, $params)
    {
        if ($this->user === null || !Password::validate($this->password, $this->user->password_hash))
            $this->addError($attribute, \Yii::t('user', 'Invalid login or passwordЦ'));
    }

    /**
     * @brief Проверяет форму и авторизирует пользователя.
     * @return bool
     */
    public function login()
    {
        if ($this->validate() && $this->user) {
            $isLogged = \Yii::$app->getUser()->login($this->user, $this->rememberMe ? $this->module->rememberFor : 0);
            if ($isLogged) {
                $this->user->updateAttributes(['last_sign_in' => time()]);
            }
            return $isLogged;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function formName()
    {
        return 'login-form';
    }

    /**
     * @inheritdoc
     */
    public function beforeValidate()
    {
        if (parent::beforeValidate()) {
            $this->user = $this->finder->findUserByUsernameOrEmail(trim($this->login));
            return true;
        } else {
            return false;
        }
    }
}
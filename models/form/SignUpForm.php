<?php

namespace mygroupdev\user\models\form;

use mygroupdev\user\models\User;
use mygroupdev\user\traits\ModuleTrait;

/**
 * Class SignUpForm
 * @package mygroupdev\user\models\forms
 * @author Dmitry Dmytruk <my.group.dev@gmail.com>
 */
class SignUpForm extends \yii\base\Model
{
    use ModuleTrait;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $username;

    /**
     * @var string
     */
    public $phone;

    /**
     * @var string
     */
    public $password;

    /**
     * @return array
     */
    public function rules()
    {
        $user = $this->module->modelMap['User'];

        return [
            // Правила имени пользователя
            'usernameTrim'     => ['username', 'trim'],
            'usernameLength'   => ['username', 'string', 'min' => 3, 'max' => 255],
            'usernamePattern'  => ['username', 'match', 'pattern' => $user::$usernameRegexp],
            'usernameRequired' => ['username', 'required'],
            'usernameUnique'   => [
                'username',
                'unique',
                'targetClass' => $user,
                'message' => \Yii::t('user', 'This username has already been taken')
            ],
            // Правила Эл. адреса
            'emailTrim'     => ['email', 'trim'],
            'emailRequired' => ['email', 'required'],
            'emailPattern'  => ['email', 'email'],
            'emailUnique'   => [
                'email',
                'unique',
                'targetClass' => $user,
                'message' => \Yii::t('user', 'This email address has already been taken')
            ],
            // Правила пароля
            'passwordRequired' => ['password', 'required', 'skipOnEmpty' => $this->module->enableGeneratingPassword],
            'passwordLength'   => ['password', 'string', 'min' => 6, 'max' => 72],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'email'    => \Yii::t('user', 'Email'),
            'username' => \Yii::t('user', 'Username'),
            'password' => \Yii::t('user', 'Password'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function formName()
    {
        return 'register-form';
    }

    /**
     * @brief Регистрирует новую учетную запись пользователя.
     * Если регистрация прошла успешно, она установит флеш-сообщение.
     *
     * @return bool
     */
    public function signUp()
    {
        if (!$this->validate()) {
            return false;
        }

        /** @var User $user */
        $user = \Yii::createObject($this->module->modelMap['User']);
        $user->scenario = $user::SCENARIO_SIGN_UP;
        $user->setAttributes($this->attributes);

        if (!$user->signUp()) {
            return false;
        }

        if($this->module->enableFlashMessages) {
            \Yii::$app->session->setFlash(
                'info',
                \Yii::t(
                    'user',
                    'Your account has been created and a message with further instructions has been sent to your email'
                )
            );
        }

        return true;
    }
}
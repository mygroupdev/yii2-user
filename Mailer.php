<?php

namespace mygroupdev\user;

use Yii;
use yii\base\Component;
use mygroupdev\user\models\User;
use mygroupdev\user\models\Token;
use mygroupdev\user\helpers\Password;

/**
 * Class Mailer
 * @package mygroupdev\user
 * @author Dmitry Dmytruk <my.group.dev@gmail.com>
 */
class Mailer extends Component
{
    /**
     * @var string
     */
    public $viewPath = '@mygroupdev/user/views/mail';

    /**
     * @var string|array Default: `Yii::$app->params['adminEmail']` OR `no-reply@example.com`
     */
    public $sender;

    /**
     * @var \yii\mail\BaseMailer Default: `Yii::$app->mailer`
     */
    public $mailerComponent;

    /**
     * @var string
     */
    protected $welcomeSubject;

    /**
     * @var string
     */
    protected $newPasswordSubject;

    /**
     * @var string
     */
    protected $confirmationSubject;

    /**
     * @var string
     */
    protected $reconfirmationSubject;

    /**
     * @var string
     */
    protected $recoverySubject;

    /**
     * @var \mygroupdev\user\Module
     */
    protected $module;

    /**
     * @return string
     */

    /**
     * @return string
     */
    public function getWelcomeSubject()
    {
        if ($this->welcomeSubject == null) {
            $this->setWelcomeSubject(Yii::t('user', 'Welcome to {0}', Yii::$app->name));
        }
        return $this->welcomeSubject;
    }

    /**
     * @param string $welcomeSubject
     */
    public function setWelcomeSubject($welcomeSubject)
    {
        $this->welcomeSubject = $welcomeSubject;
    }

    /**
     * @return string
     */
    public function getNewPasswordSubject()
    {
        if ($this->newPasswordSubject == null) {
            $this->setNewPasswordSubject(Yii::t('user', 'Your password on {0} has been changed', Yii::$app->name));
        }
        return $this->newPasswordSubject;
    }

    /**
     * @param string $newPasswordSubject
     */
    public function setNewPasswordSubject($newPasswordSubject)
    {
        $this->newPasswordSubject = $newPasswordSubject;
    }

    /**
     * @return string
     */
    public function getConfirmationSubject()
    {
        if ($this->confirmationSubject == null) {
            $this->setConfirmationSubject(Yii::t('user', 'Confirm account on {0}', Yii::$app->name));
        }
        return $this->confirmationSubject;
    }

    /**
     * @param string $confirmationSubject
     */
    public function setConfirmationSubject($confirmationSubject)
    {
        $this->confirmationSubject = $confirmationSubject;
    }

    /**
     * @return string
     */
    public function getReconfirmationSubject()
    {
        if ($this->reconfirmationSubject == null) {
            $this->setReconfirmationSubject(Yii::t('user', 'Confirm email change on {0}', Yii::$app->name));
        }
        return $this->reconfirmationSubject;
    }

    /**
     * @param string $reconfirmationSubject
     */
    public function setReconfirmationSubject($reconfirmationSubject)
    {
        $this->reconfirmationSubject = $reconfirmationSubject;
    }

    /**
     * @return string
     */
    public function getRecoverySubject()
    {
        if ($this->recoverySubject == null) {
            $this->setRecoverySubject(Yii::t('user', 'Complete password reset on {0}', Yii::$app->name));
        }
        return $this->recoverySubject;
    }

    /**
     * @param string $recoverySubject
     */
    public function setRecoverySubject($recoverySubject)
    {
        $this->recoverySubject = $recoverySubject;
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->module = Yii::$app->getModule('user');
        parent::init();
    }

    /**
     * @brief Отправляет электронное письмо пользователю после регистрации.
     *
     * @param User  $user
     * @param Token $token
     * @param bool  $showPassword
     *
     * @return bool
     */
    public function sendWelcomeMessage(User $user, Token $token = null, $showPassword = false)
    {
        return $this->sendMessage(
            $user->email,
            $this->getWelcomeSubject(),
            'welcome',
            ['user' => $user, 'token' => $token, 'module' => $this->module, 'showPassword' => $showPassword]
        );
    }

    /**
     * @brief Отправляет новый сгенерированный пароль пользователю.
     *
     * @param User  $user
     * @param Password $password
     *
     * @return bool
     */
    public function sendGeneratedPassword(User $user, $password)
    {
        return $this->sendMessage(
            $user->email,
            $this->getNewPasswordSubject(),
            'new_password',
            ['user' => $user, 'password' => $password, 'module' => $this->module]
        );
    }

    /**
     * @brief Отправляет электронное письмо пользователю с подтверждением.
     *
     * @param User  $user
     * @param Token $token
     * @return bool
     */
    public function sendConfirmationMessage(User $user, Token $token)
    {
        return $this->sendMessage(
            $user->email,
            $this->getConfirmationSubject(),
            'confirmation',
            ['user' => $user, 'token' => $token]
        );
    }

    /**
     * @brief Отправляет электронное письмо пользователю с ссылкой восстановления.
     *
     * @param User  $user
     * @param Token $token
     * @return bool
     */
    public function sendRecoveryMessage(User $user, Token $token)
    {
        return $this->sendMessage(
            $user->email,
            $this->getRecoverySubject(),
            'recovery',
            ['user' => $user, 'token' => $token]
        );
    }

    /**
     * @param string $to
     * @param string $subject
     * @param string $view
     * @param array  $params
     * @return bool
     */
    protected function sendMessage($to, $subject, $view, $params = [])
    {
        $mailer = $this->mailerComponent === null ? Yii::$app->mailer : Yii::$app->get($this->mailerComponent);
        $mailer->viewPath = $this->viewPath;
        $mailer->getView()->theme = Yii::$app->view->theme;
        if ($this->sender === null) {
            $this->sender = isset(Yii::$app->params['adminEmail']) ?
                Yii::$app->params['adminEmail']
                : 'no-reply@example.com';
        }
        return $mailer->compose(['html' => $view, 'text' => 'text/' . $view], $params)
            ->setTo($to)
            ->setFrom($this->sender)
            ->setSubject($subject)
            ->send();
    }
}
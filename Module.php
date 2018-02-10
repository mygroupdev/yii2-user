<?php

namespace mygroupdev\user;

use yii\base\Module as BaseModule;

/**
 * Class Module
 * @package mygroupdev\user
 * @author Dmitry Dmytruk <my.group.dev@gmail.com>
 */
class Module extends BaseModule
{
    const VERSION = '0.0.0';

    /**
     * @var bool Включить ли регистрация.
     */
    public $enableRegistration = true;

    /**
     * @var bool Генерировать случайный пароль если он не заполнен форме регистрации.
     */
    public $enableGeneratingPassword = false;

    /**
     * @var bool Должен ли пользователь подтвердить свою учетную запись.
     */
    public $enableConfirmation = true;

    /**
     * @var bool Разрешать вход в систему без подтверждения электронной почты.
     */
    public $enableUnconfirmedLogin = false;

    /**
     * @var bool Включить восстановление пароля.
     */
    public $enablePasswordRecovery = true;

    /**
     * @var bool Отображать флеш-сообщения.
     */
    public $enableFlashMessages = true;

    /**
     * @var int Blowfish cost.
     */
    public $cost = 10;

    /**
     * @var int Время жызни авторизации
     */
    public $rememberFor = 1209600; // Две недели

    /**
     * @var int Время на подтверждения токена, дальше он становится недействительным.
     */
    public $confirmWithin = 86400; // 24 часа

    /**
     * @var int Время до того, как токен восстановления станет недействительным.
     */
    public $recoverWithin = 21600; // 6 часов

    /**
     * @var bool Является ли пользовательский модуль в режиме DEBUG?
     * Будет автоматически установлено значение false, если приложение покинет режим DEBUG.
     */
    public $debug = false;

    /**
     * @var array Model map
     */
    public $modelMap = [];

    /**
     * @var array Mailer configuration
     */
    public $mailer = [];
}
<?php

namespace mygroupdev\user\models;

use mygroupdev\user\Finder;
use yii\web\IdentityInterface;
use yii\web\Application as WebApplication;
use yii\behaviors\TimestampBehavior;
use yii\base\NotSupportedException;
use yii\helpers\ArrayHelper;

use mygroupdev\user\traits\ModuleTrait;
use mygroupdev\user\helpers\Password;
use mygroupdev\user\Mailer;


/**
 * This is the model class for table "user".
 * @package mygroupdev\user
 * @author Dmitry Dmytruk <my.group.dev@gmail.com>
 *
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string $phone
 * @property string $password_hash
 * @property string $auth_key
 * @property string $sing_up_ip
 * @property int $role
 * @property int $status
 * @property int $last_sign_in
 * @property int $confirmed_at
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Token[] $tokens
 */

class User extends \yii\db\ActiveRecord implements IdentityInterface
{
    use ModuleTrait;

    const SCENARIO_SIGN_UP  = 'sign-up';
    const SCENARIO_CREATE   = 'create';
    const SCENARIO_UPDATE   = 'update';
    const SCENARIO_SETTINGS = 'settings';
    const BEFORE_SIGN_UP    = 'beforeSignUp';
    const AFTER_SIGN_UP     = 'afterSignUp';
    const BEFORE_CONFIRM    = 'beforeConfirm';
    const AFTER_CONFIRM     = 'afterConfirm';

    /**
     * @var string Обычный пароль. Используется для проверки модели.
     */
    public $password;

    /**
     * @var string валидация имени пользователя regexp
     */
    public static $usernameRegexp = '/^[-a-zA-Z0-9_\.@]+$/';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        return ArrayHelper::merge($scenarios, [
            self::SCENARIO_SIGN_UP  => ['username', 'email', 'password'],
            self::SCENARIO_CREATE   => ['username', 'email', 'password'],
            self::SCENARIO_UPDATE   => ['username', 'email', 'password'],
            self::SCENARIO_SETTINGS => ['username', 'email', 'password'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // Правила имени пользователя
            'usernameTrim'     => ['username', 'trim'],
            'usernameRequired' => ['username', 'required', 'on' => [
                self::SCENARIO_SIGN_UP,
                self::SCENARIO_CREATE,
                self::SCENARIO_UPDATE
            ]],
            'usernameMatch'    => ['username', 'match', 'pattern' => static::$usernameRegexp],
            'usernameLength'   => ['username', 'string', 'min' => 3, 'max' => 255],
            'usernameUnique'   => [
                'username',
                'unique',
                'message' => \Yii::t('user', 'This username has already been taken')
            ],
            // Правила Эл. адреса
            'emailTrim'     => ['email', 'trim'],
            'emailRequired' => ['email', 'required', 'on' => [
                self::SCENARIO_SIGN_UP,
                self::SCENARIO_CREATE,
                self::SCENARIO_UPDATE
            ]],
            'emailPattern'  => ['email', 'email'],
            'emailLength'   => ['email', 'string', 'max' => 255],
            'emailUnique'   => [
                'email',
                'unique',
                'message' => \Yii::t('user', 'This email address has already been taken')
            ],
            // Правила пароля
            'passwordRequired' => ['password', 'required', 'on' => [self::SCENARIO_SIGN_UP]],
            'passwordLength'   => ['password', 'string', 'min' => 6, 'max' => 72, 'on' => [
                self::SCENARIO_SIGN_UP,
                self::SCENARIO_CREATE
            ]],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'username'          => \Yii::t('user', 'Username'),
            'email'             => \Yii::t('user', 'Email'),
            'sing_up_ip'        => \Yii::t('user', 'Registration ip'),
            'unconfirmed_email' => \Yii::t('user', 'New email'),
            'password'          => \Yii::t('user', 'Password'),
            'created_at'        => \Yii::t('user', 'Registration time'),
            'last_sign_in'      => \Yii::t('user', 'Last login'),
            'confirmed_at'      => \Yii::t('user', 'Confirmation time'),
        ];
    }

    /**
     * @brief Регистрация нового пользователя. Работает только если Module::enableConfirmation = true.
     * Если Module::enableConfirmation = true, потребуется подтвердить адрес электронной почты
     * @return bool
     * @throws \Exception
     */
    public function signUp()
    {
        $transaction = $this->getDb()->beginTransaction();

        try {
            $this->confirmed_at = $this->module->enableConfirmation ? null : time();
            $this->password     = $this->module->enableGeneratingPassword ? Password::generate(8) : $this->password;

            $this->trigger(self::BEFORE_SIGN_UP);

            if (!$this->save()) {
                $transaction->rollBack();
                return false;
            }

            if ($this->module->enableConfirmation) {
                /** @var Token $token */
                $token = \Yii::createObject(['class' => $this->module->modelMap['Token'], 'type' => Token::TYPE_CONFIRMATION]);
                $token->link('user', $this);
            }

            $this->mailer->sendWelcomeMessage($this, isset($token) ? $token : null);
            $this->trigger(self::AFTER_SIGN_UP);
            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            \Yii::warning($e->getMessage());
            throw $e;
        }
    }

    /**
     * @param $code
     * @return bool
     */
    public function attemptConfirmation($code)
    {
        $token = $this->finder->findTokenByParams($this->id, $code, Token::TYPE_CONFIRMATION);

        if ($token instanceof Token && !$token->isExpired) {

            $token->delete();
            if (($success = $this->confirm())) {
                \Yii::$app->user->login($this, $this->module->rememberFor);
                $message = \Yii::t('user', 'Thank you, registration is now complete.');
            } else {
                $message = \Yii::t('user', 'Something went wrong and your account has not been confirmed.');
            }

        } else {
            $success = false;
            $message = \Yii::t('user', 'The confirmation link is invalid or expired. Please try requesting a new one.');
        }
        \Yii::$app->session->setFlash($success ? 'success' : 'danger', $message);
        return $success;
    }

    /**
     * @return bool
     */
    public function confirm()
    {
        $this->trigger(self::BEFORE_CONFIRM);
        $result = (bool) $this->updateAttributes(['confirmed_at' => time()]);
        $this->trigger(self::AFTER_CONFIRM);
        return $result;
    }

    /**
     * @brief Сбрасывает пароль.
     *
     * @param string $password
     * @return bool
     */
    public function resetPassword($password)
    {
        return (bool)$this->updateAttributes(['password_hash' => Password::hash($password)]);
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if ($insert) {
            $this->setAttribute('auth_key', \Yii::$app->security->generateRandomString());
            if (\Yii::$app instanceof WebApplication) {
                $this->setAttribute('sing_up_ip', \Yii::$app->request->userIP);
            }
        }
        if (!empty($this->password)) {
            $this->setAttribute('password_hash', Password::hash($this->password));
        }
        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getAttribute('id');
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->getAttribute('auth_key');
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAttribute('auth_key') === $authKey;
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('Method "' . __CLASS__ . '::' . __METHOD__ . '" is not implemented.');
    }

    /**
     * @return object
     */
    protected function getFinder()
    {
        return \Yii::$container->get(Finder::className());
    }

    /**
     * @return object|Mailer
     */
    protected function getMailer()
    {
        return \Yii::$container->get(Mailer::className());
    }

    /**
     * @return bool
     */
    public function getIsConfirmed()
    {
        return $this->confirmed_at != null;
    }

    /**
     * @return bool
     */
    public function getIsBlocked()
    {
        return $this->blocked_at != null;
    }

    /**
     * @return bool Whether the user is an admin or not.
     */
    public function getIsAdmin()
    {
        return false; // todo:
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTokens()
    {
        return $this->hasMany(Token::className(), ['user_id' => 'id']);
    }
}
<?php

namespace mygroupdev\user\models\form;

use mygroupdev\user\Finder;
use mygroupdev\user\Mailer;
use mygroupdev\user\models\Token;
use mygroupdev\user\models\User;
use mygroupdev\user\traits\ModuleTrait;
use yii\base\Model;

/**
 * Class ResendForm
 * @package mygroupdev\user\models\forms
 * @author Dmitry Dmytruk <my.group.dev@gmail.com>
 */
class ResendForm extends Model
{
    use ModuleTrait;
    /**
     * @var string
     */
    public $email;

    /**
     * @var Mailer
     */
    protected $mailer;

    /**
     * @var Finder
     */
    protected $finder;

    /**
     * @param Mailer $mailer
     * @param Finder $finder
     * @param array  $config
     */
    public function __construct(Mailer $mailer, Finder $finder, $config = [])
    {
        $this->mailer = $mailer;
        $this->finder = $finder;
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            'emailRequired' => ['email', 'required'],
            'emailPattern' => ['email', 'email'],
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'email' => \Yii::t('user', 'Email'),
        ];
    }
    /**
     * @inheritdoc
     */
    public function formName()
    {
        return 'resend-form';
    }

    /**
     * @brief Создает новый токен подтверждения и отправляет его пользователю.
     * @return bool
     */
    public function resend()
    {
        if (!$this->validate()) {
            return false;
        }

        $user = $this->finder->findUserByEmail($this->email);

        if ($user instanceof User && !$user->isConfirmed) {
            /** @var Token $token */
            $token = \Yii::createObject([
                'class' => $this->module->modelMap['Token'],
                'user_id' => $user->id,
                'type' => Token::TYPE_CONFIRMATION,
            ]);
            $token->save(false);
            $this->mailer->sendConfirmationMessage($user, $token);
        }

        if($this->module->enableFlashMessages) {
            if($user->isConfirmed) {
                \Yii::$app->session->setFlash(
                    'info',
                    \Yii::t(
                        'user',
                        'Your account has been verified.'
                    )
                );
            } else {
                \Yii::$app->session->setFlash(
                    'info',
                    \Yii::t(
                        'user',
                        'A message has been sent to your email address. It contains a confirmation link that you must click to complete registration.'
                    )
                );
            }
        }
        return true;
    }
}
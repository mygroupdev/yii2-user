<?php

namespace mygroupdev\user;

use mygroupdev\user\models\form\RecoveryForm;
use mygroupdev\user\models\form\ResendForm;
use mygroupdev\user\models\form\SignInForm;
use mygroupdev\user\models\form\SignUpForm;
use mygroupdev\user\models\Token;
use mygroupdev\user\models\User;

/**
 * Class DI
 * @package mygroupdev\user
 * @author Dmitry Dmytruk <my.group.dev@gmail.com>
 */
class DI extends \yii\base\Object
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var Token
     */
    protected $token;

    /**
     * @var SignUpForm
     */
    protected $signUpForm;

    /**
     * @var SignInForm
     */
    protected $signInForm;

    /**
     * @var RecoveryForm
     */
    protected $recoveryForm;

    /**
     * @var ResendForm
     */
    protected $resendForm;

    /**
     * @return SignUpForm
     */
    public function getSignUpForm()
    {
        return $this->signUpForm;
    }

    /**
     * @param SignUpForm $signUpForm
     */
    public function setSignUpForm(SignUpForm $signUpForm)
    {
        $this->signUpForm = $signUpForm;
    }

    /**
     * @return SignInForm
     */
    public function getSignInForm()
    {
        return $this->signInForm;
    }

    /**
     * @param SignInForm $signInForm
     */
    public function setSignInForm(SignInForm $signInForm)
    {
        $this->signInForm = $signInForm;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return Token
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param Token $token
     */
    public function setToken(Token $token)
    {
        $this->token = $token;
    }

    /**
     * @param RecoveryForm $recoveryForm
     */
    public function setRecoveryForm(RecoveryForm $recoveryForm)
    {
        $this->recoveryForm = $recoveryForm;
    }

    /**
     * @return RecoveryForm
     */
    public function getRecoveryForm()
    {
        return $this->recoveryForm;
    }

    /**
     * @param ResendForm $resendForm
     */
    public function setResendForm(ResendForm $resendForm)
    {
        $this->resendForm = $resendForm;
    }

    /**
     * @return ResendForm
     */
    public function getResendForm()
    {
        return $this->resendForm;
    }
}
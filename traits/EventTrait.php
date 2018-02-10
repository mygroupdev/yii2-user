<?php

namespace mygroupdev\user\traits;

use yii\base\Model;

use mygroupdev\user\events\UserEvent;
use mygroupdev\user\events\FormEvent;
use mygroupdev\user\events\ResetPasswordEvent;

use mygroupdev\user\models\User;
use mygroupdev\user\models\Token;

use mygroupdev\user\models\form\RecoveryForm;
use yii\web\IdentityInterface;

/**
 * Trait EventTrait
 * @package mygroupdev\user\traits
 * @author Dmitry Dmytruk <my.group.dev@gmail.com>
 */
trait EventTrait
{
    /**
     * @param Model $form
     * @return object
     */
    protected function getFormEvent(Model $form)
    {
        return \Yii::createObject(['class' => FormEvent::className(), 'form' => $form]);
    }

    /**
     * @param User|IdentityInterface $user
     * @return object
     */
    protected function getUserEvent(User $user)
    {
        return \Yii::createObject(['class' => UserEvent::className(), 'user' => $user]);
    }

    /**
     * @param Token|null $token
     * @param RecoveryForm|null $form
     * @return object
     */
    protected function getResetPasswordEvent(Token $token = null, RecoveryForm $form = null)
    {
        return \Yii::createObject(['class' => ResetPasswordEvent::className(), 'token' => $token, 'form' => $form]);
    }
}
<?php

namespace mygroupdev\user\events;

use yii\base\Event;
use mygroupdev\user\models\Token;
use mygroupdev\user\models\form\RecoveryForm;

/**
 * Class ResetPasswordEvent
 * @package mygroupdev\user\events
 * @author Dmitry Dmytruk <my.group.dev@gmail.com>
 */
class ResetPasswordEvent extends Event
{
    /**
     * @var RecoveryForm
     */
    private $_form;

    /**
     * @var Token
     */
    private $_token;

    /**
     * @return Token
     */
    public function getToken()
    {
        return $this->_token;
    }

    /**
     * @param Token $token
     */
    public function setToken(Token $token = null)
    {
        $this->_token = $token;
    }

    /**
     * @return RecoveryForm
     */
    public function getForm()
    {
        return $this->_form;
    }

    /**
     * @param RecoveryForm $form
     */
    public function setForm(RecoveryForm $form = null)
    {
        $this->_form = $form;
    }
}
<?php

namespace mygroupdev\user\events;

use yii\base\Event;
use mygroupdev\user\models\User;

/**
 * Class UserEvent
 * @package mygroupdev\user\events
 * @author Dmitry Dmytruk <my.group.dev@gmail.com>
 */
class UserEvent extends Event
{
    /**
     * @var User
     */
    private $_user;

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->_user;
    }

    /**
     * @param User $form
     */
    public function setUser(User $form)
    {
        $this->_user = $form;
    }
}
<?php

namespace mygroupdev\user\events;

use yii\base\Event;
use yii\base\Model;

/**
 * Class FormEvent
 * @package mygroupdev\user\events
 * @author Dmitry Dmytruk <my.group.dev@gmail.com>
 */
class FormEvent extends Event
{
    /**
     * @var Model
     */
    private $_form;

    /**
     * @return Model
     */
    public function getForm()
    {
        return $this->_form;
    }

    /**
     * @param Model $form
     */
    public function setForm(Model $form)
    {
        $this->_form = $form;
    }
}
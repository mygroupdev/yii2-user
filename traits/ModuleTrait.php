<?php

namespace mygroupdev\user\traits;

/**
 * Trait ModuleTrait
 * @package mygroupdev\user\traits
 * @author Dmitry Dmytruk <my.group.dev@gmail.com>
 */
trait ModuleTrait
{
    /**
     * @return null|\yii\base\Module
     */
    public function getModule()
    {
        return \Yii::$app->getModule('user');
    }
}
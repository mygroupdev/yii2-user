<?php

namespace mygroupdev\user\traits;

use yii\base\Model;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * Trait AjaxValidationTrait
 * @package mygroupdev\user\traits
 * @author Dmitry Dmytruk <my.group.dev@gmail.com>
 */
trait AjaxValidationTrait
{
    /**
     * @brief Выполняет ajax валидацию.
     *
     * @param Model $model
     *
     * @throws \yii\base\ExitException
     */
    protected function performAjaxValidation(Model $model)
    {
        if (\Yii::$app->request->isAjax && $model->load(\Yii::$app->request->post())) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            \Yii::$app->response->data   = ActiveForm::validate($model);
            \Yii::$app->response->send();
            \Yii::$app->end();
        }
    }
}
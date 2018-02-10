<?php

namespace mygroupdev\user\controllers;

use mygroupdev\user\components\CommonController;
use mygroupdev\user\models\form\ResendForm;
use mygroupdev\user\models\form\SignInForm;
use mygroupdev\user\models\form\SignUpForm;
use mygroupdev\user\models\form\RecoveryForm;
use mygroupdev\user\models\User;
use mygroupdev\user\models\Token;
use mygroupdev\user\traits\EventTrait;
use yii\web\NotFoundHttpException;
use mygroupdev\user\traits\AjaxValidationTrait;

/**
 * Class SecurityController
 * @package mygroupdev\user\controllers
 * @author Dmitry Dmytruk <my.group.dev@gmail.com>
 */
class SecurityController extends CommonController
{
    use AjaxValidationTrait;
    use EventTrait;

    const EVENT_BEFORE_SIGN_UP          = 'beforeSignUp';
    const EVENT_AFTER_SIGN_UP           = 'afterSignUp';

    const EVENT_BEFORE_CONFIRM          = 'beforeConfirm';
    const EVENT_AFTER_CONFIRM           = 'afterConfirm';

    const EVENT_BEFORE_SIGN_IN          = 'beforeSignIn';
    const EVENT_AFTER_SIGN_IN           = 'afterSignIn';

    const EVENT_BEFORE_RESEND           = 'beforeResend';
    const EVENT_AFTER_RESEND            = 'afterResend';

    const EVENT_BEFORE_LOGOUT           = 'beforeLogout';
    const EVENT_AFTER_LOGOUT            = 'afterLogout';


    const EVENT_BEFORE_REQUEST          = 'beforeRequest';
    const EVENT_AFTER_REQUEST           = 'afterRequest';

    const EVENT_BEFORE_RESET            = 'beforeReset';
    const EVENT_AFTER_RESET             = 'afterReset';

    const EVENT_BEFORE_TOKEN_VALIDATE   = 'beforeTokenValidate';
    const EVENT_AFTER_TOKEN_VALIDATE    = 'afterTokenValidate';

    /**
     * @brief Регистрация пользователя.
     *
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionSignUp()
    {
        if (!$this->module->enableRegistration) {
            throw new NotFoundHttpException();
        }

        /** @var SignUpForm $model */
        $model = $this->di->getSignUpForm();

        $event = $this->getFormEvent($model);

        $this->trigger(self::EVENT_BEFORE_SIGN_UP, $event);

        $this->performAjaxValidation($model);

        if ($model->load(\Yii::$app->request->post()) && $model->signUp()) {
            $this->trigger(self::EVENT_AFTER_SIGN_UP, $event);
            return $this->render('/message', [
                'title'  => \Yii::t('user', 'Your account has been created'),
                'module' => $this->module,
            ]);
        }


        return $this->render('sign-up', [
            'model' => $model,
            'module' => $this->module,
        ]);
    }

    /**
     * @brief Подтверждение электронной почты.
     *
     * @param $id
     * @param $code
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionConfirm($id, $code)
    {
        /** @var User $user */
        $user = $this->finder->findUserById($id);

        if ($user === null || $this->module->enableConfirmation == false) {
            throw new NotFoundHttpException();
        }

        $event = $this->getUserEvent($user);

        $this->trigger(self::EVENT_BEFORE_CONFIRM, $event);

        $user->attemptConfirmation($code);

        $this->trigger(self::EVENT_AFTER_CONFIRM, $event);

        return $this->render('/message', [
            'title'  => \Yii::t('user', 'Account confirmation'),
            'module' => $this->module,
        ]);
    }

    /**
     * @brief Отплавна нового письма подтверждения.
     *
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionResend()
    {
        if ($this->module->enableConfirmation == false) {
            throw new NotFoundHttpException();
        }

        /** @var ResendForm $model */
        $model = $this->di->getResendForm();

        $event = $this->getFormEvent($model);

        $this->trigger(self::EVENT_BEFORE_RESEND, $event);

        $this->performAjaxValidation($model);

        if ($model->load(\Yii::$app->request->post()) && $model->resend()) {

            $this->trigger(self::EVENT_AFTER_RESEND, $event);
            return $this->render('/message', [
                'title'  => \Yii::t('user', 'A new confirmation link has been sent'),
                'module' => $this->module,
            ]);
        }
        return $this->render('resend', [
            'model' => $model,
        ]);
    }

    /**
     * @brief Авторизация пользователя.
     *
     * @return string|\yii\web\Response
     */
    public function actionSignIn()
    {
        if (!\Yii::$app->user->isGuest) {
            $this->goHome();
        }
        /** @var SignInForm $model */
        $model = $this->di->getSignInForm();

        $event = $this->getFormEvent($model);

        $this->performAjaxValidation($model);

        $this->trigger(self::EVENT_BEFORE_SIGN_IN, $event);

        if ($model->load(\Yii::$app->getRequest()->post()) && $model->login()) {
            $this->trigger(self::EVENT_AFTER_SIGN_IN, $event);
            return $this->goBack();
        }
        return $this->render('sign-in', [
            'model'  => $model,
            'module' => $this->module,
        ]);
    }

    /**
     * @brief Выход пользователя.
     *
     * @return \yii\web\Response
     */
    public function actionLogout()
    {
        if(\Yii::$app->user->isGuest) {
            throw new NotFoundHttpException();
        }
        $event = $this->getUserEvent(\Yii::$app->user->identity);
        $this->trigger(self::EVENT_BEFORE_LOGOUT, $event);
        \Yii::$app->getUser()->logout();
        $this->trigger(self::EVENT_AFTER_LOGOUT, $event);
        return $this->goHome();
    }

    /**
     * @brief Страница, на которой пользователь может запросить восстановление пароля.
     *
     * @return string
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionRequest()
    {
        if (!$this->module->enablePasswordRecovery) {
            throw new NotFoundHttpException();
        }

        /** @var RecoveryForm $model */
        $model = $this->di->getRecoveryForm();
        $model->scenario = $model::SCENARIO_REQUEST;

        $event = $this->getFormEvent($model);

        $this->performAjaxValidation($model);
        $this->trigger(self::EVENT_BEFORE_REQUEST, $event);

        if ($model->load(\Yii::$app->request->post()) && $model->sendRecoveryMessage()) {
            $this->trigger(self::EVENT_AFTER_REQUEST, $event);
            return $this->render('/message', [
                'title'  => \Yii::t('user', 'Recovery message sent'),
                'module' => $this->module,
            ]);
        }

        return $this->render('request', [
            'model' => $model,
        ]);
    }

    /**
     * @brief Страница, на которой пользователь может сбросить пароль.
     *
     * @param $id
     * @param $code
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionReset($id, $code)
    {
        if (!$this->module->enablePasswordRecovery) {
            throw new NotFoundHttpException();
        }

        /** @var Token $token */
        $token = $this->finder->findToken(['user_id' => $id, 'code' => $code, 'type' => Token::TYPE_RECOVERY])->one();
        if (empty($token) || ! $token instanceof Token) {
            throw new NotFoundHttpException();
        }
        $event = $this->getResetPasswordEvent($token);

        $this->trigger(self::EVENT_BEFORE_TOKEN_VALIDATE, $event);

        if ($token === null || $token->isExpired || $token->user === null) {
            $this->trigger(self::EVENT_AFTER_TOKEN_VALIDATE, $event);
            \Yii::$app->session->setFlash(
                'danger',
                \Yii::t('user', 'Recovery link is invalid or expired. Please try requesting a new one.')
            );
            return $this->render('/message', [
                'title'  => \Yii::t('user', 'Invalid or expired link'),
                'module' => $this->module,
            ]);
        }

        /** @var RecoveryForm $model */
        $model = $this->di->getRecoveryForm();
        $model->scenario = $model::SCENARIO_RESET;

        $event->setForm($model);

        $this->performAjaxValidation($model);
        $this->trigger(self::EVENT_BEFORE_RESET, $event);

        if ($model->load(\Yii::$app->getRequest()->post()) && $model->resetPassword($token)) {
            $this->trigger(self::EVENT_AFTER_RESET, $event);
            return $this->render('/message', [
                'title'  => \Yii::t('user', 'Password has been changed'),
                'module' => $this->module,
            ]);
        }

        return $this->render('reset', [
            'model' => $model,
        ]);
    }
}
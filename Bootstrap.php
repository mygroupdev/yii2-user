<?php

namespace mygroupdev\user;

use Yii;
use yii\base\BootstrapInterface;
use yii\i18n\PhpMessageSource;

/**
 * Class Bootstrap
 * @package mygroupdev\user
 * @author Dmitry Dmytruk <my.group.dev@gmail.com>
 */
class Bootstrap implements BootstrapInterface
{
    private $_modelMap = [
        'User'              => 'mygroupdev\user\models\User',
        'Token'             => 'mygroupdev\user\models\Token',
        'SignUpForm'        => 'mygroupdev\user\models\form\SignUpForm',
        'SignInForm'        => 'mygroupdev\user\models\form\SignInForm',
        'RecoveryForm'      => 'mygroupdev\user\models\form\RecoveryForm',
        'ResendForm'        => 'mygroupdev\user\models\form\ResendForm',
    ];

    public function bootstrap($app)
    {
        if ($app->hasModule('user') && ($module = $app->getModule('user')) instanceof Module) {

            $this->_modelMap = array_merge($this->_modelMap, $module->modelMap);

            foreach ($this->_modelMap as $name => $definition) {

                $modelName = is_array($definition) ? $definition['class'] : $definition;
                $module->modelMap[$name] = $modelName;

                Yii::$container->set($name, function () use ($modelName) {
                    return Yii::createObject($modelName);
                });

                if (in_array($name, ['User', 'Token'])) {
                    Yii::$container->set($name . 'Query', function () use ($modelName) {
                        return $modelName::find();
                    });
                }
            }

            Yii::$container->setSingleton(Finder::className(), [
                'userQuery' => Yii::$container->get('UserQuery'),
                'tokenQuery' => Yii::$container->get('TokenQuery'),
            ]);

            Yii::$container->setSingleton(DI::className(), [
                'user'              => Yii::$container->get('User'),
                'token'             => Yii::$container->get('Token'),
                'signUpForm'        => Yii::$container->get('SignUpForm'),
                'signInForm'        => Yii::$container->get('SignInForm'),
                'RecoveryForm'      => Yii::$container->get('RecoveryForm'),
                'ResendForm'        => Yii::$container->get('ResendForm'),
            ]);

            if (!isset($app->get('i18n')->translations['user*'])) {
                $app->get('i18n')->translations['user*'] = [
                    'class' => PhpMessageSource::className(),
                    'basePath' => __DIR__ . '/messages',
                ];
            }

            Yii::$container->set('mygroupdev\user\Mailer', $module->mailer);

            $module->debug = $this->ensureCorrectDebugSetting();
        }
    }

    /**
     * @brief Убедитесь, что модуль не находится в режиме DEBUG в продакшене
     */
    public function ensureCorrectDebugSetting()
    {
        if (!defined('YII_DEBUG')) {
            return false;
        }
        if (!defined('YII_ENV')) {
            return false;
        }
        if (defined('YII_ENV') && YII_ENV !== 'dev') {
            return false;
        }
        if (defined('YII_DEBUG') && YII_DEBUG !== true) {
            return false;
        }
        return Yii::$app->getModule('user')->debug;
    }
}
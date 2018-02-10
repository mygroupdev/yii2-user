<?php

namespace mygroupdev\user\models;

use Yii;
use mygroupdev\user\traits\ModuleTrait;
use yii\helpers\Url;

/**
 * This is the model class for table "token".
 * @package mygroupdev\user
 * @author Dmitry Dmytruk <my.group.dev@gmail.com>
 *
 * @property int $user_id
 * @property string $code
 * @property int $type
 * @property int $created_at
 *
 * @property User $user
 */
class Token extends \yii\db\ActiveRecord
{
    use ModuleTrait;

    const TYPE_CONFIRMATION      = 0;
    const TYPE_RECOVERY          = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'token';
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        switch ($this->type) {
            case self::TYPE_CONFIRMATION:
                $route = '/user/security/confirm';
                break;
            case self::TYPE_RECOVERY:
                $route = '/user/security/reset';
                break;
            default:
                throw new \RuntimeException();
        }
        return Url::to([$route, 'id' => $this->user_id, 'code' => $this->code], true);
    }

    /**
     * @return bool Whether token has expired.
     */
    public function getIsExpired()
    {
        switch ($this->type) {
            case self::TYPE_CONFIRMATION:
            case self::TYPE_RECOVERY:
                $expirationTime = $this->module->recoverWithin;
                break;
            default:
                throw new \RuntimeException();
        }
        return ($this->created_at + $expirationTime) < time();
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if ($insert) {
            static::deleteAll(['user_id' => $this->user_id, 'type' => $this->type]);
            $this->setAttribute('created_at', time());
            $this->setAttribute('code', Yii::$app->security->generateRandomString());
        }
        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public static function primaryKey()
    {
        return ['user_id', 'code', 'type'];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne($this->module->modelMap['User'], ['id' => 'user_id']);
    }
}
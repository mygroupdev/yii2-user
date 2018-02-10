<?php

namespace mygroupdev\user;

use mygroupdev\user\traits\ModuleTrait;
use yii\db\ActiveQuery;

/**
 * Class Finder
 * @package mygroupdev\user
 * @author Dmitry Dmytruk <my.group.dev@gmail.com>
 */
class Finder extends \yii\base\Object
{
    use ModuleTrait;

    /**
     * @var ActiveQuery
     */
    protected $userQuery;

    /**
     * @var ActiveQuery
     */
    protected $tokenQuery;

    /**
     * @return ActiveQuery
     */
    public function getUserQuery()
    {
        return $this->userQuery;
    }

    /**
     * @param ActiveQuery $userQuery
     */
    public function setUserQuery(ActiveQuery $userQuery)
    {
        $this->userQuery = $userQuery;
    }

    /**
     * @return ActiveQuery
     */
    public function getTokenQuery()
    {
        return $this->tokenQuery;
    }

    /**
     * @param ActiveQuery $tokenQuery
     */
    public function setTokenQuery(ActiveQuery $tokenQuery)
    {
        $this->tokenQuery = $tokenQuery;
    }

    /**
     * @param $id
     * @return array|null|\yii\db\ActiveRecord
     */
    public function findUserById($id)
    {
        return $this->findUser(['id' => $id])->one();
    }

    /**
     * @param $email
     * @return array|null|\yii\db\ActiveRecord
     */
    public function findUserByEmail($email)
    {
        return $this->findUser(['email' => $email])->one();
    }

    /**
     * @param $username
     * @return array|null|\yii\db\ActiveRecord
     */
    public function findUserByUsername($username)
    {
        return $this->findUser(['username' => $username])->one();
    }

    /**
     * @param $usernameOrEmail
     * @return array|null|\yii\db\ActiveRecord
     */
    public function findUserByUsernameOrEmail($usernameOrEmail)
    {
        if (filter_var($usernameOrEmail, FILTER_VALIDATE_EMAIL)) {
            return $this->findUserByEmail($usernameOrEmail);
        }
        return $this->findUserByUsername($usernameOrEmail);
    }

    /**
     * @param $userId
     * @param $code
     * @param $type
     * @return array|null|\yii\db\ActiveRecord
     */
    public function findTokenByParams($userId, $code, $type)
    {
        return $this->findToken([
            'user_id' => $userId,
            'code'    => $code,
            'type'    => $type,
        ])->one();
    }

    /**
     * @param $condition
     * @return ActiveQuery
     */
    public function findUser($condition)
    {
        return $this->userQuery->where($condition);
    }

    /**
     * @param $condition
     * @return ActiveQuery
     */
    public function findToken($condition)
    {
        return $this->tokenQuery->where($condition);
    }
}
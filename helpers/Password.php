<?php

namespace mygroupdev\user\helpers;

use Yii;

/**
 * Class Password
 * @package mygroupdev\user\helpers
 * @author Dmitry Dmytruk <my.group.dev@gmail.com>
 */
class Password
{
    /**
     * @param $password
     * @return string
     */
    public static function hash($password)
    {
        return Yii::$app->security->generatePasswordHash($password, Yii::$app->getModule('user')->cost);
    }

    /**
     * @param $password
     * @param $hash
     * @return bool
     */
    public static function validate($password, $hash)
    {
        return Yii::$app->security->validatePassword($password, $hash);
    }

    /**
     * @param $length
     * @return string
     */
    public static function generate($length)
    {
        $sets = [
            'abcdefghjkmnpqrstuvwxyz',
            'ABCDEFGHJKMNPQRSTUVWXYZ',
            '23456789',
        ];
        $all = '';
        $password = '';
        foreach ($sets as $set) {
            $password .= $set[array_rand(str_split($set))];
            $all .= $set;
        }
        $all = str_split($all);
        for ($i = 0; $i < $length - count($sets); $i++) {
            $password .= $all[array_rand($all)];
        }
        $password = str_shuffle($password);
        return $password;
    }
}
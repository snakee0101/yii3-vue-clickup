<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class User extends ActiveRecord
{
    public static function tableName()
    {
        return 'users';
    }

    public static function findByEmail($email)
    {
        return self::find()->where(['email' => $email])->one();
    }

    public static function findByAccessToken($accessToken)
    {
        return self::find()->where(['access_token' => $accessToken])->one();
    }

    public function validatePassword($password)
    {
        return Yii::$app->getSecurity()->validatePassword($password, $this->password_hash);
    }
}

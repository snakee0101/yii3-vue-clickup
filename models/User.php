<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class User extends ActiveRecord implements \yii\web\IdentityInterface
{
    //serializer behavior - you need to include relations in serialized output
    public function fields()
    {
        $fields = parent::fields();
        $fields['spaces'] = 'spaces';
        return $fields;
    }

    public static function tableName()
    {
        return 'users';
    }

    public static function findByEmail($email)
    {
        return self::find()->where(['email' => $email])->one();
    }

    public function validatePassword($password)
    {
        return Yii::$app->getSecurity()->validatePassword($password, $this->password_hash);
    }

    public static function findIdentity($id)
    {
        return self::find()->where(['id' => $id])->one();
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return self::find()->where(['access_token' => $token])->one();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return $this->access_token;
    }

    public function validateAuthKey($authKey)
    {
        return $authKey == $this->getAuthKey();
    }

    public function getSpaces()
    {
        return $this->hasMany(Space::class, ['user_id' => 'id']);
    }
}

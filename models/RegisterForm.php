<?php

namespace app\models;

use yii\base\Model;

class RegisterForm extends Model
{
    public $name;
    public $email;
    public $password;

    public function rules()
    {
        return [
            [['name', 'email', 'password'], 'required'],
            ['name', 'string', 'min' => 3],
            ['email', 'email'],
            ['password', 'string', 'min' => 8],
            ['email', 'unique', 'targetClass' => \app\models\User::class, 'targetAttribute' => 'email']
        ];
    }
}
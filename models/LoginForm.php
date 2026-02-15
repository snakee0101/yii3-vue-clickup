<?php

namespace app\models;

use Yii;
use yii\base\Model;

class LoginForm extends Model
{
    public $email;
    public $password;
    private $user;

    public function rules()
    {
        return [
            [['email', 'password'], 'required'],
            ['email', 'exist', 'targetClass' => \app\models\User::class, 'targetAttribute' => 'email'],
            ['password', 'validatePassword'],
        ];
    }

    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $this->user = User::findByEmail($this->email);

            if(!Yii::$app->getSecurity()->validatePassword($this->password, $this->user->password_hash)) {
                $this->addError('password', 'Password is incorrect');
            }
        }
    }

    public function login()
    {
        Yii::$app->user->login($this->user, 3600 * 24 * 30);

        return $this->user;
    }
}

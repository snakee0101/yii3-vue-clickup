<?php

namespace app\controllers;

use app\models\User;
use Yii;
use yii\rest\Controller;

class UserController extends Controller
{
    public function actionRegister($name, $email, $password)
    {
        $user = new User();
        $user->name = $name;
        $user->email = $email;
        $user->password_hash = Yii::$app->getSecurity()->generatePasswordHash($password);
        $user->access_token = Yii::$app->security->generateRandomString();
        $user->save();
    }
}

<?php

namespace app\controllers;

use app\models\User;
use Yii;
use yii\rest\Controller;

class UserController extends Controller
{
    public function actionRegister()
    {
        $model = new \app\models\RegisterForm(); //Model is in fact a validator (not an ActiveRecord that saves data to DB)
        $model->name = Yii::$app->request->post('name');
        $model->email = Yii::$app->request->post('email');
        $model->password = Yii::$app->request->post('password');

        if ($model->validate() === false) {
            Yii::$app->response->statusCode = 422;

            return ['errors' => $model->errors];
        }

        //This IS ActiveRecord model that saves data to DB
        $user = new User();
        $user->name = $model->name;
        $user->email = $model->email;
        $user->password_hash = Yii::$app->getSecurity()->generatePasswordHash($model->password);
        $user->access_token = Yii::$app->security->generateRandomString();
        $user->save();

        //Login this user immediately
        Yii::$app->user->login($user, duration: 3600 * 24 * 30);

        return ['errors' => null];
    }

    public function actionLogin($email, $password)
    {

    }
}

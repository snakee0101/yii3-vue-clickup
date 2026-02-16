<?php

namespace app\controllers;

use app\models\Space;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\rest\ActiveController;

class SpaceController extends ActiveController
{
    public $modelClass = Space::class;

    public function behaviors()
    {
        $behaviours = parent::behaviors();
        $behaviours['authenticator']['authMethods'] = [HttpBearerAuth::class];
        //automatically resolves Bearer token (Header "Authorization" with contents "Bearer your-token") to User model that contains this token in "access_token" column,
        //and automatically authenticates that user so that it is accessible in Yii::$app->user->id variable

        return $behaviours;
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);   //delete default index action handler (which just returns every Space which we dont want) and redefine my own handler
        return $actions;
    }

    //my own index action handler - return all spaces belonging to current user
    public function actionIndex()
    {
        return Space::find()->where(['user_id' => Yii::$app->user->id])->all();
    }
}

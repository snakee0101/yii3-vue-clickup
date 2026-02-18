<?php

namespace app\controllers;

use app\models\Space;
use app\models\SpaceForm;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\Cors;
use yii\rest\ActiveController;

class SpaceController extends ActiveController
{
    public $modelClass = Space::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // remove default authenticator
        unset($behaviors['authenticator']);

        // CORS first
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
        ];

        // add authenticator back correctly
        $behaviors['authenticator'] = [
            'class' => \yii\filters\auth\HttpBearerAuth::class,
            'except' => ['options'], // allow preflight
        ];

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index'], $actions['create']);   //delete default index action handler (which just returns every Space which we dont want) and redefine my own handler
        return $actions;
    }

    //my own index action handler - return all spaces belonging to current user
    public function actionIndex()
    {
        return Space::find()->where(['user_id' => Yii::$app->user->id])->all();
    }

    public function actionCreate()
    {
        ['name' => $name, 'description' => $description] = Yii::$app->request->post();

        $model = new SpaceForm();
        $model->name = $name;

        if ($model->validate() === false) {
            Yii::$app->response->statusCode = 422;

            return ['errors' => $model->errors];
        }

        //This IS ActiveRecord model that saves data to DB
        $space = new Space();
        $space->name = $name;
        $space->description = $description;
        $space->user_id = Yii::$app->user->id;
        $space->save();

        return $space;
    }
}

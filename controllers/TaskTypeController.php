<?php

namespace app\controllers;

use app\models\TaskList;
use app\models\TaskListForm;
use app\models\TaskType;
use app\models\TaskTypeForm;
use Yii;
use yii\rest\ActiveController;

class TaskTypeController extends ActiveController
{
    public $modelClass = TaskType::class;

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
        unset($actions['create']);
        return $actions;
    }

    public function actionCreate()
    {
        ['type_name' => $type_name, 'icon_name' => $icon_name, 'icon_style' => $icon_style] = Yii::$app->request->post();

        $model = new TaskTypeForm();
        $model->type_name = $type_name;
        $model->icon_name = $icon_name;
        $model->user_id = Yii::$app->user->id;

        if ($model->validate() === false) {
            Yii::$app->response->statusCode = 422;

            return ['errors' => $model->errors];
        }

        //This IS ActiveRecord model that saves data to DB
        $task_type = new TaskType();
        $task_type->type_name = $type_name;
        $task_type->icon_name = $icon_name;
        $task_type->icon_style = $icon_style;
        $task_type->user_id = Yii::$app->user->id;
        $task_type->save();

        return $task_type;
    }
}

<?php

namespace app\controllers;

use app\models\Task;
use app\models\TaskForm;
use app\models\TaskList;
use Yii;
use yii\rest\ActiveController;

class TaskController extends ActiveController
{
    public $modelClass = TaskList::class;

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
        ['task_header' => $task_header, 'task_content' => $task_content, 'list_id' => $list_id] = Yii::$app->request->post();

        $model = new TaskForm();
        $model->task_header = $task_header;

        if ($model->validate() === false) {
            Yii::$app->response->statusCode = 422;

            return ['errors' => $model->errors];
        }

        //This IS ActiveRecord model that saves data to DB
        $task = new Task();
        $task->task_header = $task_header;
        $task->task_content = $task_content;
        $task->list_id = $list_id;
        $task->save();

        return $task;
    }
}

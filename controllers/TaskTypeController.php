<?php

namespace app\controllers;

use app\models\EditTaskTypeForm;
use app\models\Task;
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
        unset($actions['create'], $actions['index'], $actions['delete'], $actions['update']);
        return $actions;
    }

    public function actionIndex()
    {
        return TaskType::find()->where(['user_id' => Yii::$app->user->id])->all();
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

    public function actionUpdate($id)
    {
        ['type_name' => $type_name, 'icon_name' => $icon_name, 'icon_style' => $icon_style] = Yii::$app->request->post();

        $model = new EditTaskTypeForm();
        $model->type_name = $type_name;
        $model->icon_name = $icon_name;

        if ($model->validate() === false) {
            Yii::$app->response->statusCode = 422;

            return ['errors' => $model->errors];
        }

        //This IS ActiveRecord model that saves data to DB
        $task_type = TaskType::findOne($id);
        $task_type->type_name = $type_name;
        $task_type->icon_name = $icon_name;
        $task_type->user_id = Yii::$app->user->id;
        $task_type->icon_style = $icon_style;
        $task_type->save();

        return $task_type;
    }

    public function actionDelete($id)
    {
        //reset all tasks that have the task type we are deleting to default task type
        $default_task_type = TaskType::find()->where(['user_id' => Yii::$app->user->id, 'type_name' => 'Task'])->one();
        Task::updateAll(['task_type_id' => $default_task_type->id], ['task_type_id' => $id]);

        //before we delete the task itself
        TaskType::deleteAll(['id' => $id]);
    }
}

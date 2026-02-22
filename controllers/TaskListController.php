<?php

namespace app\controllers;

use app\models\TaskList;
use app\models\TaskListForm;
use Yii;
use yii\rest\ActiveController;

class TaskListController extends ActiveController
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
        unset($actions['create'], $actions['update']);
        return $actions;
    }

    public function actionCreate()
    {
        ['list_name' => $list_name, 'description' => $description, 'folder_id' => $folder_id] = Yii::$app->request->post();

        $model = new TaskListForm();
        $model->list_name = $list_name;

        if ($model->validate() === false) {
            Yii::$app->response->statusCode = 422;

            return ['errors' => $model->errors];
        }

        //This IS ActiveRecord model that saves data to DB
        $list = new TaskList();
        $list->list_name = $list_name;
        $list->description = $description;
        $list->folder_id = $folder_id;
        $list->save();

        return $list;
    }

    public function actionUpdate($id)
    {
        ['list_name' => $list_name, 'description' => $description] = Yii::$app->request->post();

        $model = new TaskListForm();
        $model->list_name = $list_name;

        if ($model->validate() === false) {
            Yii::$app->response->statusCode = 422;

            return ['errors' => $model->errors];
        }

        //update model
        $list = TaskList::find()->where(['id' => $id])->one();
        $list->list_name = $list_name;
        $list->description = $description;
        $list->save();

        return $list;
    }
}

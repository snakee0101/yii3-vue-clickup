<?php

namespace app\controllers;

use app\models\TaskComment;
use app\models\TaskCommentForm;
use Yii;
use yii\rest\ActiveController;

class TaskCommentController extends ActiveController
{
    public $modelClass = TaskComment::class;

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
        unset($actions['create'], $actions['delete']);
        return $actions;
    }

    public function actionCreate()
    {
        ['comment_content' => $comment_content, 'task_id' => $task_id] = Yii::$app->request->post();

        $model = new TaskCommentForm();
        $model->comment_content = $comment_content;
        $model->task_id = $task_id;

        if ($model->validate() === false) {
            Yii::$app->response->statusCode = 422;

            return ['errors' => $model->errors];
        }

        //This IS ActiveRecord model that saves data to DB
        $comment = new TaskComment();
        $comment->user_id = Yii::$app->user->id;
        $comment->task_id = $task_id;
        $comment->comment_content = $comment_content;
        $comment->save();

        return $comment;
    }

    public function actionDelete($id)
    {
        $model = TaskComment::findOne($id);
        $model->delete();
    }
}

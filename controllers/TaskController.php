<?php

namespace app\controllers;

use app\models\Tag;
use app\models\Task;
use app\models\TaskForm;
use Yii;
use yii\rest\ActiveController;
use yii\web\UploadedFile;

class TaskController extends ActiveController
{
    public $modelClass = Task::class;

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
        $post = Yii::$app->request->post();

        $task_header  = $post['task_header']  ?? null;
        $task_content = $post['task_content'] ?? null;
        $list_id      = $post['list_id']      ?? null;
        $parent_id    = $post['parent_id']    ?? null;
        $priority     = $post['priority']     ?? null;
        $due_date     = $post['due_date']     ?? null;
        $start_date   = $post['start_date']   ?? null;
        $tags         = $post['tags'] == '[]' ? [] : $post['tags'];

        $model = new TaskForm();
        $model->task_header = $task_header;
        $model->due_date = $due_date;
        $model->start_date = $start_date;

        if ($model->validate() === false) {
            Yii::$app->response->statusCode = 422;

            return ['errors' => $model->errors];
        }

        //This IS ActiveRecord model that saves data to DB
        $task = new Task();
        $task->task_header = $task_header;
        $task->task_content = $task_content;
        $task->list_id = $list_id;
        $task->parent_id = $parent_id;
        $task->priority = $priority;
        $task->due_date = $due_date;
        $task->start_date = $start_date;
        $task->save();

        //process associated tags
        foreach ($tags as $tag) {
            if(is_null($tag['id'])) {
                //if tag doesn't exist, create it and associate with a note
                $newTag = new Tag();
                $newTag->tag_name = $tag['tag_name'];
                $newTag->user_id = Yii::$app->user->id;
                $newTag->save();

                $task->link('tags', $newTag);
            } else {
                //otherwise associate existing tag
                $task->link('tags', Tag::findOne($tag['id']));
            }

        }

        //process attachments
        $model->uploadAndSaveAttachments($task, 'attachments');
        return $task;
    }

    public function actionUpdate($id)
    {
        $task_header  = $_REQUEST['task_header']  ?? null;
        $task_content = $_REQUEST['task_content'] ?? null;
        $priority     = $_REQUEST['priority']     ?? null;
        $due_date     = $_REQUEST['due_date']     ?? null;
        $start_date   = $_REQUEST['start_date']   ?? null;

        $tags = isset($_REQUEST['tags'])
            ? json_decode($_REQUEST['tags'], true)
            : [];

        $model = new TaskForm();
        $model->task_header = $task_header;
        $model->due_date = $due_date;
        $model->start_date = $start_date;

        if ($model->validate() === false) {
            Yii::$app->response->statusCode = 422;

            return ['errors' => $model->errors];
        }

        //update model
        $task = Task::find()->where(['id' => $id])->one();
        $task->task_header = $task_header;
        $task->task_content = $task_content;
        $task->priority = $priority;
        $task->due_date = $due_date;
        $task->start_date = $start_date;
        $task->save();

        //process associated tags
        $task->unlinkAll('tags', true);

        foreach ($tags as $tag) {
            if(is_null($tag['id'])) {
                //if tag doesn't exist, create it and associate with a note
                $newTag = new Tag();
                $newTag->tag_name = $tag['tag_name'];
                $newTag->user_id = Yii::$app->user->id;
                $newTag->save();

                $task->link('tags', $newTag);
            } else {
                //otherwise associate existing tag
                $task->link('tags', Tag::findOne($tag['id']));
            }
        }

        $model->uploadAndSaveAttachments($task, 'new_attachments');

        return $task;
    }
}

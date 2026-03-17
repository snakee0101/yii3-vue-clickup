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
        unset($actions['create'], $actions['update'], $actions['view']);
        return $actions;
    }

    public function actionView($id)
    {
        return Task::find()->with(['tags', 'subtasks', 'attachments', 'checklists.items', 'taskComments'])->where(['id' => $id])->asArray()->one();
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
        $task_type_id = $post['task_type_id'];
        $tags         = $post['tags'] == '[]' ? [] : json_decode($post['tags']);
        $checklists   = $post['checklists'] == '[]' ? [] : json_decode($post['checklists']);

        $model = new TaskForm();
        $model->task_header = $task_header;
        $model->due_date = $due_date;
        $model->start_date = $start_date;
        $model->checklists = $checklists;

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
        $task->task_type_id = $task_type_id;
        $task->save();

        //process associated tags
        foreach ($tags as $tag) {
            if(is_null($tag->id)) {
                //if tag doesn't exist, create it and associate with a note
                $newTag = new Tag();
                $newTag->tag_name = $tag->tag_name;
                $newTag->user_id = Yii::$app->user->id;
                $newTag->save();

                $task->link('tags', $newTag);
            } else {
                //otherwise associate existing tag
                $task->link('tags', Tag::findOne($tag->id));
            }

        }

        //process attachments
        $model->uploadAndSaveAttachments($task, 'attachments');
        $model->saveChecklists($task, $checklists);
        return $task;
    }

    public function actionUpdate($id)
    {
        //if we want to quickly update only one parameter
        $request = Yii::$app->request->post();

        $task_header  = $request['task_header']  ?? null;
        $task_content = $request['task_content'] ?? null;
        $priority     = $request['priority']     ?? null;
        $due_date     = $request['due_date']     ?? null;
        $start_date   = $request['start_date']   ?? null;
        $task_type_id = $request['task_type_id'] ?? null;

        if(isset($request['update_one_field'])) {
            $task = Task::find()->where(['id' => $id])->one();

            $task->priority = array_key_exists('priority', $request) ? $request['priority'] : $task->priority;
            $task->due_date = $due_date ?? $task->due_date;
            $task->start_date = $start_date ?? $task->start_date;
            $task->task_type_id = $task_type_id ?? $task->task_type_id;

            $task->save();

            return;
        }

        //must be checked after "one field" operations, otherwise we will get undefined array key error
        $attachments  = isset($request['attachments']) ? json_decode($request['attachments']) : [];
        $checklists = [];

        if(array_key_exists('checklists', $request)) {
            $checklists   = $request['checklists'] == '[]' ? [] : json_decode($request['checklists']);
        }

        $tags = isset($request['tags'])
            ? json_decode($request['tags'], true)
            : [];

        $model = new TaskForm();
        $model->task_header = $task_header;
        $model->due_date = $due_date;
        $model->start_date = $start_date;
        $model->checklists = $checklists;

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
        $task->task_type_id = $task_type_id;
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
        $model->deleteMissingAttachments($task, $attachments);
        $model->manageChecklists($task, $checklists);

        return $task;
    }
}

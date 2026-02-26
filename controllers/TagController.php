<?php

namespace app\controllers;

use app\models\Tag;
use app\models\Task;
use Yii;
use yii\rest\ActiveController;

class TagController extends ActiveController
{
    public $modelClass = Tag::class;

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

    public function actionDetach()
    {
        ['task_id' => $task_id, 'tag_id' => $tag_id] = Yii::$app->request->post();
        $task = Task::findOne($task_id);
        $tag = Tag::findOne($tag_id);

        return $task->unlink('tags', $tag, true);
    }
}

<?php

namespace app\models;

use yii\base\Model;

class TaskCommentForm extends Model
{
    public $comment_content;
    public $task_id;

    public function rules()
    {
        return [
            [['comment_content'], 'required'],
            [['task_id'], 'exist', 'targetClass' => \app\models\Task::class, 'targetAttribute' => 'id'],
        ];
    }
}
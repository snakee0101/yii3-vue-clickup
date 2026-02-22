<?php

namespace app\models;

use yii\base\Model;

class TaskForm extends Model
{
    public $task_header;

    public function rules()
    {
        return [
            [['task_header'], 'required'],
        ];
    }
}
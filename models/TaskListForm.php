<?php

namespace app\models;

use yii\base\Model;

class TaskListForm extends Model
{
    public $list_name;

    public function rules()
    {
        return [
            [['list_name'], 'required'],
        ];
    }
}
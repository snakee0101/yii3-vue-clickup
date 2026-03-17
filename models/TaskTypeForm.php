<?php

namespace app\models;

use yii\base\Model;

class TaskTypeForm extends Model
{
    public $type_name;
    public $icon_name;
    public $user_id;

    public function rules()
    {
        return [
            [['type_name'], 'required'],
            [['icon_name'], 'required'],
            [['type_name', 'user_id'], 'unique',
            'targetAttribute' => ['type_name', 'user_id'], 'targetClass' => TaskType::className()]
        ];
    }
}
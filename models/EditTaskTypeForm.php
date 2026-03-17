<?php

namespace app\models;

use yii\base\Model;

class EditTaskTypeForm extends Model
{
    public $type_name;
    public $icon_name;

    public function rules()
    {
        return [
            [['type_name'], 'required'],
            [['icon_name'], 'required'],
        ];
    }
}
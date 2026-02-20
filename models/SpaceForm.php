<?php

namespace app\models;

use yii\base\Model;

class SpaceForm extends Model
{
    public $space_name;

    public function rules()
    {
        return [
            [['space_name'], 'required'],
            [['space_name'], 'unique', 'targetClass' => Space::class, 'targetAttribute' => 'space_name']
        ];
    }
}
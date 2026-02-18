<?php

namespace app\models;

use yii\base\Model;

class SpaceForm extends Model
{
    public $name;

    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'unique', 'targetClass' => Space::class, 'targetAttribute' => 'name']
        ];
    }
}
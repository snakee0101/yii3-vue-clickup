<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Task extends ActiveRecord
{
    //serializer behavior - you need to include relations in serialized output
    public function fields()
    {
        $fields = parent::fields();
        $fields['subtasks'] = 'subtasks';
        return $fields;
    }

    public static function tableName()
    {
        return 'tasks';
    }

    public function getSubtasks()
    {
        return $this->hasMany(Task::class, ['parent_id' => 'id']);
    }
}

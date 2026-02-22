<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class TaskList extends ActiveRecord
{
    //serializer behavior - you need to include relations in serialized output
    public function fields()
    {
        $fields = parent::fields();
        $fields['tasks'] = 'tasks';
        return $fields;
    }

    public static function tableName()
    {
        return 'lists';
    }

    public function getFolder()
    {
        return $this->hasOne(Folder::class, ['id' => 'folder_id']);
    }

    public function getTasks()
    {
        return $this->hasMany(Task::class, ['list_id' => 'id'])
                    ->where(['parent_id' => null]);
    }
}

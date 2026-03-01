<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Folder extends ActiveRecord
{
    //serializer behavior - you need to include relations in serialized output
    //for test commit
    public function fields()
    {
        $fields = parent::fields();
        $fields['lists'] = 'lists';
        return $fields;
    }

    public static function tableName()
    {
        return 'folders';
    }

    public function getSpace()
    {
        return $this->hasOne(Space::class, ['id' => 'space_id']);
    }

    public function getLists()
    {
        return $this->hasMany(TaskList::class, ['folder_id' => 'id']);
    }
}

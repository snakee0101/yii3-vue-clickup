<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Folder extends ActiveRecord
{
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

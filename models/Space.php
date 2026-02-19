<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Space extends ActiveRecord
{
    public static function tableName()
    {
        return 'spaces';
    }

    public function getFolders()
    {
        return $this->hasMany(Folder::class, ['space_id' => 'id']);
    }
}

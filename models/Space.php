<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Space extends ActiveRecord
{
    //serializer behavior - you need to include relations in serialized output
    public function fields()
    {
        $fields = parent::fields();
        $fields['folders'] = 'folders';
        return $fields;
    }

    public static function tableName()
    {
        return 'spaces';
    }

    public function getFolders()
    {
        return $this->hasMany(Folder::class, ['space_id' => 'id']);
    }
}

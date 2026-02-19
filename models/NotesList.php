<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class NotesList extends ActiveRecord
{
    public static function tableName()
    {
        return 'lists';
    }

    public function getFolder()
    {
        return $this->hasOne(Folder::class, ['id' => 'folder_id']);
    }
}

<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Checklist extends ActiveRecord
{
    //serializer behavior - you need to include relations in serialized output
    public function fields()
    {
        $fields = parent::fields();
        $fields['items'] = 'items';
        return $fields;
    }

    public static function tableName()
    {
        return 'checklists';
    }

    public function getItems()
    {
        return $this->hasMany(ChecklistItem::class, ['checklist_id' => 'id']);
    }
}

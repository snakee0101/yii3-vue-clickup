<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class ChecklistItem extends ActiveRecord
{
    public static function tableName()
    {
        return 'checklist_items';
    }
}

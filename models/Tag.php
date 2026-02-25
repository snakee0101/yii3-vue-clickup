<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Tag extends ActiveRecord
{
    public static function tableName()
    {
        return 'tags';
    }
}

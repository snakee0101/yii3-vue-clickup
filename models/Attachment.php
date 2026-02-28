<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Attachment extends ActiveRecord
{
    public static function tableName()
    {
        return 'attachments';
    }
}

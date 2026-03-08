<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class TaskComment extends ActiveRecord
{
    //serializer behavior - you need to include relations in serialized output
    public function fields()
    {
        return [...parent::fields(), 'id'];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
            ],
        ];
    }

    public static function tableName()
    {
        return 'task_comments';
    }
}

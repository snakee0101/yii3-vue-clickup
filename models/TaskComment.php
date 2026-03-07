<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class TaskComment extends ActiveRecord
{
    //serializer behavior - you need to include relations in serialized output
    public function fields()
    {
        $fields = parent::fields();
        return $fields;
    }

    public static function tableName()
    {
        return 'task_comments';
    }
}

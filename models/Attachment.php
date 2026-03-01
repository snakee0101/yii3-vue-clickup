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

    public function getTask()
    {
        return $this->hasOne(Task::class, ['id' => 'task_id']);
    }
}

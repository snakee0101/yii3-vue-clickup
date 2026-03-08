<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Task extends ActiveRecord
{
    //serializer behavior - you need to include relations in serialized output
    public function fields()
    {
        $fields = parent::fields();
        $fields['subtasks'] = 'subtasks';
        $fields['tags'] = 'tags';
        $fields['attachments'] = 'attachments';
        $fields['checklists'] = 'checklists';
        $fields['taskComments'] = 'taskComments';
        return $fields;
    }

    public static function tableName()
    {
        return 'tasks';
    }

    public function getSubtasks()
    {
        return $this->hasMany(Task::class, ['parent_id' => 'id']);
    }

    public function getTags()
    {
        return $this->hasMany(Tag::class, ['id' => 'tag_id'])
                    ->viaTable('task_tags', ['task_id' => 'id']);
    }

    public function getAttachments()
    {
        return $this->hasMany(Attachment::class, ['task_id' => 'id'])->inverseOf('task');
    }

    public function getChecklists()
    {
        return $this->hasMany(Checklist::class, ['task_id' => 'id']);
    }

    public function getTaskComments()
    {
        return $this->hasMany(TaskComment::class, ['task_id' => 'id'])->orderBy(['created_at' => SORT_DESC]);
    }
}

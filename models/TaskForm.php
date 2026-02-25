<?php

namespace app\models;

use yii\base\Model;

class TaskForm extends Model
{
    public $task_header;
    public $start_date;
    public $due_date;

    public function rules()
    {
        return [
            [['task_header'], 'required'],
            [['due_date'], 'validateDueDate']
        ];
    }

    public function validateDueDate($due_date)
    {
        if ($this->start_date == null && $this->due_date == null) {
            return;
        }

        if($this->start_date > $this->due_date) {
            $this->addError('due_date', 'Due date must be on or after start date');
        }
    }
}
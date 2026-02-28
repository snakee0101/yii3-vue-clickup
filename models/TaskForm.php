<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

class TaskForm extends Model
{
    public $task_header;
    public $start_date;
    public $due_date;
    public $attachments;

    public function rules()
    {
        return [
            [['task_header'], 'required'],
            [['due_date'], 'validateDueDate'],
            [['attachments'], 'file', 'skipOnEmpty' => true, 'maxFiles' => 10],
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

    public function uploadAndSaveAttachments($task, $request_key)
    {
        $this->attachments = UploadedFile::getInstancesByName($request_key);

        foreach ($this->attachments as $file) {
            $random_filename = $file->baseName . Yii::$app->getSecurity()->generateRandomString(60) . time() . '.' . $file->extension;
            $file->saveAs('uploads/' . $random_filename); //uploads in /web/uploads directory - so the files are accessible by the user

            $attachment = new Attachment();
            $attachment->task_id = $task->id;
            $attachment->filename = $file->baseName . '.' . $file->extension;
            $attachment->size = $file->size;
            $attachment->file_path = 'uploads/' . $random_filename;
            $attachment->created_at = date('Y-m-d H:i:s');
            $attachment->updated_at = date('Y-m-d H:i:s');
            $attachment->save();
        }
    }
}
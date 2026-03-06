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
    public $checklists;

    public function rules()
    {
        return [
            [['task_header'], 'required'],
            [['due_date'], 'validateDueDate'],
            [['checklists'], 'validateChecklists'],
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

    public function validateChecklists()
    {
        foreach ($this->checklists as $checklist)
        {
            if($checklist->checklist_name == '')
            {
                $this->addError('checklists.' . $checklist->temp_unique_id, 'Checklist name must not be empty');
            }

            foreach ($checklist->items as $checklist_item)
            {
                if($checklist_item->item_name == '')
                {
                    $this->addError('checklists.' . $checklist->temp_unique_id . '.item.' . $checklist_item->temp_unique_id, 'Checklist item must not be empty');
                }
            }
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

    public function deleteMissingAttachments(Task $task, array $attachments)
    {
        $attachment_ids = array_column($attachments, 'id');

        $deleted_attachments = Attachment::find()
            ->where(['not in', 'id', $attachment_ids])
            ->andWhere(['task_id' => $task->id])
            ->all();

        foreach ($deleted_attachments as $attachment) {
            unlink($attachment->file_path);

            $task->unlink('attachments', $attachment, true);
        }
    }

    public function saveChecklists(Task $task, array $checklists)
    {
        foreach ($checklists as $checklist)
        {
            $checklist_model = new Checklist();
            $checklist_model->checklist_name = $checklist->checklist_name;
            $checklist_model->task_id = $task->id;
            $checklist_model->save();

            foreach ($checklist->items as $checklist_item)
            {
                $checklist_model->refresh();

                $checklist_item_model = new ChecklistItem();
                $checklist_item_model->checklist_id = $checklist_model->id;
                $checklist_item_model->item_name = $checklist_item->item_name;
                $checklist_item_model->is_completed = $checklist_item->is_completed;
                $checklist_item_model->save();
            }
        }
    }

    public function manageChecklists(Task $task, array $checklists)
    {
        /*
         *  {
        "temp_unique_id": "hGKIbsNqkyzefDOXO4LcsnN6C66U7o0rn2Ho7IcTRgs2inh8M4Gno04hpLH3EZrs",
        "checklist_name": "Checklist",
        "task_id": 2,
        "id": 1, //present if its not new
        "items": [
            {
                "id": 10, //present if its not new
                "item_name": "752075750",
                "is_completed": false,
                "temp_unique_id": "zD2hnFqy8M5OlycxBmHt3cDxWp2KWhwdDMUQXvrx7XqYDyvTXGcqzSGXoB3iysyw"
            },
            {
                "item_name": "2072",
                "is_completed": true,
                "temp_unique_id": "zX28zJzCW9ALEByPZ1pmkqSdhrCo4HBP7EOGUtQLwBBf8zLS7xdyl4dSnFLw1Fpt"
            },
            {
                "item_name": "52",
                "is_completed": false,
                "temp_unique_id": "aXfMowmLyU44WUTeVJRZCwXg6xjMgjK2HHwuBGUtMhsfRXTpmnHYzsXFKotsPcxi"
            }
        ]
    }
         * */

        $original_checklist_ids = array_column($task->checklists, 'id');

        //1. if there are checklist ids that are present in the table, but not in the form - that are the ids of checklists to be deleted
        $form_checklist_ids = array_column($checklists, 'id');
        $deleted_checklist_ids = array_diff($original_checklist_ids, $form_checklist_ids);
        Checklist::deleteAll(['in', 'id', $deleted_checklist_ids]);

        foreach ($checklists as $checklist)
        {
            //2. if checklist id is not defined - that means it is a new checklist - and it must be created
            if(property_exists($checklist, 'id') === false)
            {
                $this->saveChecklists($task, [$checklist]);
                continue;
            }

            //3. if checklist exist - edit it (change the name)
            $checklist_model = Checklist::findOne(['id' => $checklist->id]);
            $checklist_model->checklist_name = $checklist->checklist_name;
            $checklist_model->save();

            //4. manage checklist items
            foreach ($checklist->items as $checklist_item)
            {
                //5. if checklist item doesn't have id - than its new
                if(property_exists($checklist_item, 'id') === false)
                {
                    $checklist_item_model = new ChecklistItem();
                    $checklist_item_model->checklist_id = $checklist_model->id;
                    $checklist_item_model->item_name = $checklist_item->item_name;
                    $checklist_item_model->is_completed = $checklist_item->is_completed;
                    $checklist_item_model->save();

                    continue;
                }

                //6. otherwise it is an existing checklist item and it must be edited
                $checklist_item_model = ChecklistItem::findOne(['id' => $checklist_item->id]);
                $checklist_item_model->checklist_id = $checklist_model->id;
                $checklist_item_model->item_name = $checklist_item->item_name;
                $checklist_item_model->is_completed = $checklist_item->is_completed;
                $checklist_item_model->save();
            }
        }

        return $checklists;
    }
}
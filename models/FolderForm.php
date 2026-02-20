<?php

namespace app\models;

use yii\base\Model;

class FolderForm extends Model
{
    public $folder_name;

    public function rules()
    {
        return [
            [['folder_name'], 'required'],
        ];
    }
}
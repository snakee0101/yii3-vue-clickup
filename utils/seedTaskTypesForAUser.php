<?php

namespace utils;

function seedTaskTypesForAUser(int $user_id): array
{
    $standard_task_types = require \Yii::getAlias('@app/config/task_types.php');

    return array_map(function ($data) use ($user_id) {
        $task_type = new \app\models\TaskType(['user_id' => $user_id]);
        $task_type->setAttributes($data, false);
        $task_type->save();

        return $task_type;
    }, $standard_task_types);
}
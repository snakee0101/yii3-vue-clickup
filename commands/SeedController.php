<?php

namespace app\commands;

use app\models\Checklist;
use app\models\ChecklistItem;
use app\models\TaskComment;
use app\models\TaskList;
use app\models\TaskType;
use app\models\User;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\console\Controller;
use yii\console\ExitCode;
use Faker;

class SeedController extends Controller
{
    protected function seedUsers()
    {
        $faker = Faker\Factory::create();

        $user1 = new User();
        $user1->name = $faker->name;
        $user1->email = 'test@gmail.com';
        $user1->password_hash = Yii::$app->security->generatePasswordHash('12345678');
        $user1->access_token = Yii::$app->security->generateRandomString(32);
        $user1->save(false);


        $user2 = new User();
        $user2->name = $faker->name;
        $user2->email = 'test2@gmail.com';
        $user2->password_hash = Yii::$app->security->generatePasswordHash('1234567890');
        $user2->access_token = Yii::$app->security->generateRandomString(32);
        $user2->save(false);

        echo "Seeding users..." . "\n";
        return [$user1, $user2];
    }

    protected function seedSpaces($users)
    {
        $faker = Faker\Factory::create();

        $spaces = [];

        foreach ($users as $user) {
            for ($i = 0; $i < 2; $i++) {
                $space = new \app\models\Space();
                $space->space_name = $faker->company;
                $space->description = $faker->paragraph;
                $space->user_id = $user->id;
                $space->save(false);

                $spaces[] = $space;
            }
        }

        echo "Seeding spaces..." . "\n";
        return $spaces;
    }

    protected function seedFolders($spaces)
    {
        $faker = Faker\Factory::create();

        $folders = [];

        foreach ($spaces as $space) {
            for ($i = 0; $i < 2; $i++) {
                $folder = new \app\models\Folder();
                $folder->folder_name = $faker->word;
                $folder->description = $faker->paragraph;
                $folder->space_id = $space->id;
                $folder->save(false);

                $folders[] = $folder;
            }
        }

        echo "Seeding folders..." . "\n";
        return $folders;
    }

    protected function seedLists($folders)
    {
        $faker = Faker\Factory::create();

        $lists = [];

        foreach ($folders as $folder) {
            for ($i = 0; $i < 2; $i++) {
                $list = new \app\models\TaskList();
                $list->list_name = $faker->word;
                $list->description = $faker->paragraph;
                $list->folder_id = $folder->id;
                $list->save(false);

                $lists[] = $list;
            }
        }

        echo "Seeding lists..." . "\n";
        return $lists;
    }

    protected function seedTasks($lists)
    {
        $faker = Faker\Factory::create();
        $tasks = [];

        foreach ($lists as $list) {
            $task_types_id_list = array_column(TaskType::find()->where(['user_id' => $list->folder->space->user_id])->asArray()->all(), 'id');

            for ($i = 0; $i < 2; $i++) {
                $task = new \app\models\Task();
                $task->list_id = $list->id;
                $task->task_header = $faker->sentence;
                $task->task_content = $faker->paragraph;
                $task->priority = $faker->numberBetween(1, 4); //task with priority
                $task->task_type_id = $faker->randomElement($task_types_id_list);
                $task->save(false);

                $tasks[] = $task;
            }

            $task = new \app\models\Task();
            $task->list_id = $list->id;
            $task->task_header = $faker->sentence;
            $task->task_content = $faker->paragraph;
            $task->priority = null; //task without priority
            $task->task_type_id = $faker->randomElement($task_types_id_list);
            $task->save(false);

            $tasks[] = $task;
        }

        echo "Seeding tasks..." . "\n";
        return $tasks;
    }

    protected function seedSubTasks($tasks)
    {
        $faker = Faker\Factory::create();

        $subtasks = [];

        foreach ($tasks as $key => $task) {
            if($key % 2 == 0) continue;

            $list = TaskList::find()->where(['id' => $task->list_id])->one();
            $task_types_id_list = array_column(TaskType::find()->where(['user_id' => $list->folder->space->user_id])->asArray()->all(), 'id');

            for ($i = 0; $i < 2; $i++) {
                $subtask = new \app\models\Task();
                $subtask->list_id = $task->list_id;
                $subtask->task_header = $faker->sentence;
                $subtask->task_content = $faker->paragraph;
                $subtask->parent_id = $task->id;
                $subtask->priority = $faker->numberBetween(1, 4); //subtask with priority
                $subtask->task_type_id = $faker->randomElement($task_types_id_list);
                $subtask->save(false);

                $subtasks[] = $subtask;
            }

            $subtask = new \app\models\Task();
            $subtask->list_id = $task->list_id;
            $subtask->task_header = $faker->sentence;
            $subtask->task_content = $faker->paragraph;
            $subtask->parent_id = $task->id;
            $subtask->priority = null; //subtask without priority
            $subtask->task_type_id = $faker->randomElement($task_types_id_list);
            $subtask->save(false);

            $subtasks[] = $subtask;
        }

        echo "Seeding subtasks..." . "\n";
        return $subtasks;
    }

    public function seedChecklists($tasks)
    {
        $faker = Faker\Factory::create();

        foreach ($tasks as $key => $task) {
            if ($key % 2 == 0) continue;

            for ($i = 0; $i < 2; $i++) {
                $checklist_model = new Checklist();
                $checklist_model->checklist_name = $faker->words(4, true);
                $checklist_model->task_id = $task->id;
                $checklist_model->save();

                for ($j = 0; $j < 3; $j++) {
                    $checklist_item_model = new ChecklistItem();
                    $checklist_item_model->checklist_id = $checklist_model->id;
                    $checklist_item_model->item_name =  $faker->words(4, true);
                    $checklist_item_model->is_completed = $faker->boolean();
                    $checklist_item_model->save();
                }
            }
        }

        echo "Seeding checklists..." . "\n";
    }

    public function seedComments($tasks, $users)
    {
        $faker = Faker\Factory::create();

        foreach ($tasks as $key => $task) {
            if ($key % 2 != 0) continue;

            for ($i = 0; $i < 3; $i++) {
               $comment = new TaskComment();
               $comment->detachBehaviors(); //temporarily turn off auto-timestamps and all other behaviours
               $comment->task_id = $task->id;
               $comment->user_id = $users[0]->id;
               $comment->comment_content = "<p><strong style=\"background-color: rgb(0, 102, 204); color: rgb(255, 255, 255);\">75207207520</strong></p><p>comment $i</p>";
               $comment->created_at = $faker->dateTimeBetween('-1 year', 'now')->getTimestamp();
               $comment->updated_at = $comment->created_at;
               $comment->save(false);
            }
        }

        echo "Seeding comments..." . "\n";
    }

    public function seedTaskTypes($users): array
    {
        $task_types = [];

        $standard_task_types = require Yii::getAlias('@app/config/task_types.php');

        foreach ($users as $user)
        {
            $task_types[$user->id] = [];

            foreach ($standard_task_types as $task_type_data)
            {
                $task_type = new \app\models\TaskType();
                $task_type->user_id = $user->id;

                foreach ($task_type_data as $column => $value)
                {
                    $task_type->{$column} = $value;
                }

                $task_type->save();

                $task_types[$user->id][] = $task_type;
            }
        }

        return $task_types;
    }

    public function actionIndex()
    {
        $users = $this->seedUsers();
        $spaces = $this->seedSpaces($users);
        $folders = $this->seedFolders($spaces);
        $lists = $this->seedLists($folders);
        $this->seedTaskTypes($users);
        $tasks = $this->seedTasks($lists);
        $this->seedSubTasks($tasks);
        $this->seedChecklists($tasks);
        $this->seedComments($tasks, $users);

        echo "Database seeded successfully" . "\n";

        return ExitCode::OK;
    }
}

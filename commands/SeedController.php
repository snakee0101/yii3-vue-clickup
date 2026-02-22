<?php

namespace app\commands;

use app\models\User;
use Yii;
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
            for ($i = 0; $i < 2; $i++) {
                $task = new \app\models\Task();
                $task->list_id = $list->id;
                $task->task_header = $faker->sentence;
                $task->task_content = $faker->paragraph;
                $task->save(false);

                $tasks[] = $task;
            }
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

            for ($i = 0; $i < 2; $i++) {
                $subtask = new \app\models\Task();
                $subtask->list_id = $task->list_id;
                $subtask->task_header = $faker->sentence;
                $subtask->task_content = $faker->paragraph;
                $subtask->parent_id = $task->id;
                $subtask->save(false);

                $subtasks[] = $subtask;
            }
        }

        echo "Seeding subtasks..." . "\n";
        return $subtasks;
    }

    public function actionIndex()
    {
        $users = $this->seedUsers();
        $spaces = $this->seedSpaces($users);
        $folders = $this->seedFolders($spaces);
        $lists = $this->seedLists($folders);
        $tasks = $this->seedTasks($lists);
        $this->seedSubTasks($tasks);

        echo "Database seeded successfully" . "\n";

        return ExitCode::OK;
    }
}

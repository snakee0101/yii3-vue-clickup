<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%tasks}}`.
 */
class m260219_140930_create_tasks_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('tasks', [
            'id' => $this->primaryKey(),
            'list_id' => $this->integer()->notNull(),
            'task_header' => $this->string(255)->notNull(),
            'task_content' => $this->text(),
            'parent_id' => $this->integer(),
            'priority' => $this->tinyInteger()->unsigned(),
            'start_date' => $this->date()->null(),
            'due_date' => $this->date()->null(),
        ]);

        $this->addForeignKey('fk-task-list_id', 'tasks', 'list_id', 'lists','id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-parent_task_id', 'tasks', 'parent_id', 'tasks','id', 'CASCADE', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-parent_task_id', 'tasks');
        $this->dropForeignKey('fk-task-list_id', 'tasks');
        $this->dropTable('tasks');
    }
}

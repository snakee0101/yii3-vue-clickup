<?php

use yii\db\Migration;

/**
 * Handles the creation of table `task_comments`.
 */
class m260307_195147_create_task_comments_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('task_comments', [
            'id' => $this->primaryKey(),
            'task_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'comment_content' => $this->text()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey('fk_task_comments_task_id', 'task_comments', 'task_id', 'tasks', 'id', 'CASCADE');
        $this->addForeignKey('fk_comment_user_id', 'task_comments', 'user_id', 'users', 'id', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_task_comments_task_id', 'task_comments');
        $this->dropForeignKey('fk_comment_user_id', 'task_comments');
        $this->dropTable('task_comments');
    }
}

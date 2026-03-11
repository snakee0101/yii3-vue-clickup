<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%task_statuses}}`.
 */
class m260311_111028_create_task_types_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('task_types', [
            'id' => $this->primaryKey(),
            'type_name' => $this->string()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'icon_name' => $this->string()->notNull(),
            'icon_style' => $this->string()->notNull(),
        ]);

        $this->addForeignKey('fk_task_type_user_id', 'task_types', 'user_id', 'users', 'id', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_task_type_user_id', 'task_types');
        $this->dropTable('task_types');
    }
}

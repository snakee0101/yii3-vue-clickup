<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%checklists}}`.
 */
class m260303_154407_create_checklists_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('checklists', [
            'id' => $this->primaryKey(),
            'checklist_name' => $this->string()->notNull(),
            'task_id' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey('fk-checklists-task_id', 'checklists', 'task_id', 'tasks','id', 'CASCADE', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-checklists-task_id', 'checklists');
        $this->dropTable('checklists');
    }
}

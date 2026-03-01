<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%attachments}}`.
 */
class m260227_132758_create_attachments_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('attachments', [
            'id' => $this->primaryKey(),
            'task_id' => $this->integer()->notNull(),
            'filename' => $this->string(255)->notNull(),
            'size' => $this->integer()->unsigned()->notNull(), //in bytes
            'file_path' => $this->string(255)->notNull(),
            'created_at' => $this->timestamp()->notNull(),
            'updated_at' => $this->timestamp()->notNull(),
        ]);

        $this->addForeignKey('fk-attachments-task_id', 'attachments', 'task_id', 'tasks','id', 'CASCADE', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-attachments-task_id', 'attachments');
        $this->dropTable('attachments');
    }
}

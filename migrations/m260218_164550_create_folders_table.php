<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%folders}}`.
 */
class m260218_164550_create_folders_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('folders', [
            'id' => $this->primaryKey(),
            'folder_name' => $this->string(255)->notNull(),
            'description' => $this->text(),
            'space_id' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey('fk-folders-space_id', 'folders', 'space_id', 'spaces','id', 'CASCADE', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-folders-space_id', 'folders');

        $this->dropTable('folders');
    }
}

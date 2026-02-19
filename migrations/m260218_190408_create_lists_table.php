<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%lists}}`.
 */
class m260218_190408_create_lists_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('lists', [
            'id' => $this->primaryKey(),
            'list_name' => $this->string(255)->notNull(),
            'description' => $this->text(),
            'folder_id' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey('fk-lists-folder_id', 'lists', 'folder_id', 'folders','id', 'CASCADE', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-lists-folder_id', 'lists');

        $this->dropTable('lists');
    }
}

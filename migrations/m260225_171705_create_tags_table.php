<?php

use yii\db\Migration;

/**
 * Handles the creation of table `tags`.
 */
class m260225_171705_create_tags_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('tags', [
            'id' => $this->primaryKey(),
            'tag_name' => $this->string(255)->notNull(),
            'user_id' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey('fk-tags-user_id', 'tags', 'user_id', 'users','id', 'CASCADE', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-tags-user_id', 'tags');
        $this->dropTable('tags');
    }
}

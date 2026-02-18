<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%spaces}}`.
 */
class m260216_220139_create_spaces_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('spaces', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'name' => $this->string(255)->notNull(),
            'description' => $this->text(),
        ]);

        $this->addForeignKey('fk-spaces-user_id', 'spaces', 'user_id', 'users','id', 'CASCADE', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-spaces-user_id', 'spaces');
        $this->dropTable('spaces');
    }
}

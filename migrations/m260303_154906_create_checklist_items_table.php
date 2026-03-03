<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%checklist_items}}`.
 */
class m260303_154906_create_checklist_items_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('checklist_items', [
            'id' => $this->primaryKey(),
            'checklist_id' => $this->integer()->notNull(),
            'item_name' => $this->string()->notNull(),
            'is_completed' => $this->boolean()->defaultValue(false),
        ]);

        $this->addForeignKey('fk-checklist_items-checklist_id', 'checklist_items', 'checklist_id', 'checklists','id', 'CASCADE', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-checklist_items-checklist_id', 'checklist_items');
        $this->dropTable('checklist_items');
    }
}

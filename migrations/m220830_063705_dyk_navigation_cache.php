<?php

use yii\db\Migration;

/**
 * Class m220830_063705_dyk_navigation_cache
 */
class m220830_063705_dyk_navigation_cache extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('dyk_navigation_cache', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255),
            'nav' => $this->getDb()->getSchema()->createColumnSchemaBuilder('mediumtext'),
            'created_at' => $this->dateTime()->defaultExpression('now()'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('dyk_navigation_cache');
    }
}

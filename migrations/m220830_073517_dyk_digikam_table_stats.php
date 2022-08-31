<?php

use yii\db\Migration;

/**
 * Class m220830_073517_dyk_digikam_table_stats
 */
class m220830_073517_dyk_digikam_table_stats extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('dyk_digikam_table_stats', [
            'id' => $this->primaryKey(),
            'digikam_tablename' => $this->string(255),
            'count' => $this->integer(),
            'created_at' => $this->dateTime()->defaultExpression('now()'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('dyk_digikam_table_stats');
    }
}

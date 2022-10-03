<?php

use yii\db\Migration;

/**
 * Class m220908_164351_dyk_last_position
 */
class m220908_164351_dyk_last_position extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('dyk_last_position', [
            'id' => $this->primaryKey(),
            'albumid' => $this->integer(),
            'tagid' => $this->integer(),
            'created_at' => $this->dateTime()->defaultExpression('now()'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('dyk_last_position');
    }
}

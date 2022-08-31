<?php

use yii\db\Migration;

/**
 * Class m220816_143640_dyk_log
 */
class m220816_143640_dyk_log extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('dyk_log', [
            'id' => $this->primaryKey(),
            'created_at' => $this->dateTime()->defaultExpression('now()'),
            'updated_at' => $this->dateTime(),
            'process' => $this->string(255),
            'finished' => $this->boolean()->defaultExpression(0),
            'start_datetime' => $this->dateTime()->defaultExpression('now()'),
            'end_datetime' => $this->dateTime(),
            'notice' => $this->string(4000),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('dyk_log');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220816_143640_dyk_log cannot be reverted.\n";

        return false;
    }
    */
}

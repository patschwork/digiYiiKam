<?php

use yii\db\Migration;

/**
 * Class m220828_174308_dyk_thumbnails_addColumn
 */
class m220828_174308_dyk_thumbnails_addColumn extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('dyk_thumbnails', 'uniqueHash', $this->string(128));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('dyk_thumbnails', 'uniqueHash');
    }
}

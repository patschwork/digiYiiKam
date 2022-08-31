<?php

use yii\db\Migration;

/**
 * Class m220828_170945_dyk_thumbnails_addColumns
 */
class m220828_170945_dyk_thumbnails_addColumns extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('dyk_thumbnails', 'file_extension_not_supported', $this->boolean());
        $this->addColumn('dyk_thumbnails', 'created_at', $this->dateTime()->defaultExpression('now()'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('dyk_thumbnails', 'created_at');
        $this->dropColumn('dyk_thumbnails', 'file_extension_not_supported');
    }

}

<?php

use yii\db\Schema;
use yii\db\Migration;

/**
 * Class dyk_thumbnails
 */
class m220816_103241_dyk_thumbnails extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('dyk_thumbnails', [
            'id' => $this->primaryKey(),
            'thumbnail_creation_ok' => $this->boolean(),
            'digikam_Images_id' => $this->integer()->notNull(),
            'thumbnail_blob' => 'longblob',
            'thumbnail_mime' => $this->string(255),
            'thumbnail_filename' => $this->string(255),
            'parent_image_full_filepath' => $this->string(4000),
            'parent_image_file_extension' => $this->string(255),
            'error' => $this->string(4000),
            'processing_hint' => $this->string(4000),
            'parent_image_mime' => $this->string(255),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('dyk_thumbnails');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220816_103241_thumbnails cannot be reverted.\n";

        return false;
    }
    */
}

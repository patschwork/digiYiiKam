<?php

use yii\db\Migration;

/**
 * Class m220818_120700_dyk_images
 */
class m220818_120700_dyk_images extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('dyk_images', [
            'id' => $this->primaryKey(),
            'image_creation_ok' => $this->boolean(),
            'dyk_thumbnails_id' => $this->integer(),
            'digikam_Images_id' => $this->integer()->notNull(),
            'image_blob' => 'longblob',
            'image_mime' => $this->string(255),
            'image_filename' => $this->string(255),
            'parent_image_full_filepath' => $this->string(4000),
            'parent_image_file_extension' => $this->string(255),
            'error' => $this->string(4000),
            'processing_hint' => $this->string(4000),
            'parent_image_mime' => $this->string(255),
            'not_needed_source_is_a_jpeg' => $this->boolean(),
            'created_at' => $this->dateTime()->defaultExpression('now()'),
        ]);

        $this->addForeignKey('fk-dyk_thumbnails_id', 'dyk_images', 'dyk_thumbnails_id', 'dyk_thumbnails', 'id', 'RESTRICT', 'RESTRICT');
    }
    
    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-dyk_thumbnails_id', 'dyk_images');

        $this->dropTable('dyk_images');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220818_120700_dyk_images cannot be reverted.\n";

        return false;
    }
    */
}

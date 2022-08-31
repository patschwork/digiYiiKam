<?php

use yii\db\Migration;

/**
 * Class m220823_080244_dyk_images_index
 */
class m220823_080244_dyk_images_index extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // unique index
        $this->createIndex(
            'idx_digikam_Images_id',
            'dyk_images',
            'digikam_Images_id',
            true
        );

        $this->createIndex(
            'idx_parent_image_file_extension',
            'dyk_images',
            'parent_image_file_extension'
        );

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex(
            'idx_digikam_Images_id',
            'dyk_images'
        );

        $this->dropIndex(
            'idx_parent_image_file_extension',
            'dyk_images'
        );
    }
}

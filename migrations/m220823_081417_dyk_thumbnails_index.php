<?php

use yii\db\Migration;

/**
 * Class m220823_081417_dyk_thumbnails_index
 */
class m220823_081417_dyk_thumbnails_index extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // unique index
        $this->createIndex(
            'idx_digikam_Images_id',
            'dyk_thumbnails',
            'digikam_Images_id',
            true
        );

        $this->createIndex(
            'idx_parent_image_file_extension',
            'dyk_thumbnails',
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
            'dyk_thumbnails'
        );

        $this->dropIndex(
            'idx_parent_image_file_extension',
            'dyk_thumbnails'
        );
    }

}

<?php

namespace app\models\base;

use Yii;

/**
 * This is the model class for table "dyk_thumbnails".
 *
 * @property int $id
 * @property int|null $thumbnail_creation_ok
 * @property int $digikam_Images_id
 * @property resource|null $thumbnail_blob
 * @property string|null $thumbnail_mime
 * @property string|null $thumbnail_filename
 * @property string|null $parent_image_full_filepath
 * @property string|null $parent_image_file_extension
 * @property string|null $error
 * @property string|null $processing_hint
 * @property string|null $parent_image_mime
 * @property int|null $file_extension_not_supported
 * @property string|null $created_at
 * @property string|null $uniqueHash
 *
 * @property DykImages[] $dykImages
 */
class DykThumbnails extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'dyk_thumbnails';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['thumbnail_creation_ok', 'digikam_Images_id', 'file_extension_not_supported'], 'integer'],
            [['digikam_Images_id'], 'required'],
            [['thumbnail_blob'], 'string'],
            [['created_at'], 'safe'],
            [['thumbnail_mime', 'thumbnail_filename', 'parent_image_file_extension', 'parent_image_mime'], 'string', 'max' => 255],
            [['parent_image_full_filepath', 'error', 'processing_hint'], 'string', 'max' => 4000],
            [['uniqueHash'], 'string', 'max' => 128],
            [['digikam_Images_id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'thumbnail_creation_ok' => 'Thumbnail Creation Ok',
            'digikam_Images_id' => 'Digikam Images ID',
            'thumbnail_blob' => 'Thumbnail Blob',
            'thumbnail_mime' => 'Thumbnail Mime',
            'thumbnail_filename' => 'Thumbnail Filename',
            'parent_image_full_filepath' => 'Parent Image Full Filepath',
            'parent_image_file_extension' => 'Parent Image File Extension',
            'error' => 'Error',
            'processing_hint' => 'Processing Hint',
            'parent_image_mime' => 'Parent Image Mime',
            'file_extension_not_supported' => 'File Extension Not Supported',
            'created_at' => 'Created At',
            'uniqueHash' => 'Unique Hash',
        ];
    }

    /**
     * Gets query for [[DykImages]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDykImages()
    {
        return $this->hasMany(DykImages::className(), ['dyk_thumbnails_id' => 'id']);
    }
}

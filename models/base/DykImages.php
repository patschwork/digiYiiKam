<?php

namespace app\models\base;

use Yii;

/**
 * This is the model class for table "dyk_images".
 *
 * @property int $id
 * @property int|null $image_creation_ok
 * @property int|null $dyk_thumbnails_id
 * @property int $digikam_Images_id
 * @property resource|null $image_blob
 * @property string|null $image_mime
 * @property string|null $image_filename
 * @property string|null $parent_image_full_filepath
 * @property string|null $parent_image_file_extension
 * @property string|null $error
 * @property string|null $processing_hint
 * @property string|null $parent_image_mime
 * @property int|null $not_needed_source_is_a_jpeg
 * @property string|null $created_at
 *
 * @property DykThumbnails $dykThumbnails
 */
class DykImages extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'dyk_images';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['image_creation_ok', 'dyk_thumbnails_id', 'digikam_Images_id', 'not_needed_source_is_a_jpeg'], 'integer'],
            [['digikam_Images_id'], 'required'],
            [['image_blob'], 'string'],
            [['created_at'], 'safe'],
            [['image_mime', 'image_filename', 'parent_image_file_extension', 'parent_image_mime'], 'string', 'max' => 255],
            [['parent_image_full_filepath', 'error', 'processing_hint'], 'string', 'max' => 4000],
            [['dyk_thumbnails_id'], 'exist', 'skipOnError' => true, 'targetClass' => DykThumbnails::className(), 'targetAttribute' => ['dyk_thumbnails_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'image_creation_ok' => 'Image Creation Ok',
            'dyk_thumbnails_id' => 'Dyk Thumbnails ID',
            'digikam_Images_id' => 'Digikam Images ID',
            'image_blob' => 'Image Blob',
            'image_mime' => 'Image Mime',
            'image_filename' => 'Image Filename',
            'parent_image_full_filepath' => 'Parent Image Full Filepath',
            'parent_image_file_extension' => 'Parent Image File Extension',
            'error' => 'Error',
            'processing_hint' => 'Processing Hint',
            'parent_image_mime' => 'Parent Image Mime',
            'not_needed_source_is_a_jpeg' => 'Not Needed Source Is A Jpeg',
            'created_at' => 'Created At',
        ];
    }

    /**
     * Gets query for [[DykThumbnails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDykThumbnails()
    {
        return $this->hasOne(DykThumbnails::className(), ['id' => 'dyk_thumbnails_id']);
    }
}

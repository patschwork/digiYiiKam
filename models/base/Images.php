<?php

namespace app\models\base;

use Yii;

/**
 * This is the model class for table "Images".
 *
 * @property int $id
 * @property int|null $album
 * @property string $name
 * @property int $status
 * @property int $category
 * @property string|null $modificationDate
 * @property int|null $fileSize
 * @property string|null $uniqueHash
 * @property int|null $manualOrder
 *
 * @property Albums $album0
 * @property Albums[] $albums
 * @property ImageComments[] $imageComments
 * @property ImageCopyright[] $imageCopyrights
 * @property ImageHistory $imageHistory
 * @property ImageInformation $imageInformation
 * @property ImageMetadata $imageMetadata
 * @property ImagePositions $imagePositions
 * @property ImageProperties[] $imageProperties
 * @property ImageRelations[] $imageRelations
 * @property ImageRelations[] $imageRelations0
 * @property ImageTagProperties[] $imageTagProperties
 * @property ImageTags[] $imageTags
 * @property Tags[] $tags
 * @property Tags[] $tags0
 * @property VideoMetadata $videoMetadata
 */
class Images extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Images';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_digikam');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['album', 'status', 'category', 'fileSize', 'manualOrder'], 'integer'],
            [['name', 'status', 'category'], 'required'],
            [['name'], 'string'],
            [['modificationDate'], 'safe'],
            [['uniqueHash'], 'string', 'max' => 128],
            [['album'], 'exist', 'skipOnError' => true, 'targetClass' => Albums::className(), 'targetAttribute' => ['album' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'album' => 'Album',
            'name' => 'Name',
            'status' => 'Status',
            'category' => 'Category',
            'modificationDate' => 'Modification Date',
            'fileSize' => 'File Size',
            'uniqueHash' => 'Unique Hash',
            'manualOrder' => 'Manual Order',
        ];
    }

    /**
     * Gets query for [[Album0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAlbum0()
    {
        return $this->hasOne(Albums::className(), ['id' => 'album']);
    }

    /**
     * Gets query for [[Albums]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAlbums()
    {
        return $this->hasMany(Albums::className(), ['icon' => 'id']);
    }

    /**
     * Gets query for [[ImageComments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getImageComments()
    {
        return $this->hasMany(ImageComments::className(), ['imageid' => 'id']);
    }

    /**
     * Gets query for [[ImageCopyrights]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getImageCopyrights()
    {
        return $this->hasMany(ImageCopyright::className(), ['imageid' => 'id']);
    }

    /**
     * Gets query for [[ImageHistory]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getImageHistory()
    {
        return $this->hasOne(ImageHistory::className(), ['imageid' => 'id']);
    }

    /**
     * Gets query for [[ImageInformation]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getImageInformation()
    {
        return $this->hasOne(ImageInformation::className(), ['imageid' => 'id']);
    }

    /**
     * Gets query for [[ImageMetadata]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getImageMetadata()
    {
        return $this->hasOne(ImageMetadata::className(), ['imageid' => 'id']);
    }

    /**
     * Gets query for [[ImagePositions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getImagePositions()
    {
        return $this->hasOne(ImagePositions::className(), ['imageid' => 'id']);
    }

    /**
     * Gets query for [[ImageProperties]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getImageProperties()
    {
        return $this->hasMany(ImageProperties::className(), ['imageid' => 'id']);
    }

    /**
     * Gets query for [[ImageRelations]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getImageRelations()
    {
        return $this->hasMany(ImageRelations::className(), ['object' => 'id']);
    }

    /**
     * Gets query for [[ImageRelations0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getImageRelations0()
    {
        return $this->hasMany(ImageRelations::className(), ['subject' => 'id']);
    }

    /**
     * Gets query for [[ImageTagProperties]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getImageTagProperties()
    {
        return $this->hasMany(ImageTagProperties::className(), ['imageid' => 'id']);
    }

    /**
     * Gets query for [[ImageTags]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getImageTags()
    {
        return $this->hasMany(ImageTags::className(), ['imageid' => 'id']);
    }

    /**
     * Gets query for [[Tags]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTags()
    {
        return $this->hasMany(Tags::className(), ['icon' => 'id']);
    }

    /**
     * Gets query for [[Tags0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTags0()
    {
        return $this->hasMany(Tags::className(), ['id' => 'tagid'])->viaTable('ImageTags', ['imageid' => 'id']);
    }

    /**
     * Gets query for [[VideoMetadata]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVideoMetadata()
    {
        return $this->hasOne(VideoMetadata::className(), ['imageid' => 'id']);
    }
}

<?php

namespace app\models\base;

use Yii;

/**
 * This is the model class for table "Albums".
 *
 * @property int $id
 * @property int $albumRoot
 * @property string $relativePath
 * @property string|null $date
 * @property string|null $caption
 * @property string|null $collection
 * @property int|null $icon
 * @property string|null $modificationDate
 *
 * @property AlbumRoots $albumRoot0
 * @property Images $icon0
 * @property Images[] $images
 */
class Albums extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Albums';
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
            [['albumRoot', 'relativePath'], 'required'],
            [['albumRoot', 'icon'], 'integer'],
            [['relativePath', 'caption', 'collection'], 'string'],
            [['date', 'modificationDate'], 'safe'],
            [['albumRoot'], 'exist', 'skipOnError' => true, 'targetClass' => AlbumRoots::className(), 'targetAttribute' => ['albumRoot' => 'id']],
            [['icon'], 'exist', 'skipOnError' => true, 'targetClass' => Images::className(), 'targetAttribute' => ['icon' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'albumRoot' => 'Album Root',
            'relativePath' => 'Relative Path',
            'date' => 'Date',
            'caption' => 'Caption',
            'collection' => 'Collection',
            'icon' => 'Icon',
            'modificationDate' => 'Modification Date',
        ];
    }

    /**
     * Gets query for [[AlbumRoot0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAlbumRoot0()
    {
        return $this->hasOne(AlbumRoots::className(), ['id' => 'albumRoot']);
    }

    /**
     * Gets query for [[Icon0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIcon0()
    {
        return $this->hasOne(Images::className(), ['id' => 'icon']);
    }

    /**
     * Gets query for [[Images]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getImages()
    {
        return $this->hasMany(Images::className(), ['album' => 'id']);
    }
}

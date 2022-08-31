<?php

namespace app\models\base;

use Yii;

/**
 * This is the model class for table "Tags".
 *
 * @property int $id
 * @property int|null $pid
 * @property string $name
 * @property int|null $icon
 * @property string|null $iconkde
 *
 * @property Images $icon0
 * @property ImageTagProperties[] $imageTagProperties
 * @property ImageTags[] $imageTags
 * @property Images[] $images
 * @property TagProperties[] $tagProperties
 */
class Tags extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Tags';
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
            [['pid', 'icon'], 'integer'],
            [['name'], 'required'],
            [['name', 'iconkde'], 'string'],
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
            'pid' => 'Pid',
            'name' => 'Name',
            'icon' => 'Icon',
            'iconkde' => 'Iconkde',
        ];
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
     * Gets query for [[ImageTagProperties]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getImageTagProperties()
    {
        return $this->hasMany(ImageTagProperties::className(), ['tagid' => 'id']);
    }

    /**
     * Gets query for [[ImageTags]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getImageTags()
    {
        return $this->hasMany(ImageTags::className(), ['tagid' => 'id']);
    }

    /**
     * Gets query for [[Images]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getImages()
    {
        return $this->hasMany(Images::className(), ['id' => 'imageid'])->viaTable('ImageTags', ['tagid' => 'id']);
    }

    /**
     * Gets query for [[TagProperties]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTagProperties()
    {
        return $this->hasMany(TagProperties::className(), ['tagid' => 'id']);
    }
}

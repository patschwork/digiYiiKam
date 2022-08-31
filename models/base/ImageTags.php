<?php

namespace app\models\base;

use Yii;

/**
 * This is the model class for table "ImageTags".
 *
 * @property int $imageid
 * @property int $tagid
 *
 * @property Images $image
 * @property Tags $tag
 */
class ImageTags extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ImageTags';
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
            [['imageid', 'tagid'], 'required'],
            [['imageid', 'tagid'], 'integer'],
            [['imageid', 'tagid'], 'unique', 'targetAttribute' => ['imageid', 'tagid']],
            [['imageid'], 'exist', 'skipOnError' => true, 'targetClass' => Images::className(), 'targetAttribute' => ['imageid' => 'id']],
            [['tagid'], 'exist', 'skipOnError' => true, 'targetClass' => Tags::className(), 'targetAttribute' => ['tagid' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'imageid' => 'Imageid',
            'tagid' => 'Tagid',
        ];
    }

    /**
     * Gets query for [[Image]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getImage()
    {
        return $this->hasOne(Images::className(), ['id' => 'imageid']);
    }

    /**
     * Gets query for [[Tag]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTag()
    {
        return $this->hasOne(Tags::className(), ['id' => 'tagid']);
    }
}

<?php

namespace app\models\base;

use Yii;

/**
 * This is the model class for table "ImageInformation".
 *
 * @property int $imageid
 * @property int|null $rating
 * @property string|null $creationDate
 * @property string|null $digitizationDate
 * @property int|null $orientation
 * @property int|null $width
 * @property int|null $height
 * @property string|null $format
 * @property int|null $colorDepth
 * @property int|null $colorModel
 *
 * @property Images $image
 */
class ImageInformation extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ImageInformation';
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
            [['imageid'], 'required'],
            [['imageid', 'rating', 'orientation', 'width', 'height', 'colorDepth', 'colorModel'], 'integer'],
            [['creationDate', 'digitizationDate'], 'safe'],
            [['format'], 'string'],
            [['imageid'], 'unique'],
            [['imageid'], 'exist', 'skipOnError' => true, 'targetClass' => Images::className(), 'targetAttribute' => ['imageid' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'imageid' => 'Imageid',
            'rating' => 'Rating',
            'creationDate' => 'Creation Date',
            'digitizationDate' => 'Digitization Date',
            'orientation' => 'Orientation',
            'width' => 'Width',
            'height' => 'Height',
            'format' => 'Format',
            'colorDepth' => 'Color Depth',
            'colorModel' => 'Color Model',
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
}

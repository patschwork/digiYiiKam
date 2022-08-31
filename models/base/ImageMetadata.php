<?php

namespace app\models\base;

use Yii;

/**
 * This is the model class for table "ImageMetadata".
 *
 * @property int $imageid
 * @property string|null $make
 * @property string|null $model
 * @property string|null $lens
 * @property float|null $aperture
 * @property float|null $focalLength
 * @property float|null $focalLength35
 * @property float|null $exposureTime
 * @property int|null $exposureProgram
 * @property int|null $exposureMode
 * @property int|null $sensitivity
 * @property int|null $flash
 * @property int|null $whiteBalance
 * @property int|null $whiteBalanceColorTemperature
 * @property int|null $meteringMode
 * @property float|null $subjectDistance
 * @property int|null $subjectDistanceCategory
 *
 * @property Images $image
 */
class ImageMetadata extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ImageMetadata';
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
            [['imageid', 'exposureProgram', 'exposureMode', 'sensitivity', 'flash', 'whiteBalance', 'whiteBalanceColorTemperature', 'meteringMode', 'subjectDistanceCategory'], 'integer'],
            [['make', 'model', 'lens'], 'string'],
            [['aperture', 'focalLength', 'focalLength35', 'exposureTime', 'subjectDistance'], 'number'],
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
            'make' => 'Make',
            'model' => 'Model',
            'lens' => 'Lens',
            'aperture' => 'Aperture',
            'focalLength' => 'Focal Length',
            'focalLength35' => 'Focal Length 35',
            'exposureTime' => 'Exposure Time',
            'exposureProgram' => 'Exposure Program',
            'exposureMode' => 'Exposure Mode',
            'sensitivity' => 'Sensitivity',
            'flash' => 'Flash',
            'whiteBalance' => 'White Balance',
            'whiteBalanceColorTemperature' => 'White Balance Color Temperature',
            'meteringMode' => 'Metering Mode',
            'subjectDistance' => 'Subject Distance',
            'subjectDistanceCategory' => 'Subject Distance Category',
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

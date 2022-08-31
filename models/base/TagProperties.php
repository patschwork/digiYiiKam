<?php

namespace app\models\base;

use Yii;

/**
 * This is the model class for table "TagProperties".
 *
 * @property int|null $tagid
 * @property string|null $property
 * @property string|null $value
 *
 * @property Tags $tag
 */
class TagProperties extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'TagProperties';
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
            [['tagid'], 'integer'],
            [['property', 'value'], 'string'],
            [['tagid'], 'exist', 'skipOnError' => true, 'targetClass' => Tags::className(), 'targetAttribute' => ['tagid' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'tagid' => 'Tagid',
            'property' => 'Property',
            'value' => 'Value',
        ];
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

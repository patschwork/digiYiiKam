<?php

namespace app\models\base;

use Yii;

/**
 * This is the model class for table "dyk_last_position".
 *
 * @property int $id
 * @property int|null $albumid
 * @property int|null $tagid
 * @property string|null $created_at
 */
class DykLastPosition extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'dyk_last_position';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['albumid', 'tagid'], 'integer'],
            [['created_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'albumid' => 'Albumid',
            'tagid' => 'Tagid',
            'created_at' => 'Created At',
        ];
    }
}

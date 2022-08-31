<?php

namespace app\models\base;

use Yii;

/**
 * This is the model class for table "TagsTree".
 *
 * @property int $id
 * @property int $pid
 */
class TagsTree extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'TagsTree';
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
            [['id', 'pid'], 'required'],
            [['id', 'pid'], 'integer'],
            [['id', 'pid'], 'unique', 'targetAttribute' => ['id', 'pid']],
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
        ];
    }
}

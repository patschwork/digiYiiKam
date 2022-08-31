<?php

namespace app\models\base;

use Yii;

/**
 * This is the model class for table "AlbumRoots".
 *
 * @property int $id
 * @property string|null $label
 * @property int $status
 * @property int $type
 * @property string|null $identifier
 * @property string|null $specificPath
 *
 * @property Albums[] $albums
 */
class AlbumRoots extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'AlbumRoots';
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
            [['label', 'identifier', 'specificPath'], 'string'],
            [['status', 'type'], 'required'],
            [['status', 'type'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'label' => 'Label',
            'status' => 'Status',
            'type' => 'Type',
            'identifier' => 'Identifier',
            'specificPath' => 'Specific Path',
        ];
    }

    /**
     * Gets query for [[Albums]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAlbums()
    {
        return $this->hasMany(Albums::className(), ['albumRoot' => 'id']);
    }
}

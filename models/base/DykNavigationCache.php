<?php

namespace app\models\base;

use Yii;

/**
 * This is the model class for table "dyk_navigation_cache".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $nav
 * @property string|null $created_at
 */
class DykNavigationCache extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'dyk_navigation_cache';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nav'], 'string'],
            [['created_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'nav' => 'Nav',
            'created_at' => 'Created At',
        ];
    }
}

<?php

namespace app\models\base;

use Yii;

/**
 * This is the model class for table "dyk_digikam_table_stats".
 *
 * @property int $id
 * @property string|null $digikam_tablename
 * @property int|null $count
 * @property string|null $created_at
 */
class DykDigikamTableStats extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'dyk_digikam_table_stats';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['count'], 'integer'],
            [['created_at'], 'safe'],
            [['digikam_tablename'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'digikam_tablename' => 'Digikam Tablename',
            'count' => 'Count',
            'created_at' => 'Created At',
        ];
    }
}

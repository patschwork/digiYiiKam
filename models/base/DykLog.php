<?php

namespace app\models\base;

use Yii;

/**
 * This is the model class for table "dyk_log".
 *
 * @property int $id
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property string|null $process
 * @property int|null $finished
 * @property string|null $start_datetime
 * @property string|null $end_datetime
 * @property string|null $notice
 */
class DykLog extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'dyk_log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at', 'start_datetime', 'end_datetime'], 'safe'],
            [['finished'], 'integer'],
            [['process'], 'string', 'max' => 255],
            [['notice'], 'string', 'max' => 4000],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'process' => 'Process',
            'finished' => 'Finished',
            'start_datetime' => 'Start Datetime',
            'end_datetime' => 'End Datetime',
            'notice' => 'Notice',
        ];
    }
}

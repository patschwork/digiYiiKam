<?php

namespace app\models;

use Yii;

/**
 * {@inheritdoc}
 */
class DykThumbnails extends \app\models\base\DykThumbnails
{
    public static function getDbName(): string
    {
        return self::getDb()->createCommand("SELECT DATABASE()")->queryScalar();
    }

    public static function tableName(): string
    {
        return '{{%' . self::getDbName() . '.'.self::getDb()->getSchema()->getRawTableName(parent::tableName()).'}}';
    }

    public function getImages()
    {
        return $this->hasOne(Images::class, ['id' => 'digikam_Images_id']);
    }
}
?>
<?php

namespace app\models;

use Yii;

/**
 * {@inheritdoc}
 */
class Images extends \app\models\base\Images
{
    public static function getDbName(): string
    {
        return self::getDb()->createCommand("SELECT DATABASE()")->queryScalar();
    }

    public static function tableName(): string
    {
        return '{{%' . self::getDbName() . '.'.self::getDb()->getSchema()->getRawTableName(parent::tableName()).'}}';
    }

    public function getAlbums()
    {
        return $this->hasMany(Albums::className(), ['id' => 'album']);
    }

    public function getDykThumbnails()
    {
        return $this->hasOne(DykThumbnails::class, ['digikam_Images_id' => 'id']);
    }
}
?>
<?php

/** @var yii\web\View $this */

use yii\helpers\VarDumper;
use yii\helpers\Html;

$this->title = 'Albums';
?>

<?php

foreach($albums_with_id as $key=>$value)
{
    echo Html::a(
        $value,
        ['gallery/album', 'albumid' => $key],
        [
            'class' => 'btn btn-xs btn-success btn-block',
        ]
    );
}
?>
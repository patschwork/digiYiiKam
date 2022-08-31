<?php

/** @var yii\web\View $this */

$this->title = 'Check';
?>

<?php
echo \yii\bootstrap4\Progress::widget([
    'percent' => $percent,
    'barOptions' => ['class' => 'progress-bar'],
    'options' => ['class' => 'active progress-striped'],
]);
echo "$already_processed / $to_be__processed";
?>
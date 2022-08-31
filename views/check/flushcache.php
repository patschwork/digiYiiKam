<?php

/** @var yii\web\View $this */

use yii\bootstrap4\Alert;

$this->title = 'Flush cache';


if ($msg1 !== "")
{
    echo Alert::widget([
        'options' => [
            'class' => 'alert-success',
        ],
        'body' => $msg1,
    ]);
}
if ($msg2 !== "")
{
    echo Alert::widget([
        'options' => [
            'class' => 'alert-success',
        ],
        'body' => $msg2,
    ]);
}
?>


<?php

/** @var yii\web\View $this */

use yii\helpers\VarDumper;
use yii\helpers\Html;

$this->title = 'Album: ' . $album_name;
?>

<?php

VarDumper::dump($items, 100, true);

// echo "<h1>$album_name</h1>";
// echo dosamigos\gallery\Gallery::widget(['items' => $items]);

?>
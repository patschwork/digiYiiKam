<?php

/** @var yii\web\View $this */

use yii\helpers\VarDumper;
use yii\helpers\Html;

$this->title = 'Album: ' . $album_name;
?>

<?php

// VarDumper::dump($items, 100, true);

// echo "<h1>$album_name</h1>";
// echo dosamigos\gallery\Gallery::widget(['items' => $items]);

?>


<style>
/* Pen-specific styles */
* {
box-sizing: border-box;
}
/* body {
font-size: 1.25rem;
font-family: sans-serif;
line-height: 150%;
text-shadow: 0 2px 2px #b6701e;
} */

section {
color: #fff;
text-align: center;
}

div {
height: 100%;
}

article {
position: absolute;
top: 50%;
left: 50%;
transform: translate(-50%, -50%);
width: 100%;
padding: 20px;
}

h1 {
font-size: 1.75rem;
margin: 0 0 0.75rem 0;
}

/* Pattern styles */
.container {
display: table;
width: 100%;
}

.left-half {
background-color: #ff9e2c;
position: absolute;
left: 0px;
width: 25%;
}

.right-half {
background-color: #b6701e;
position: absolute;
right: 0px;
width: 75%;
/* height: 80%;  new */
}
</style>

<section class="container">
<div class="left-half">
<article>
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
</article>
</div>
<div class="right-half">
<article>
<h1>TEST</h1>
  <?= dosamigos\gallery\Gallery::widget(['items' => $items]); ?>
</article>
</div>
</section>


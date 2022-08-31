<?php

/** @var yii\web\View $this */

use yii\helpers\VarDumper;
use yii\helpers\Html;

$this->title = 'Album: ' . $album_name;
?>

<?php

// VarDumper::dump($items, 100, true);

// echo "<h2>$album_name</h2>";
// echo dosamigos\gallery\Gallery::widget(['items' => $items]);

$expl=explode("/", $album_name);
unset($expl[0]);
VarDumper::dump($expl, 10, true);

?>

<style>
.container {
   height: auto;
   overflow: hidden;
}

.right {
    width: 50%;
    float: right;
    padding-left: 50px;
}

.left {
    float: none; /* not needed, just for clarification */
    /* the next props are meant to keep this block independent from the other floated one */
    width: auto;
    overflow: hidden;
    background: #aafed6;

}

.blueimp-gallery > .description_btn {
  position: absolute;
  top: 70px;
  left: 50px;
}


.blueimp-gallery > .description, .blueimp-gallery > .example {
  position: absolute;
  top: 100px;
  left: 50px;
  color: #000000;
  display: none;
  background: #c0c0c0;
}
.blueimp-gallery-controls > .description, .blueimp-gallery-controls > .example {
  display: block;
}
​​
</style>
<script>
function toggleEXIFBox() {
  var x = document.getElementsByClassName("description")[0];
  if (x.style.display === "none") {
    x.style.display = "block";
  } else {
    x.style.display = "none";
  }
} 
</script>

<div class="container">
    <div class="right">

    <?= 
            dosamigos\gallery\Gallery::widget(
                [
                    'items' => $items,
                    'clientEvents' => [
                        'onslide' => 'function(index, slide) {
                            console.log(index);
                            // alert(slide);
                            // var text = index,
                            var text = this.list[index].getAttribute("exif"),
                            node = this.container.find(".description");
                            node.empty();
                            if (text) {
                                // node[0].appendChild(document.createTextNode(text));
                                // node[0].appendChild(document.createElement("p").innerHTML("Hello World <span style=\"color:#BA0000\">error</span> "));
                                // node[0].appendChild.createElement("p").innerHTML("Hello World <span style=\"color:#BA0000\">error</span> "));
                                node[0].innerHTML = text;
                            }
                        }'
                ]
                ]
            ); 
        ?>
    </div>
    <div class="left">
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
    </div>
</div>
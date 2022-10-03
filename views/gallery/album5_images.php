<?php

/** @var yii\web\View $this */

// NUR DIE BILDER DER GALLERY

?>


<style>
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

/* // {... heart-tag */
.blueimp-gallery > .heart-tag
{
  position: absolute;
  top: 15px;
  left: 15px;
  margin: 0 40px 0 0;
  font-size: 20px;
  line-height: 30px;
  color: #FF0000;
  text-shadow: 0 0 2px #000;
  opacity: 0.8;
  display: none;
}

.blueimp-gallery > .heart-tag 
{
  padding: 15px;
  right: 50px;
  left: auto;
  margin: -15px;
  font-size: 30px;
  text-decoration: none;
  cursor: pointer;
  background: url(../img/play-pause.png) 0 0 no-repeat;
}

.blueimp-gallery > .heart-tag:hover
{
  color: #fff;
  opacity: 1;
}

.blueimp-gallery-controls > .heart-tag
{
  display: block;
  /* Fix z-index issues (controls behind slide element) on Android: */
  -webkit-transform: translateZ(0);
     -moz-transform: translateZ(0);
      -ms-transform: translateZ(0);
       -o-transform: translateZ(0);
          transform: translateZ(0);
}

.blueimp-gallery > .heart-tag
{
  -webkit-user-select: none;
   -khtml-user-select: none;
     -moz-user-select: none;
      -ms-user-select: none;
          user-select: none;
}
/* // ...} */

.blueimp-gallery > .download-jpg
{
  position: absolute;
  top: 15px;
  left: 100px;
  margin: 0 40px 0 0;
  font-size: 20px;
  line-height: 30px;
  color: #FF0000;
  text-shadow: 0 0 2px #000;
  opacity: 0.8;
  display: none;
}

.blueimp-gallery > .download-jpg 
{
  padding: 15px;
  right: 100px;
  left: auto;
  margin: -15px;
  font-size: 30px;
  text-decoration: none;
  cursor: pointer;
  background: url(../img/play-pause.png) 0 0 no-repeat;
}
.blueimp-gallery-controls > .download-jpg
{
  display: block;
  /* Fix z-index issues (controls behind slide element) on Android: */
  -webkit-transform: translateZ(0);
     -moz-transform: translateZ(0);
      -ms-transform: translateZ(0);
       -o-transform: translateZ(0);
          transform: translateZ(0);
}

.blueimp-gallery > .download-jpg
{
  -webkit-user-select: none;
   -khtml-user-select: none;
     -moz-user-select: none;
      -ms-user-select: none;
          user-select: none;
}

.blueimp-gallery > .download-src
{
  position: absolute;
  top: 15px;
  left: 100px;
  margin: 0 40px 0 0;
  font-size: 20px;
  line-height: 30px;
  color: #FF0000;
  text-shadow: 0 0 2px #000;
  opacity: 0.8;
  display: none;
}

.blueimp-gallery > .download-src 
{
  padding: 15px;
  right: 146px;
  left: auto;
  margin: -15px;
  font-size: 30px;
  text-decoration: none;
  cursor: pointer;
  background: url(../img/play-pause.png) 0 0 no-repeat;
}
.blueimp-gallery-controls > .download-src
{
  display: block;
  /* Fix z-index issues (controls behind slide element) on Android: */
  -webkit-transform: translateZ(0);
     -moz-transform: translateZ(0);
      -ms-transform: translateZ(0);
       -o-transform: translateZ(0);
          transform: translateZ(0);
}

.blueimp-gallery > .download-src
{
  -webkit-user-select: none;
   -khtml-user-select: none;
     -moz-user-select: none;
      -ms-user-select: none;
          user-select: none;
}

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
// {... heart-tag
localStorage.removeItem("heart-tag-states");
// ...}

</script>

<div class="loading-indicator" style="z-index: 1; position: absolute; left: 50%; top: 20%;">
  <?php
  use kartik\spinner\Spinner;
  echo Spinner::widget([
        // 'preset' => 'large',
      'align' => 'center',
      'color' => 'white',
      'pluginOptions' =>
        [
          'scale' => 60,
          'length'=> 38,
          'width'=> 17, 
          'radius'=> 45, 
          'color' => '#ffffff',
        ],
    ]);
  ?>
</div>
<div class="container-fluid">
    <?= 
            dosamigos\gallery\Gallery::widget(
                [
                    'items' => $items,
                    'clientEvents' => [
                        'onslide' => 'function(index, slide) {
                            console.log(index);
                            var text = this.list[index].getAttribute("exif");
                            node = this.container.find(".description");
                            node.empty();
                            if (text) {
                              node[0].innerHTML = text;
                            }
                            
                            node_download_jpg = this.container.find(".download-jpg");
                            var download_jpg = this.list[index].getAttribute("download-jpg");
                            node_download_jpg.empty();
                            if (download_jpg) {
                              node_download_jpg[0].innerHTML = download_jpg;
                            }
                            
                            node_download_src = this.container.find(".download-src");
                            var download_src = this.list[index].getAttribute("download-src");
                            node_download_src.empty();
                            if (download_src) {
                              node_download_src[0].innerHTML = download_src;
                            }                            
                            
                            // {... heart-tag
                            node_ht = this.container.find(".heart-tag");
                            var heart_tag = this.list[index].getAttribute("heart-tag");
                            var digikam_Images_id = this.list[index].getAttribute("digikam_Images_id");
                            if (heart_tag)
                            {
                                if (JSON.parse(localStorage.getItem("heart-tag-states")))
                                {
                                    arr = JSON.parse(localStorage.getItem("heart-tag-states"));
                                    if (arr[digikam_Images_id])
                                    {
                                      // restore saved state from user interaction
                                      node_ht[0].innerHTML = arr[digikam_Images_id];
                                    }
                                    else
                                    {
                                      // state from server side/database
                                      node_ht[0].innerHTML = heart_tag;
                                    }  
                                }
                                else
                                {
                                  // state from server side/database
                                  node_ht[0].innerHTML = heart_tag;
                                }
                          }
                          // ...}
                        }'
                ]
                ]
            ); 
        ?>
</div>


<?php
$script='$(document).ready(function() {
  //alert("$(document).ready fired");
  $(".loading-indicator").css("display","none");
});'; 
$this->registerJs($script, \yii\web\View::POS_LOAD);
?>
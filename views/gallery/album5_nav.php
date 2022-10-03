<?php

/** @var yii\web\View $this */

use kartik\sidenav\SideNav;


$this->title = 'Album: ' . $album_name;
$this->params['fluid'] = true;
?>
<br>
<br>
<br>


<style>
.container-fluid {
   height: auto;
   overflow: hidden;
}

.right {
    width: 70%;
    float: right;
    padding-left: 10px;
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
  <div class="right">

  <iframe id="iframe_1" width="100%" height="800px" src="index.php?r=gallery%2Fimagesonlytest&albumid=1005"></iframe>
    
    </div>
    <div class="left">
    <?php
    $items = [
      ['label' => '1005', 'url' => '#1005',  'active' =>  false],
      ['label' => '1006', 'url' => '#1006',  'active' =>  false],
      ['label' => '1009', 'url' => '#1009',  'active' =>  false],
      ['label' => '1010', 'url' => '#1010',  'active' =>  false],
    ];

    $utils = new \vendor\digiyiikam\Utils();
    $items1 = $utils->prepare_navsidebar_data($useAnchorLinks = true);

    echo SideNav::widget([
      'type' => SideNav::TYPE_INFO,
      'encodeLabels' => false,
      'heading' => '<i class="fas fa-map-signs"></i> Navigation',
      'iconPrefix' => 'fa fa-',
      'indItem' => "",
      'items' => $items1,
    ]);        
        ?>
    </div>
</div>


<?php
$script='$(document).ready(function() {
  //alert("$(document).ready fired");
  $(".loading-indicator").css("display","none");
});'; 
$this->registerJs($script, \yii\web\View::POS_LOAD);
?>

<?php
$script2='// ANKER LINKS ABFANGEN
$(document).ready(function() {
                $(document).click(function (event) {
                    // alert(event);
                    console.log(event);
                    href=event.target.hash;
                    albumid = href.replace("#", "");
                    targetURL = "index.php?r=gallery%2Fimagesonlytest&albumid=" + albumid;
                    console.log(targetURL);
                    document.getElementById("iframe_1").src = targetURL;
                    // alert(targetURL);
                });
            });'; 
$this->registerJs($script2, \yii\web\View::POS_READY);
?>
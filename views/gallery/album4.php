<?php

/** @var yii\web\View $this */

use app\models\DykNavigationCache;
use kartik\sidenav\SideNav;


$this->title = 'Album: ' . $album_name;
$this->params['fluid'] = true;
?>
<br>
<br>
<br>

<?php
// $expl=explode("/", $album_name);
// unset($expl[0]);
// VarDumper::dump($expl, 10, true);
?>

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
    <div class="left">
    <?php
    $utils = new \vendor\digiyiikam\Utils();
    // Yii::debug(strlen(json_encode($items1)), 'Dev album4');
    // $m = new DykNavigationCache();
    // $m->name = 'filesystem';
    // $m->nav = json_encode($items1);
    // $m->save();
    
    
    $l_m = DykNavigationCache::findOne(['name' => 'filesystem']);
    if (!is_null($l_m))
    {
      $nav_data = $l_m->nav;
      $items1 = unserialize($nav_data);
    }
    else
    {
      $items1 = $utils->prepare_navsidebar_data();
      $m = new DykNavigationCache();
      $m->name = 'filesystem';
      $m->nav = serialize($items1);
      $m->save();
    }

    $items2 = array();
    $l_m_2 = DykNavigationCache::findOne(['name' => 'tags']);
    if (!is_null($l_m_2))
    {
      $nav_data = $l_m_2->nav;
      $items2 = unserialize($nav_data);
    }
    else
    {
      array_push($items2, ['label' => "Tags", 'icon' => "tags", 'items' => (new \vendor\digiyiikam\utils())->TagItemsTree()]);
      $m = new DykNavigationCache();
      $m->name = 'tags';
      $m->nav = serialize($items2);
      $m->save();
    }

    $items = array_merge($items1, $items2);

    echo SideNav::widget([
      'type' => SideNav::TYPE_INFO,
      'encodeLabels' => false,
      'heading' => '<i class="fas fa-map-signs"></i> Navigation',
      'iconPrefix' => 'fa fa-',
      'indItem' => "",
      'items' => $items,
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
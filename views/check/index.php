<?php

/** @var yii\web\View $this */

use yii\helpers\VarDumper;

$this->title = 'Check';
?>

<?php

VarDumper::dump($collectionPaths, 10, true);
VarDumper::dump($chkCanReachCollectionFiles, 100, true);
?>
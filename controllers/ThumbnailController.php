<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\DykThumbnails;
use yii\web\Response;

class ThumbnailController extends Controller
{

    // Example:   http://localhost:8888/index.php?r=thumbnail/getimage&id=55242
    // Example 2: http://localhost:8888/index.php?r=thumbnail/getimage&id=55242&resizetomax=250
    public function actionGetimage($id = 1, $resizetomax = 150)
    {
        $response = Yii::$app->getResponse();
        $qry = DykThumbnails::findOne($id);
        if (!is_null($qry))
        {
            $response->headers->set('Content-Type', $qry->thumbnail_mime);
            if ($resizetomax>0)
            {
                $image = imagecreatefromstring($qry->thumbnail_blob);
                $image = imagescale($image, $resizetomax, $resizetomax);
                ob_start();
                imagejpeg($image);
                $contents = ob_get_contents();
                ob_end_clean();
                imagedestroy($image);
                $response->data = $contents;
            }
            else
            {
                $response->data = $qry->thumbnail_blob;
            }
        }
        $response->format = Response::FORMAT_RAW;
        return $response->send();
    }

}

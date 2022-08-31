<?php

namespace app\controllers;

use app\models\base\Albums;
use app\models\DykLog;
use app\models\DykNavigationCache;
use InvalidArgumentException;
use Yii;
use yii\base\InvalidParamException;
use yii\caching\Cache;
use yii\filters\AccessControl;
use yii\web\Controller;

class CheckController extends Controller
{

    public function actionIndex()
    {
        $config = new \vendor\digiyiikam\config();
        $collectionPaths = $config->getCollectionPaths();
        
        $checker = new \vendor\digiyiikam\checker();
        $chkCanReachCollectionFiles = $checker->chkCanReachCollectionFiles($collectionPaths, 5, true);


        $param = [
            'collectionPaths' => $collectionPaths
           ,'chkCanReachCollectionFiles' => $chkCanReachCollectionFiles
        ];
        return $this->render('index', $param);
    }

    public function checkFileAccess()
    {
        // $modelAlbums = new Albums();
        // $qry = $modelAlbums->findAll();
    }

    public function actionRunningprocesses()
    {
        // $max = Laptop::find()->max('price');
        $max_id = DykLog::find()->where(['LIKE', 'notice','Process is running ('])->max('id');
        if (!is_null($max_id))
        {
            $qry = DykLog::findOne($max_id);
            if ($qry->finished == 0)
            {
                $notice = $qry->notice;
                $process_steps = explode("(",$notice)[1];
                $process_steps = str_replace(")", "", $process_steps);
                $already_processed = intval(explode("/", $process_steps)[0]);
                $to_be__processed = intval(explode("/", $process_steps)[1]);
                $percent = intval(($already_processed / $to_be__processed)*100);
                $param = [
                    'percent' => $percent,
                    'already_processed' => $already_processed,
                    'to_be__processed' => $to_be__processed,
                ];
                return $this->render('runningprocesses', $param);
                
            }
            
        }
    }
    
    public function actionFlushcache()
    {
        $utils = new \vendor\digiyiikam\utils();

        $msg1 = $utils->flushCache('cache');
        $msg2 = $utils->eraseNavCache() >0 ? "NavCache erased" : "";
        $param = [
             'msg1' => $msg1
            ,'msg2' => $msg2
        ];
        return $this->render('flushcache', $param);
        
    }
}

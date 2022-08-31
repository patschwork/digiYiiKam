<?php

namespace app\commands;


use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;


class UtilsController extends Controller
{

    public function actionInitThumbnailsDatabase()
    {
        $cnt = \app\models\DykThumbnails::find()->count('id');
        if ($cnt > 0)
        {
            $msg = $this->ansiFormat("\n".'There are alredy items in the table.', Console::FG_YELLOW);
            $msg .= $this->ansiFormat("\n".'To add new items, please use the the command "add-thumbnails-database"', Console::FG_YELLOW);
            $msg .= $this->ansiFormat("\n".'Abort.'."\n", Console::FG_YELLOW);
            echo "$msg";
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $config = new \vendor\digiyiikam\config();
        $collectionPaths = $config->getCollectionPaths();
        
        $utils = new \vendor\digiyiikam\utils();
        $utils->init_thumbnails_table($collectionPaths);
        $finished = $this->ansiFormat('FINISHED', Console::FG_GREEN);
        echo "Status: $finished";
        return ExitCode::OK;
    }    
    
    public function actionAddThumbnailsDatabase()
    {
        $config = new \vendor\digiyiikam\config();
        $collectionPaths = $config->getCollectionPaths();
        
        $utils = new \vendor\digiyiikam\utils();
        $utils->add_to_thumbnails_table($collectionPaths);
        $finished = $this->ansiFormat('FINISHED', Console::FG_GREEN);
        echo "Status: $finished";
        return ExitCode::OK;
    }

    // public function signal_handler($signal) {
    //     echo $signal;
    //     switch($signal) {
    //         case SIGTERM:
    //             echo "Caught SIGTERM\n";
    //             exit;
    //         case SIGKILL:
    //             echo "Caught SIGKILL\n";
    //             exit;
    //         case SIGINT:
    //             echo "Caught SIGINT\n";
    //             exit;
    //     }
    // }

    public function actionGenerateThumbnails()
    {

        // register_shutdown_function([$this, 'signal_handler']);                  // Handle END of script

        // declare(ticks = 1);

        // pcntl_signal(SIGTERM, [$this, 'signal_handler']);
        // pcntl_signal(SIGINT, [$this, 'signal_handler']);

        while(1)
        {
            $start = $this->ansiFormat('START', Console::FG_BLUE);
            $finished = $this->ansiFormat('FINISHED', Console::FG_GREEN);
            $error = $this->ansiFormat('ERROR', Console::BG_RED, Console::FG_YELLOW);
            echo "\nStatus: $start";
            $utils = new \vendor\digiyiikam\utils();
            $result = $utils->generate_thumbnails($max_errors = 3000, $withEcho = true);
            if (!$result)
            {
                echo "\nStatus: $error";
                return ExitCode::UNSPECIFIED_ERROR;
            }
            else
            {
                echo "\nStatus: $finished";
                return ExitCode::OK;
            }
        }
    }
}

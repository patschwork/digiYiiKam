<?php

namespace vendor\digiyiikam;

use Yii;

class Config
{
    /**
     * digiKam can hold many collections. 
     * This is the pendant to the digiKam entry in the database table AlbumRoots
     * The id should match to the items in the database to be in sync
     * @return array
     * @example returnValue[1]='/path1/', returnValue[2]='/path2/'
     */
    public function getCollectionPaths()
    {
        $returnValue = array();
        array_push($returnValue, 'DUMMY'); // ID = 0
        if (str_contains(gethostname(), "Air-von-Patrick")) array_push($returnValue, '/Volumes/Macintosh HD/Volumes/Macintosh HD/System/Volumes/Data/media/remote/smb_srv_bilder/'); // ID = 1
        if (str_contains(gethostname(), "patsch3")) array_push($returnValue, '/media/remote/smb_srv_bilder/'); // ID = 1
        if (str_contains(gethostname(), "srv")) array_push($returnValue, '/var/www/digiYiiKam/bilder/'); // ID = 1
        if (isset(Yii::$app->params['collectionPaths'])) if (is_array(Yii::$app->params['collectionPaths'])) $returnValue = Yii::$app->params['collectionPaths'];
        return $returnValue;
    }    
    
    /**
     * Returns the path to the base working dir for converting RAW image files.
     * @return string Path (without trailing path seperator)
     */
    public function getWorkingDirForRAWConverting()
    {
        $returnValue = '/tmp/digiyiikam';
        return $returnValue;
    }
}
<?php

namespace vendor\digiyiikam;

use app\models\base\Albums;

class Checker
{
    /**
     * Check, if the application can access the files (images) in the collections paths
     * @param $collectionPaths (see vendor/digiyiikam/config.php/getCollectionPaths())
     * @param $countToCheck defaults to end after 10 items (sucessful, unsuccessful) for each collection
     * @return array
     */
    public function chkCanReachCollectionFiles($collectionPaths, $countToCheck = 50, $withMimeType = false)
    {
        $returnValue = array();
        foreach($collectionPaths as $key=>$value)
        {
            // get the Album with Images from the digiKam database
            // the $key is the identifier
            // $value is the path
            $modelAlbums = Albums::find()->with('images')->where(['albumRoot' => $key])->limit($countToCheck)->all();

            foreach($modelAlbums as $modelAlbumsRow)
            {
                foreach($modelAlbumsRow->images as $imagesRow)
                {
                    // $value aka the collection path ends with a /
                    // Albumns.relativePath starts with a / but does not end with a /
                    // Images.name does not have a / at all
                    $fullpath = $value . substr($modelAlbumsRow->relativePath, 1, 999999) . "/" . $imagesRow->name;
                    $file_readable = is_readable($fullpath);
                    $file_mimetype = $withMimeType ? mime_content_type($fullpath) : 'MIME Type not checked';
                    $status = [
                        'fullpath'                     => $fullpath
                       ,'file_readable'                => $file_readable
                       ,'file_mimetype'                => $file_mimetype
                       ,'digiYiiKam_CollectionPath ID' => $key
                       ,'digiYiiKam_CollectionPath'    => $value
                    ];
                    array_push($returnValue, $status);
                }
            }
        }
        return $returnValue;
    }
}
<?php

namespace vendor\digiyiikam;
use Yii;
use app\models\Albums;
use app\models\DykDigikamTableStats;
use app\models\DykImages;
use app\models\DykLastPosition;
use app\models\Images;
use app\models\DykLog;
use app\models\DykNavigationCache;
use app\models\DykThumbnails;
use app\models\ImageInformation;
use app\models\Tags;
use yii\helpers\Url;
use app\models\ImageTags;
use InvalidArgumentException;
use yii\caching\Cache;

class Utils
{

    public function get_translated_fullpath_for_source_image($imageid)
    {
        $modelImages = Images::find()
            ->cache(7200)
            ->joinWith('albums')->where(['IN','Images.id', array($imageid)])->one();
        
        $config = new \vendor\digiyiikam\config();
        $collectionPaths = $config->getCollectionPaths();
        $fullpath = $collectionPaths[$modelImages->albums[0]->albumRoot] . substr($modelImages->albums[0]->relativePath, 1, 999999) . "/" . $modelImages->name;
        return $fullpath;
    }

    /**
     * Init table dyk_thumbnails
     * Reads all Images from the digiKam database and put this into the digiYiiKam database table dyk_thumbnails
     * This seperates the doing from generating and see, which already have been processed.
     */
    public function init_thumbnails_table($collectionPaths)
    {
        $returnValue = array();
        foreach($collectionPaths as $key=>$value)
        {
            // get the Album with Images from the digiKam database
            // the $key is the identifier
            // $value is the path
            $modelAlbums = Albums::find()
                ->with('images')->where(['albumRoot' => $key])->all();

            foreach($modelAlbums as $modelAlbumsRow)
            {
                foreach($modelAlbumsRow->images as $imagesRow)
                {
                    // $value aka the collection path ends with a /
                    // Albumns.relativePath starts with a / but does not end with a /
                    // Images.name does not have a / at all
                    $fullpath = $value . substr($modelAlbumsRow->relativePath, 1, 999999) . "/" . $imagesRow->name;
                    $file_readable = is_readable($fullpath);
                    $file_mimetype = mime_content_type($fullpath);
                    $extension = pathinfo($fullpath, PATHINFO_EXTENSION);
                    $status = [
                        'fullpath'                     => $fullpath
                       ,'file_readable'                => $file_readable
                       ,'file_mimetype'                => $file_mimetype
                       ,'digiYiiKam_CollectionPath ID' => $key
                       ,'digiYiiKam_CollectionPath'    => $value
                    ];
                    
                    $DykThumbnails_Model = new DykThumbnails();

                    $DykThumbnails_Model->digikam_Images_id = $imagesRow->id;
                    $DykThumbnails_Model->parent_image_full_filepath = $fullpath;
                    $DykThumbnails_Model->parent_image_file_extension = $extension;
                    $DykThumbnails_Model->parent_image_mime = $file_mimetype;
                    $DykThumbnails_Model->processing_hint = json_encode($status);
                    $DykThumbnails_Model->uniqueHash = $imagesRow->uniqueHash;

                    $DykThumbnails_Model->save();

                }
            }
        }
        return $returnValue;
    }

    public function add_to_thumbnails_table($collectionPaths)
    {
        $returnValue = array();
        foreach($collectionPaths as $key=>$value)
        {
            $query = \app\models\Images::find()->joinWith('albums')->joinWith('dykThumbnails', false, 'LEFT JOIN')->where(['not', ['Images.album' => null]])->andWhere(['dyk_thumbnails.id' => null, 'albumRoot' => $key])->all();
            foreach($query as $imagesRow)
            {
                $fullpath = $value . substr($imagesRow->albums[0]->relativePath, 1, 999999) . "/" . $imagesRow->name;
                $file_readable = is_readable($fullpath);
                $file_mimetype = mime_content_type($fullpath);
                $extension = pathinfo($fullpath, PATHINFO_EXTENSION);

                $time = 'now';
                $timezone = 'Europe/Berlin';
                $dateTime = new \DateTime('now', new \DateTimeZone($timezone));
                $timeParsed = $dateTime->format('c');

                $status = [
                    'fullpath'                     => $fullpath
                   ,'file_readable'                => $file_readable
                   ,'file_mimetype'                => $file_mimetype
                   ,'digiYiiKam_CollectionPath ID' => $key
                   ,'digiYiiKam_CollectionPath'    => $value
                   ,'item_added_at'                => $timeParsed
                ];
                
                $DykThumbnails_Model = new DykThumbnails();

                $DykThumbnails_Model->digikam_Images_id = $imagesRow->id;
                $DykThumbnails_Model->parent_image_full_filepath = $fullpath;
                $DykThumbnails_Model->parent_image_file_extension = $extension;
                $DykThumbnails_Model->parent_image_mime = $file_mimetype;
                $DykThumbnails_Model->processing_hint = json_encode($status);
                $DykThumbnails_Model->uniqueHash = $imagesRow->uniqueHash;

                $DykThumbnails_Model->save();
            }
        }
        return $returnValue;
    }


    /**
     * Fix orientation
     * Taken from: https://stackoverflow.com/a/26698203
     */
    private function image_fix_orientation($path, $givenOrientationFromDigiKam = null)
    {
        $image = imagecreatefromjpeg($path);
        $exif = exif_read_data($path);
        
        if (!is_null($givenOrientationFromDigiKam))
        {
            $orientation = $givenOrientationFromDigiKam;
        }
        else
        {
            if (empty($exif['Orientation']))
            {
                return false;
            }
            $orientation = $exif['Orientation'];
        }
    
        switch ($orientation)
        {
            case 3:
                $image = imagerotate($image, 180, 0);
                break;
            case 6:
                $image = imagerotate($image, - 90, 0);
                break;
            case 8:
                $image = imagerotate($image, 90, 0);
                break;
        }
    
        imagejpeg($image, $path);
    
        return true;
    }



    /**
     * Generates the thumbnails from the source collections paths and uploads them to the digiYiiKam database table dyk_thumbnails.
     * Function init_thumbnails_table must be run before to create rows into the taable.
     * Only images, which does not have the flag thumbnail_creation_ok will be processed.
     * Log entries are created into digiYiiKam database table dyk_log. 
     * CURRENTLY ONLY JPG IS PROCESSED
     * @return Nothing
     */
    public function generate_thumbnails($max_errors = 10, $withEcho = true)
    {
        $utils = new \vendor\digiyiikam\utils();

        // New log item
        $DykLog_Model = new DykLog();
        $DykLog_Model->process = Yii::$app->controller->id;
        $DykLog_Model->notice = "Process is started...";
        $DykLog_Model->save();
        $logid = $DykLog_Model->id;
        
        try
        {
            // $DykThumbnails_Model = DykThumbnails::find()->where(['thumbnail_creation_ok' => NULL])->andWhere(['parent_image_file_extension' => 'CR2', 'digikam_Images_id'=>98886])->all();
            // $DykThumbnails_Model = DykThumbnails::find()->where(['thumbnail_creation_ok' => NULL])->andWhere(['parent_image_file_extension' => 'CR2'])->all();
            // $DykThumbnails_Model = DykThumbnails::find()->where(['thumbnail_creation_ok' => NULL])->andWhere(['parent_image_file_extension' => 'DNG'])->all();
            $DykThumbnails_Model = DykThumbnails::find()->where(['thumbnail_creation_ok' => NULL])->andWhere(['file_extension_not_supported' => NULL])->all();
            // $DykThumbnails_Model = DykThumbnails::find()->where(['thumbnail_creation_ok' => NULL])->andWhere(['digikam_Images_id'=>92496])->all();
            $cntRowsToProcess = count($DykThumbnails_Model);
            $cnt = 0;
            $i = 0;
            $errorcnt = 0;
            $error_last_printed = "";

            foreach($DykThumbnails_Model as $row)
            {
                if ($errorcnt>$max_errors)
                {
                    return false;
                }
                $cnt++;
                $i++;
                if ($i == 10)
                {
                    $DykLog_Model = DykLog::findOne($logid);
                    $DykLog_Model->notice = "Process is running ($cnt/$cntRowsToProcess)";
                    $DykLog_Model->updated_at = new \yii\db\Expression('NOW()');
                    $DykLog_Model->save();
                    if ($withEcho)
                    {
                        echo "\n" . "Process is running ($cnt/$cntRowsToProcess)";
                        if ($errorcnt>0)
                        {
                            if ($error_last_printed !== "$errorcnt")
                            {
                                $error = (new \yii\console\Controller(null, null))->ansiFormat('Error count: '. $errorcnt, \yii\helpers\Console::FG_RED);
                                echo "\n\t" . "$error";
                                $error_last_printed = "$errorcnt";   
                            }
                        }
                    }
                    $i = 0;
                }

                $this_thumbnail = DykThumbnails::findOne($row->id);
                $this_thumbnail->error = NULL;
                try
                {

                    $givenOrientationFromDigiKam = null;
                    $doNotOrientationCorrection = false;
                    if (strtoupper($row->parent_image_file_extension) == "DNG") $doNotOrientationCorrection = true;
                    $ImageInformation_Model = ImageInformation::findOne(['imageid' => $row->digikam_Images_id]);
                    if (!is_null($ImageInformation_Model))
                    {
                        $givenOrientationFromDigiKam = $ImageInformation_Model->orientation;
                    }
                    $orientationCorrectionOnImage = false;

                    $orgFile = null;
                    if ($row->parent_image_mime == 'image/jpeg')
                    {
                        $orgFile = 'file://' . $row->parent_image_full_filepath;

                        // only if blanks...
                        if (strpos($row->parent_image_full_filepath," ")) $orgFile = $this->src_path_with_blanks_thumbnail_pre_handling($row->parent_image_full_filepath);
                    }
                    else
                    {
                        // VORBEREITUNG:
                        // http://localhost:8888/index.php?r=gallery/getfullimagefromdatabase&digikam_Images_id=119161
                        // Url::to(['gallery/getfullimagefromdatabase', 'digikam_Images_id' => $row->digikam_Images_id])
                        
                        $check = DykImages::find()->select(['id'])->where(['digikam_Images_id' => $row->digikam_Images_id])->one();
                        if (!is_null($check))
                        {
                            $orgFile = Url::to(['gallery/getfullimagefromdatabase', 'digikam_Images_id' => $row->digikam_Images_id]);
                        }
                        else
                        {
                            // arghhh, not the right place, but... ;-)
                            // @ToDo
                            $resultArr = $utils->convert_raw_to_jpg($row->digikam_Images_id);

                            if (!$resultArr["error"])
                            {
                                $produced_jpg = $resultArr["generated_jpg_file"];
                                // echo "<pre>$produced_jpg</pre>";
                    
                                $this_image = DykImages::findOne(['digikam_Images_id' => $row->digikam_Images_id]);
                                is_null($this_image) ? $this_image = new DykImages() : null;

                                if (!$doNotOrientationCorrection) $orientationCorrectionOnImage = $this->image_fix_orientation($produced_jpg, $givenOrientationFromDigiKam);
                                
                                $this_image->image_blob = file_get_contents($produced_jpg);
                                $this_image->image_mime = mime_content_type($produced_jpg);
                    
                                $info = pathinfo($produced_jpg);
                                $file_name =  basename($produced_jpg,'.'.$info['extension']);
                                $this_image->image_filename = "$file_name".".jpg";
                                $this_image->image_creation_ok = 1;
                                
                                $this_image->dyk_thumbnails_id = $row->id;
                                $this_image->digikam_Images_id = $row->digikam_Images_id;
                                $this_image->parent_image_full_filepath = $resultArr["parent_image_full_filepath"];
                                $this_image->parent_image_file_extension = $resultArr["parent_image_file_extension"];
                                $this_image->parent_image_mime = $resultArr["parent_image_mime"];
                                $this_image->processing_hint = "Creation successful";
                                // $this_image->error = "";
                                $this_image->not_needed_source_is_a_jpeg = 0;
                                
                                if ($this_image->save())
                                {
                                    $OK = true;
                                    $orgFile = 'file://' . $produced_jpg;
                                }
                                else
                                {
                                    // That is not bulletproof. No error handling... 
                                    $OK = false;
                                }
                            }
                            else
                            {
                                // That is not bulletproof. No error handling... 
                                $OK = false;
                            }
                        }
                    }
                    if (!is_null($orgFile))
                    {
                        $thumbUrl = Yii::$app->thumbnailer->get($orgFile);
                        if (Yii::$app instanceof \yii\console\Application)
                        {
                            $thumbnail_local = $thumbUrl;
                        }
                        else
                        {
                            $thumbnail_local = Yii::getAlias('@webroot') ."/". 'assets/thumbnails/' . explode("/",$thumbUrl)[5] ."/". explode("/",$thumbUrl)[6];
                        }

                        // $givenOrientationFromDigiKam = null;
                        // $ImageInformation_Model = ImageInformation::findOne(['imageid' => $row->digikam_Images_id]);
                        // if (!is_null($ImageInformation_Model))
                        // {
                        //     $givenOrientationFromDigiKam = $ImageInformation_Model->orientation;
                        // }
                        if (!$doNotOrientationCorrection) if (!$orientationCorrectionOnImage) $this->image_fix_orientation($thumbnail_local, $givenOrientationFromDigiKam);

                        $this_thumbnail->thumbnail_blob = file_get_contents($thumbnail_local);
                        $this_thumbnail->thumbnail_mime = mime_content_type($thumbnail_local);
                        
                        $info = pathinfo($thumbnail_local);
                        $file_name =  basename($thumbnail_local,'.'.$info['extension']);
                        $this_thumbnail->thumbnail_filename = "$file_name"."_thumb.jpg";
                        $this_thumbnail->thumbnail_creation_ok = 1;
                    }
                    else
                    {
                        $this_thumbnail->error = 'Only JPG as source is supported or no preview (raw-2-jpg) image in the DigiYiiKam database found.';
                        $this_thumbnail->file_extension_not_supported = 1;
                    }

                    if ($this_thumbnail->save())
                    {
                        $dummy = "";
                    }
                    else
                    {
                        $errorcnt++;
                        $modelError = json_encode($this_thumbnail->getErrors());
                        $this_thumbnail = DykThumbnails::findOne($row->id);
                        $this_thumbnail->error = "ERROR 1:\n".$modelError;
                        $this_thumbnail->save();
                    }
                }
                catch (\Exception $e)
                {
                    if (isset($this_thumbnail))
                    {
                        $errorcnt++;
                        $this_thumbnail->error = $this_thumbnail->error . ($this_thumbnail->error !== "" ? "\n" : "") . "ERROR 2:\n$e";
                        $this_thumbnail->save();
                    }
                    // else
                    // {
                    //     $this_thumbnail_errornous2 = DykThumbnails::findOne($row->id);
                    //     $this_thumbnail_errornous2->error = "ERROR 2: $e";
                    //     $this_thumbnail_errornous2->save();
                    // }
                }
            }
        }
        catch (\Exception $e)
        {
            $DykLog_Model = DykLog::findOne($logid);
            $DykLog_Model->end_datetime = new \yii\db\Expression('NOW()');
            $DykLog_Model->updated_at = new \yii\db\Expression('NOW()');
            $DykLog_Model->notice = "ERROR:\n$e";
            $DykLog_Model->finished = 0;
            $DykLog_Model->save();
            return false;
        }



        $DykLog_Model = DykLog::findOne($logid);
        $DykLog_Model->end_datetime = new \yii\db\Expression('NOW()');
        $DykLog_Model->updated_at = new \yii\db\Expression('NOW()');
        $DykLog_Model->notice = "Processed $cntRowsToProcess items";
        $DykLog_Model->finished = 1;
        $DykLog_Model->save();

        return true;
    }

    /**
     * Will flatten a model-find-asArray variable to a key-only array to use a IN-Where condition in other model-find queries.
     * See: https://stackoverflow.com/a/1320156
     */
    public function flatten(array $array) {
        $return = array();
        array_walk_recursive($array, function($a) use (&$return) { $return[] = $a; });
        return $return;
    }

    public function unflatten($data) {
        $output = [];
        foreach ($data as $key => $value) {
          $parts = explode('/', $key);
          $nested = &$output;
          while (count($parts) > 1) {
            $nested = &$nested[array_shift($parts)];
            if (!is_array($nested)) $nested = [];
          }
          $nested[array_shift($parts)] = $value;
        }
        return $output;
    }

    public function get_Albums_With_Images($AlbumsId = NULL)
    {
        $DykThumbnails_Model = DykThumbnails::find()
            ->cache(7200)        
            ->select(['digikam_Images_id'])
            ->where(['thumbnail_creation_ok' => 1])->asArray(true)->all();
        $modelAlbums = Albums::find()
            ->cache(7200)
            ->select(['Albums.id','relativePath','albumRoot'])
            ->distinct()
            ->innerJoinWith('images')
            ->where(
                ['IN','Images.id', $this->flatten($DykThumbnails_Model)]
            )
        ;        
        if (!is_null($AlbumsId))
        {
            $modelAlbums->andWhere(['Albums.id' => $AlbumsId]);
        }
        $modelAlbums = $modelAlbums->all();
        return $modelAlbums;
    }

    /**
     * Get a list of all images (with albums) from DigiKam, which already have a thumbnail in the DigiYiiKam database
     * @return \yii\db\ActiveRecord
     */
    public function get_Images_From_Albums($AlbumsId = NULL)
    {
        $DykThumbnails_Model = DykThumbnails::find()
        ->cache(7200)
        ->select(['digikam_Images_id'])->where(['thumbnail_creation_ok' => 1])->asArray(true)->all();
        
        $modelImages = Images::find()
            ->cache(7200)
            ->joinWith('albums')
            ->where(
                ['IN','Images.id', $this->flatten($DykThumbnails_Model)]
            )
        ;
        if (!is_null($AlbumsId))
        {
            $modelImages->andWhere(['album' => $AlbumsId]);
        }
        $modelImages = $modelImages->all();
        return $modelImages;
    }    
    
    /**
     * Get a list of all images (with tags) from DigiKam, which already have a thumbnail in the DigiYiiKam database
     * @return \yii\db\ActiveRecord
     * 
     * Test in yii shell:
     * (new \vendor\digiyiikam\utils())->get_Images_From_Tags(); 
     */
    public function get_Images_From_Tags($TagId = NULL)
    {
        $DykThumbnails_Model = DykThumbnails::find()
            ->cache(7200)
            ->select(['digikam_Images_id'])->where(['thumbnail_creation_ok' => 1])->asArray(true)->all();
       
        $modelImages = Images::find()
            ->cache(7200)
            ->joinWith('imageTags')
            ->where(
                ['IN','Images.id', $this->flatten($DykThumbnails_Model)]
            )
            ->andWhere(['not', ['album' => NULL]])
        ;
        if (!is_null($TagId))
        {
            $modelImages->andWhere(['tagid' => $TagId]);
        }
        $modelImages = $modelImages->all();
        return $modelImages;
    }

    /**
     * Prints needed and evaluated versions of the converting tools.
     * Test in yii shell:
     * (new \vendor\digiyiikam\utils())->print_needed_convert_tools_and_versions(); 
     */
    public function print_needed_convert_tools_and_versions()
    {
        echo "Print needed conversion tools:\n\n";
        $fileextensions = array("CR3", "CR2", "DNG", "NEF", "MP4");
        foreach($fileextensions as $key=>$fileextension)
        {
            echo "Fileextension: $fileextension\n";
            $res = $this->get_cmd_for_image_converting($fileextension, "/tmp/sourcefile.$fileextension", "/tmp/workingdir");
            if ($res["used_tool"] == null)
            {
                print_r("Not supported!"."\n");
            }
            else
            {
                print_r("Needed tools: ".$res["used_tool"]."\n");
                print_r("Needed versions: ".$res["minimum_version_tool"]."\n");
                print_r("Command to evaluate versions: ".$res["cmd_get_version_tool"]."\n");
                
                if ($res["cmd_get_version_tool"] !== null)
                {
                    $get_version_commands_arr = explode(";", $res["cmd_get_version_tool"]);
                    foreach($get_version_commands_arr as $key=>$command)
                    {
                        $out = "";
                        $rescode = "";
                        exec($command, $out, $rescode);
                        print_r("Version command output:\n");
                        print_r("\t->\t".$out[0]);
                        print_r("\n");
                        if ($rescode <> 0) print_r("Returncode: ".$rescode."\n");
                    }
                }
            }
            echo "----------------------------------------\n\n";
        }
    }

    /**
     * This function checks based on the fileextension of a RAW file which commandline tool will be executed for
     * converting the source file to a JPG image. The execution is not done.
     * @param string fileextension Extension of the file (currently supported are CR3, CR2, NEF, DNG).
     * @param string src_filename Filename of the RAW file on the local filesystem (e.g. IMG_0123.CR2).
     * @param string workingDir Where is the RAW file on the local filesystem? (e.g. /tmp/digiyiikam/dykworkingdir_62fffa9bda604). It will not be checked if existing.
     * @return array arr["cmd"]                         = [string] String ready to be executed on the OS (Linux/MAC).
     *               arr["converted_image_fullpath"]    = [string] This will be the expected converted image file as result.
     *               arr["used_tool"]                   = [string] The used commandline tool (to convert the image). If multiple tools are used, they are separated with a semicolon.
     *               arr["minimum_version_tool"]        = [string] Minimum version needed of the used commandline tool. If multiple tools are used, they are separated with a semicolon.
     *               arr["cmd_get_version_tool"]        = [string] How to get the version info from the commandline tool. If multiple tools are used, they are separated with a semicolon.
     *               arr["not_needed_output"][n]        = [array of string] Dump files not needed anymore (maybe part of the converting).
     *               arr["fileextension_not_supported"] = [bool] Flag indicating, if fileextension (RAW image type) is supported.
     * Test in yii shell:
     * $utils = new \vendor\digiyiikam\utils();
     * $utils->get_cmd_for_image_converting("CR3", "/tmp/sourcefile.CR3", "/tmp/workingdir");
     */
    public function get_cmd_for_image_converting($fileextension, $src_filename, $workingDir)
    {
        $returnValues = array();
        $returnValues["cmd"] = null;
        $returnValues["converted_image_fullpath"] = null;
        $returnValues["used_tool"] = null;
        $returnValues["minimum_version_tool"] = null;
        $returnValues["cmd_get_version_tool"] = null;
        $returnValues["not_needed_output"][0] = null;
        $returnValues["not_needed_output"][1] = null;
        $returnValues["fileextension_not_supported"] = true;
        
        $fileextension = strtoupper($fileextension);
        $filename_wo_extension = pathinfo($src_filename)['filename'];
        if ($fileextension == "CR3" || $fileextension == "NEF")
        {
            $returnValues["cmd"] = "exiftool -b -JpgFromRaw -w jpg -ext $fileextension $src_filename -execute -v -tagsfromfile $src_filename -ext jpg $workingDir/.";
            $returnValues["used_tool"] = "exiftool";
            $returnValues["minimum_version_tool"] = "12.40";
            $returnValues["cmd_get_version_tool"] = "exiftool -ver";
            $returnValues["converted_image_fullpath"] = "$workingDir/$filename_wo_extension".".jpg";
            $returnValues["not_needed_output"][0] = "$workingDir/$filename_wo_extension".".jpg_original";
            $returnValues["fileextension_not_supported"] = false;
        }
        if ($fileextension == "CR2")
        {
            $returnValues["cmd"] = "exiv2 -ep $src_filename";
            $returnValues["used_tool"] = "exiv2";
            $returnValues["minimum_version_tool"] = "0.27.5";
            $returnValues["cmd_get_version_tool"] = "exiv2 -V";
            $returnValues["converted_image_fullpath"] = "$workingDir/$filename_wo_extension"."-preview3.jpg"; // temp-file produced by exiv2
            $returnValues["not_needed_output"][0] = "$workingDir/$filename_wo_extension"."-preview1.jpg";
            $returnValues["not_needed_output"][1] = "$workingDir/$filename_wo_extension"."-preview2.tif";
            $returnValues["fileextension_not_supported"] = false;
        }
        if ($fileextension == "DNG")
        {
            // This works on MAC with ImageMagick, but not on LINUX PopOS! 22.04
            // $returnValues["cmd"] = "convert $src_filename $filename_wo_extension".".jpg";
            // $returnValues["used_tool"] = "convert"; // ImageMagick
            // $returnValues["minimum_version_tool"] = "7.1.0-46";
            // $returnValues["cmd_get_version_tool"] = "convert -version";
            // $returnValues["converted_image_fullpath"] = "$workingDir/$filename_wo_extension".".jpg";
            // $returnValues["fileextension_not_supported"] = false;
            
            $tiff_file = "$filename_wo_extension.tiff";
            $expected_file = "$filename_wo_extension.dng.tiff.jpg";
            $returnValues["cmd"] = "dcraw -T -o 0 -w $src_filename;convert $tiff_file $expected_file";
            $returnValues["used_tool"] = "dcraw;convert"; // dcraw and ImageMagick convert
            $returnValues["minimum_version_tool"] = "9.28;6.9.11-60";
            $returnValues["cmd_get_version_tool"] = "dcraw | grep -i 'Raw photo decoder \"dcraw\" v';convert -version | grep -i 'Version: ImageMagick '";
            $returnValues["converted_image_fullpath"] = "$workingDir/$expected_file";
            $returnValues["not_needed_output"][0] = "$workingDir/$tiff_file";
            $returnValues["fileextension_not_supported"] = false;
        }
        return $returnValues;
    }

    /**
     * This function will produce a JPG (in a local temporary folder) of different RAW source files.
     * The function goes step by step (create folder, remove temp. files during the process).
     * It will not cleanup the produced files afterwards!
     * @param integer $ImageId ID from digiKam image row. The correct path will be evaluated.
     * @return array arr["error"]                       = [boolean] True of false if a error happened.
     *               arr["error_msg"]                   = [array] Error messages line by line (stacktrace or additional information).
     *               arr["generated_jpg_file"]          = [string] Produced full_filepath to JPG file.
     *               arr["parent_image_full_filepath"]  = [string] Full_filepath absolute to the source file.
     *               arr["parent_image_file_extension"] = [string] File extension of the source file.
     *               arr["parent_image_mime"]           = [string] MIME type of the source file.
     */
    public function convert_raw_to_jpg($ImageId)
    {
        $returnValues = array();

        $returnValues["error"] = false;
        $returnValues["error_msg"] = array();
        $returnValues["generated_jpg_file"] = null;
        $returnValues["parent_image_full_filepath"] = null;
        $returnValues["parent_image_file_extension"] = null;
        $returnValues["parent_image_mime"] = null;

        $parent_image_full_filepath = $this->get_translated_fullpath_for_source_image($ImageId);
        $parent_image_file_extension = pathinfo($parent_image_full_filepath, PATHINFO_EXTENSION);
        $parent_image_mime = mime_content_type($parent_image_full_filepath);

        $returnValues["parent_image_full_filepath"] = $parent_image_full_filepath;
        $returnValues["parent_image_file_extension"] = $parent_image_file_extension;
        $returnValues["parent_image_mime"] = $parent_image_mime;

        $filename = pathinfo($parent_image_full_filepath)['basename'];
        $filename_wo_extension = pathinfo($parent_image_full_filepath)['filename'];

        $config = new \vendor\digiyiikam\Config();
        $workingDirBase = $config->getWorkingDirForRAWConverting();

        $workingDir = $workingDirBase.'/'.uniqid("dykworkingdir_");
        $tmpFile = "$workingDir/$filename"; // RAW file

        $tmpFile_jpg = null;
        $filename_for_generated_file = "$workingDir/$filename_wo_extension".".jpg"; // final file produced by exiftool

        $cmdArray = array();
        
        // Prepare next exec
        $cmdArray[1]['output'] = null;
        $cmdArray[1]['resultCode'] = null;
        $cmdArray[1]['cmd'] = "mkdir -p $workingDir";
        $cmdArray[1]['step_description'] = "Crate working dir";
        
        // Prepare next exec
        $cmdArray[2]['output'] = null;
        $cmdArray[2]['resultCode'] = null;
        $cmdArray[2]['cmd'] = "rm -v -f $filename_for_generated_file";
        $cmdArray[2]['step_description'] = "Remove files already created (just in case)";
        
        // Prepare next exec
        $cmdArray[3]['output'] = null;
        $cmdArray[3]['resultCode'] = null;
        $cmdArray[3]['cmd'] = "cp \"$parent_image_full_filepath\" $workingDir";
        $cmdArray[3]['step_description'] = "Copy RAW source file to local working dir";
        
        // Prepare next exec
        $conv_tool_arr = $this->get_cmd_for_image_converting($parent_image_file_extension, $filename, $workingDir);
        $cmdArray[4]['output'] = null;
        $cmdArray[4]['resultCode'] = -1;
        $cmdArray[4]['cmd'] = $conv_tool_arr["cmd"];
        $cmdArray[4]['step_description'] = "Convert RAW image to JPG";
        
        if ($conv_tool_arr["fileextension_not_supported"])
        {
            $returnValues["error"] = true;
            $returnValues["error_msg"] = "Fileextension $parent_image_file_extension not supported";
            return $returnValues;
        }
        
        // Prepare next exec
        $cmdArray[5]['output'] = null;
        $cmdArray[5]['resultCode'] = null;
        $cmdArray[5]['cmd'] = "rm -v -f $tmpFile";
        $cmdArray[5]['step_description'] = "Remove copied RAW image in working dir";
        
        // Prepare next exec
        $cmdArray[6]['output'] = null;
        $cmdArray[6]['resultCode'] = null;
        $cmdArray[6]['cmd'] = "rm -v ##tmpFile_jpg##";
        $cmdArray[6]['step_description'] = "Remove not needed files produced during converting";


        // START EXECUTING STEP BY STEP
        $execStep = 1; // create dirs
        exec($cmdArray[$execStep]['cmd'], $cmdArray[$execStep]['output'], $cmdArray[$execStep]['resultCode']);
        if ($cmdArray[$execStep]['resultCode'] == 0)
        {
            $execStep = 2; // remove maybe already existing output file (should not)
            exec($cmdArray[$execStep]['cmd'], $cmdArray[$execStep]['output'], $cmdArray[$execStep]['resultCode']);
            if ($cmdArray[$execStep]['resultCode'] == 0)
            {
                $execStep = 3; // copy RAW to local working dir
                exec($cmdArray[$execStep]['cmd'], $cmdArray[$execStep]['output'], $cmdArray[$execStep]['resultCode']);
                if ($cmdArray[$execStep]['resultCode'] == 0)
                {
                    // change directory (this is needed... ;-))
                    chdir($workingDir);

                    $execStep = 4; // convert RAW to JPG
                    exec($cmdArray[$execStep]['cmd'], $cmdArray[$execStep]['output'], $cmdArray[$execStep]['resultCode']);
                    // $result_from_exiftool = substr(trim($cmdArray[$execStep]['output'][0]),0,1); // not equal 0 ist OK
                    // if ($cmdArray[$execStep]['resultCode'] == 0 && $result_from_exiftool !== "0")
                    $output_file_created = is_readable($conv_tool_arr["converted_image_fullpath"]);

                    if ($cmdArray[$execStep]['resultCode'] == 0 && $output_file_created)
                    {
                        $execStep = 5; // remove RAW file
                        exec($cmdArray[$execStep]['cmd'], $cmdArray[$execStep]['output'], $cmdArray[$execStep]['resultCode']);
                        if ($cmdArray[$execStep]['resultCode'] == 0)
                        {
                            $execStep = 6; // clean up not needed files
                            foreach($conv_tool_arr["not_needed_output"] as $key=>$value)
                            {
                                if(!is_null($value))
                                {
                                    $cmdArray[$execStep]['output'] = null;
                                    $cmdArray[$execStep]['resultCode'] = null;
                                    $tmpCmd = str_replace("##tmpFile_jpg##", $value, $cmdArray[$execStep]['cmd']); 
                                    exec($tmpCmd, $cmdArray[$execStep]['output'], $cmdArray[$execStep]['resultCode']);
                                    if ($cmdArray[$execStep]['resultCode'] == 0)
                                    {
                                        // finish
                                    } else {
                                        $returnValues["error"] = true;
                                        array_push($returnValues["error_msg"], "Step: " . $cmdArray[$execStep]['step_description']);
                                        array_push($returnValues["error_msg"], $conv_tool_arr["not_needed_output"]);
                                        array_push($returnValues["error_msg"], "resultCode: " . $cmdArray[$execStep]['resultCode']);
                                        array_push($returnValues["error_msg"], $cmdArray[$execStep]['output']);            
                                        array_push($returnValues["error_msg"], "Tried cmd exec: ".$tmpCmd);                    
                                        return $returnValues;                  
                                    }    
                                }
                            }
                        } else {
                            $returnValues["error"] = true;
                            array_push($returnValues["error_msg"], "Step: " . $cmdArray[$execStep]['step_description']);
                            array_push($returnValues["error_msg"], "resultCode: " . $cmdArray[$execStep]['resultCode']);
                            array_push($returnValues["error_msg"], $cmdArray[$execStep]['output']);    
                            array_push($returnValues["error_msg"], "Tried cmd exec: ".$cmdArray[$execStep]['cmd']);            
                            return $returnValues;                  
                        }
                    } else {
                        $returnValues["error"] = true;
                        array_push($returnValues["error_msg"], "Step: " . $cmdArray[$execStep]['step_description']);
                        array_push($returnValues["error_msg"], "resultCode: " . $cmdArray[$execStep]['resultCode']);
                        array_push($returnValues["error_msg"], $cmdArray[$execStep]['output']);
                        array_push($returnValues["error_msg"], "Tried cmd exec: ".$cmdArray[$execStep]['cmd']);            
                        if (!$output_file_created)
                        {
                            array_push($returnValues["error_msg"], "Expected ".$conv_tool_arr["converted_image_fullpath"]. " file not found.");
                            if ($cmdArray[$execStep]['resultCode'] == 127)
                            {
                                array_push($returnValues["error_msg"], "127 means 'command not found'. Maybe '" . $conv_tool_arr["used_tool"] . "' not found?");
                            }
                        }
                        return $returnValues;                  
                    }
                } else {
                    $returnValues["error"] = true;
                    array_push($returnValues["error_msg"], "Step: " . $cmdArray[$execStep]['step_description']);
                    array_push($returnValues["error_msg"], "resultCode: " . $cmdArray[$execStep]['resultCode']);
                    array_push($returnValues["error_msg"], $cmdArray[$execStep]['output']);
                    array_push($returnValues["error_msg"], "Tried cmd exec: ".$cmdArray[$execStep]['cmd']);            
                    return $returnValues;                  
                }
            } else {
                $returnValues["error"] = true;
                array_push($returnValues["error_msg"], "Step: " . $cmdArray[$execStep]['step_description']);
                array_push($returnValues["error_msg"], "resultCode: " . $cmdArray[$execStep]['resultCode']);
                array_push($returnValues["error_msg"], $cmdArray[$execStep]['output']);
                array_push($returnValues["error_msg"], "Tried cmd exec: ".$cmdArray[$execStep]['cmd']);            
                return $returnValues;                  
            }
        } else {
            $returnValues["error"] = true;
            array_push($returnValues["error_msg"], "Step: " . $cmdArray[$execStep]['step_description']);
            array_push($returnValues["error_msg"], "resultCode: " . $cmdArray[$execStep]['resultCode']);
            array_push($returnValues["error_msg"], $cmdArray[$execStep]['output']);
            array_push($returnValues["error_msg"], "Tried cmd exec: ".$cmdArray[$execStep]['cmd']);            
            return $returnValues;                  
        }

        $returnValues["generated_jpg_file"] = $conv_tool_arr["converted_image_fullpath"];
        $returnValues["error"] = false;
        $returnValues["error_msg"] = null;
        return $returnValues;        
    }

    public function prepare_navsidebar_data($useAnchorLinks = false)
    {
        $s = "/";
        $label_for_this_folder = 'This folder <i class="fas fa-images"></i>';
        $icon_for_this_folder = 'arrow-alt-circle-right';
        $albums_with_path = array();

        foreach($this->get_Albums_With_Images() as $row)
        {
            $albums_with_path[$row->relativePath] = $row->id;
        }

        $unflatten1 = $this->unflatten($albums_with_path);

        $items = array();

        $tmp1 = array();
        foreach($unflatten1 as $key1=>$value1)
        {
            if (is_array($value1))
            {
                $tmp2 = array();
                foreach($value1 as $key2=>$value2)
                {          
                    if (is_array($value2))
                    {
                        $tmp3 = array();
                        foreach($value2 as $key3=>$value3)
                        {
                            if (is_array($value3))
                            {
                                $tmp4 = array();
                                foreach($value3 as $key4=>$value4)
                                {
                                    if (is_array($value4))
                                    {
                                        $tmp5 = array();
                                        foreach($value4 as $key5=>$value5)
                                        {
                                            if (is_array($value5))
                                            {
                                                $tmp6 = array();
                                                foreach($value5 as $key6=>$value6)
                                                {            
                                                    if (is_array($value6))
                                                    {
                                                        $tmp7 = array();
                                                        foreach($value6 as $key7=>$value7)
                                                        {
                                                            if (is_array($value7))
                                                            {
                                                                $tmp8 = array();
                                                                foreach($value7 as $key8=>$value8)
                                                                {
                                                                    if (is_array($value8))
                                                                    {
                                                                        $tmp9 = array();
                                                                        foreach($value8 as $key9=>$value9)
                                                                        {
                                                                            if (is_array($value9))
                                                                            {
                                                                                $tmp10 = array();
                                                                            }                                                                          
                                                                            $chk_path = "$s$key2$s$key3$s$key4$s$key5$s$key6$s$key7$s$key8$s$key9";
                                                                            $this->evaluate_tree_element(
                                                                                 $key9
                                                                                ,$value9
                                                                                ,$chk_path
                                                                                ,$albums_with_path
                                                                                ,$label_for_this_folder
                                                                                ,$icon_for_this_folder
                                                                                ,$tmp9 // call by ref
                                                                                ,$tmp10 // call by ref
                                                                                ,'folder'
                                                                                ,'images'                                                
                                                                                ,$useAnchorLinks
                                                                            );
                                                                        }
                                                                    }
                                                                    $chk_path = "$s$key2$s$key3$s$key4$s$key5$s$key6$s$key7$s$key8";
                                                                    $this->evaluate_tree_element(
                                                                         $key8
                                                                        ,$value8
                                                                        ,$chk_path
                                                                        ,$albums_with_path
                                                                        ,$label_for_this_folder
                                                                        ,$icon_for_this_folder
                                                                        ,$tmp8 // call by ref
                                                                        ,$tmp9 // call by ref
                                                                        ,'folder'
                                                                        ,'images'                                        
                                                                        ,$useAnchorLinks
                                                                    );
                                                                }
                                                            }
                                                            $chk_path = "$s$key2$s$key3$s$key4$s$key5$s$key6$s$key7";
                                                            $this->evaluate_tree_element(
                                                                 $key7
                                                                ,$value7
                                                                ,$chk_path
                                                                ,$albums_with_path
                                                                ,$label_for_this_folder
                                                                ,$icon_for_this_folder
                                                                ,$tmp7 // call by ref
                                                                ,$tmp8 // call by ref
                                                                ,'folder'
                                                                ,'images'                                
                                                                ,$useAnchorLinks
                                                            );
                                                        }
                                                    }
                                                    $chk_path = "$s$key2$s$key3$s$key4$s$key5$s$key6";
                                                    $this->evaluate_tree_element(
                                                         $key6
                                                        ,$value6
                                                        ,$chk_path
                                                        ,$albums_with_path
                                                        ,$label_for_this_folder
                                                        ,$icon_for_this_folder
                                                        ,$tmp6 // call by ref
                                                        ,$tmp7 // call by ref
                                                        ,'folder'
                                                        ,'images'                        
                                                        ,$useAnchorLinks
                                                    );
        
                                                }
                                            }
                                            $chk_path = "$s$key2$s$key3$s$key4$s$key5";
                                            $this->evaluate_tree_element(
                                                 $key5
                                                ,$value5
                                                ,$chk_path
                                                ,$albums_with_path
                                                ,$label_for_this_folder
                                                ,$icon_for_this_folder
                                                ,$tmp5 // call by ref
                                                ,$tmp6 // call by ref
                                                ,'folder'
                                                ,'images'                
                                                ,$useAnchorLinks
                                            );
                                        }
                                    }
                                    $chk_path = "$s$key2$s$key3$s$key4";
                                    $this->evaluate_tree_element(
                                         $key4
                                        ,$value4
                                        ,$chk_path
                                        ,$albums_with_path
                                        ,$label_for_this_folder
                                        ,$icon_for_this_folder
                                        ,$tmp4 // call by ref
                                        ,$tmp5 // call by ref
                                        ,'folder'
                                        ,'images'        
                                        ,$useAnchorLinks
                                    );
                                }
                            }
                            $chk_path = "$s$key2$s$key3";
                            $this->evaluate_tree_element(
                                 $key3
                                ,$value3
                                ,$chk_path
                                ,$albums_with_path
                                ,$label_for_this_folder
                                ,$icon_for_this_folder
                                ,$tmp3 // call by ref
                                ,$tmp4 // call by ref
                                ,'folder'
                                ,'images'
                                ,$useAnchorLinks
                            );
                        }
                    }
                    $icon_folder = 'folder';
                    if ($key2=="_nonSmartphone") $icon_folder = "camera";
                    if ($key2=="_Smartphone_Bilder_Alle") $icon_folder = "mobile-alt";
                    $chk_path = "$s$key2";
                    $this->evaluate_tree_element(
                         $key2
                        ,$value2
                        ,$chk_path
                        ,$albums_with_path
                        ,$label_for_this_folder
                        ,$icon_for_this_folder
                        ,$tmp2 // call by ref
                        ,$tmp3 // call by ref
                        ,$icon_folder // overriding default
                        ,'images'
                        ,$useAnchorLinks
                    );
                }
                $tmp1 = ['label' => "Filesystem".$key1, 'icon' => 'folder', 'items' => $tmp2];
                array_push($items, $tmp1);
            }
        }
        return $items;
    }

    /**
     * This function simplifies the evaluation which child elements have to be created. This method is exclusively called by the method utils->prepare_navsidebar_data.
     * The parameter $tmpParent and $tmpChild will be manipulated!
     * @param string $label_key              The label/caption for this item. The text will be shortened if it longer than 53 digits.
     * @param string $value                  This is the current for-each $valueN element which contains subarrays (means subfolder) or the current item (means images).
     * @param string $chk_path               The current path. It is used to evaluate if this current item also contains images (and subfolder). If the content is an array element of $albums_with_path, then the item "This folder" is created.
     * @param array  $albums_with_path       This array holds all paths as keys and the albumid als value. Used to check if creating "This folder" is necessary.
     * @param string $label_for_this_folder  Caption for "This folder".
     * @param string $icon_for_this_folder   Icon for "This folder".
     * @param array  &$tmpParent             Call by reference. This is the current for-each $tmpN element for this level.
     * @param array  &$tmpChild              Call by reference. This is the child $tmpN+1 element for this level.
     * @param string $icon_folder            Default: 'folder'. Icon for a folder element.
     * @param string $icon_images            Default: 'images'. Icon for a images element.
     */
    private function evaluate_tree_element($label_key, $value, $chk_path, $albums_with_path, $label_for_this_folder, $icon_for_this_folder, &$tmpParent, &$tmpChild, $icon_folder = 'folder', $icon_images = 'images', $useAnchorLinks = false)
    {
        $label = strlen($label_key)>53 ? substr($label_key,0,50)."..." : $label_key;
        if (is_array($value))
        {
            if (array_key_exists($chk_path, $albums_with_path)) array_push($tmpChild, ['label' => $label_for_this_folder, 'icon' => $icon_for_this_folder, 'url' => ['gallery/album', 'albumid' => $albums_with_path[$chk_path]]]);
            array_push($tmpParent, ['label' => $label, 'icon' => $icon_folder, 'items' => $tmpChild]);
        }
        else 
        if (!$useAnchorLinks) array_push($tmpParent, ['label' => $label, 'icon' => $icon_images, 'url' => ['gallery/album', 'albumid' => $value]]);
        else array_push($tmpParent, ['label' => $label, 'icon' => $icon_images, 'url' => "#$value"]);
    }

    // $utils = new \vendor\digiyiikam\utils();
    // $utils->TagItemsTree();
    public function TagItemsTree()
    {
        // $label_template = '<span class="pull-right float-right float-end badge btn ' . '{{{class}}}' . '{{{cnt}}}' . '</span> ' . '{{{tagname}}}';

        $images_per_tags = $this->images_per_tags();
        $items = array();
        // use app\models\Tags;
        $parentTagsInUse_qry1 = Tags::find()->cache(7200)->where(['pid' => 0])->all();
        foreach($parentTagsInUse_qry1 as $rows1)
        {
            $label1 = $rows1->name;

            $label1_with_badge = '<span class="pull-right float-right float-end badge btn ';
            if (isset($images_per_tags[$rows1->id])) $label1_with_badge .='btn-success">' . $images_per_tags[$rows1->id];
            else $label1_with_badge .='btn-danger">' . "0";
            $label1_with_badge .='</span> ' . $rows1->name;

            $icon1 = "tag";
            $url1 = ['gallery/tag', 'tagid' => $rows1->id];
            
            // check if root-tag has childs?
            $parentTagsInUse_qry2 = Tags::find()->cache(7200)->where(['pid' => $rows1->id])->all();
            if (count($parentTagsInUse_qry2)>0)
            {
                // has childs
                $subitems = array();
                foreach($parentTagsInUse_qry2 as $rows2)
                {
                    $label2 = $rows2->name;

                    $label2_with_badge = '<span class="pull-right float-right float-end badge btn ';
                    if (isset($images_per_tags[$rows2->id])) $label2_with_badge .='btn-success">' . $images_per_tags[$rows2->id];
                    else $label2_with_badge .='btn-danger">' . "0";
                    $label2_with_badge .='</span> ' . $rows2->name;

                    $icon2 = "tag";
                    $url2 = ['gallery/tag', 'tagid' => $rows2->id];
                    
                    // check if root-tag has childs?
                    $parentTagsInUse_qry3 = Tags::find()->cache(7200)->where(['pid' => $rows2->id])->all();
                    if (count($parentTagsInUse_qry3)>0)
                    {
                        // has childs
                        $subsubitems = array();
                        foreach($parentTagsInUse_qry3 as $rows3)
                        {
                            $label3 = $rows3->name;

                            $label3_with_badge = '<span class="pull-right float-right float-end badge btn ';
                            if (isset($images_per_tags[$rows3->id])) $label3_with_badge .='btn-success">' . $images_per_tags[$rows3->id];
                            else $label3_with_badge .='btn-danger">' . "0";
                            $label3_with_badge .='</span> ' . $rows3->name;

                            $icon3 = "tag";
                            $url3 = ['gallery/tag', 'tagid' => $rows3->id];

                            $this_item = ['label' => $label3_with_badge, 'icon' => $icon3, 'url' => $url3];
                            array_push($subsubitems, $this_item);
                        }
                        $icon1 = "tags";
                        $this_item = ['label' => $label2, 'icon' => $icon2, 'items' => $subsubitems];
                        array_push($subitems, $this_item);
                    }
                    else
                    {
                        // no childs
                        $this_item = ['label' => $label2_with_badge, 'icon' => $icon2, 'url' => $url2];
                        array_push($subitems, $this_item);
                    }

                }
                $icon1 = "tags";
                $this_item = ['label' => $label1, 'icon' => $icon1, 'items' => $subitems];
                array_push($items, $this_item);
            }
            else
            {
                // no childs
                $this_item = ['label' => $label1_with_badge, 'icon' => $icon1, 'url' => $url1];
                array_push($items, $this_item);
            }

        }
        
        return $items;
    }

    // $utils = new \vendor\digiyiikam\utils();
    // $utils->images_per_tags();
    public function images_per_tags()
    {
        $returnValues = array();
        $DykThumbnails_Model = DykThumbnails::find()
            ->cache(7200)
            ->select(['digikam_Images_id'])->where(['thumbnail_creation_ok' => 1])->asArray(true)->all();
        $qry = ImageTags::find()
            ->cache(7200)
            ->select('tagid, count(imageid) as cnt')->where(['IN','imageid', (new \vendor\digiyiikam\utils())->flatten($DykThumbnails_Model)])->groupBy(['tagid'])->createCommand()->queryAll();
        foreach($qry as $row)
        {
            $returnValues[$row["tagid"]] = $row["cnt"];
        }
        return $returnValues;
    }

    public function get_data_pie_chart()
    {
        $returnValues = array();
        $qryArr = \app\models\DykThumbnails::find()->select('parent_image_file_extension as name, count(parent_image_file_extension) as data')->where(['thumbnail_creation_ok' => 1])->groupBy(['parent_image_file_extension'])->createCommand()->queryAll();
        $series = array();
        $labels = array();
        foreach($qryArr as $rows)
        {
            array_push($series, $rows["data"]);
            array_push($labels, $rows["name"]);
        }
        $returnValues['series'] = $series;
        $returnValues['labels'] = $labels;
        return $returnValues;
    }

    /**
     * If a blank is the jpg-src filepath, thumbnail generation will fail/not work. 
     * In this case the file will be copied beforehand through this function and the new local path will be given.
     * @return string Full path to local copied file as file-URI notation.
     */
    public function src_path_with_blanks_thumbnail_pre_handling($parent_image_full_filepath)
    {
        $workingDirBase = (new \vendor\digiyiikam\config())->getWorkingDirForRAWConverting();
        $workingDir = $workingDirBase.'/'.uniqid("dykworkingdir_thumbnailing_");
        $cmdArray[1]['output'] = null;
        $cmdArray[1]['resultCode'] = null;
        $cmdArray[1]['cmd'] = "mkdir -p $workingDir";
        $cmdArray[1]['step_description'] = "Crate working dir";
        $execStep = 1;
        exec($cmdArray[$execStep]['cmd'], $cmdArray[$execStep]['output'], $cmdArray[$execStep]['resultCode']);
        
        $fn_new = str_replace(" ", "__", basename($parent_image_full_filepath));
        $cmdArray = array();
        $cmdArray[3]['output'] = null;
        $cmdArray[3]['resultCode'] = null;
        $cmdArray[3]['cmd'] = "cp \"$parent_image_full_filepath\" $workingDir/$fn_new";
        $cmdArray[3]['step_description'] = "Copy file to local because there are blanks in the src path";

        $execStep = 3;
        exec($cmdArray[$execStep]['cmd'], $cmdArray[$execStep]['output'], $cmdArray[$execStep]['resultCode']);

        $orgFile = "file://". "$workingDir/$fn_new";
        return $orgFile;
    }

    public function save_digikam_table_stats()
    {
        DykDigikamTableStats::deleteAll();
       
        $values = array();
        
        $model = new \app\models\Albums();
        $values[$model::getTableSchema()->name] = $model::find()->count();
        unset($model);

        $model = new \app\models\Images();
        $values[$model::getTableSchema()->name] = $model::find()->count();
        unset($model);

        $model = new \app\models\ImageTags();
        $values[$model::getTableSchema()->name] = $model::find()->count();
        unset($model);

        foreach($values as $tablename=>$count)
        {
            $model_DykDigikamTableStats = new DykDigikamTableStats();
            $model_DykDigikamTableStats->digikam_tablename = $tablename;
            $model_DykDigikamTableStats->count = $count;
            $model_DykDigikamTableStats->save();
        }
    }

    /**
     * Compares the value counts from the digiYiiKam table dyk_digikam_table_stats with the 
     * current table count of the digiKam tables.
     * This method is needed to determine, if a cache reset is necessary.
     * @return bool If one table count differs, then true is returned. Otherwise false (table counts in sync)
     */
    public function has_diff_compare_digikam_table_stats()
    {
        $returnValue = false;
        $qry = DykDigikamTableStats::find()->all();
        foreach($qry as $row)
        {
            if ($row->digikam_tablename == 'Albums') if ($row->count != Albums::find()->count()) $returnValue = true;
            if ($row->digikam_tablename == 'Images') if ($row->count != Images::find()->count()) $returnValue = true;
            if ($row->digikam_tablename == 'ImageTags') if ($row->count != ImageTags::find()->count()) $returnValue = true;
        }
        if ($returnValue) $this->save_digikam_table_stats();
        return $returnValue;
    }

    /**
     * Reset application cache (used for caching database query results).
     * @return string Message text with the result.
     */
    public function flushCache($id='cache')
    {
        if (!isset(Yii::$app->{$id}) || !(Yii::$app->{$id} instanceof Cache)) {
            $msg = Yii::t('app','Invalid cache to flush: {cache}', ['cache'=>$id]);
            throw new InvalidArgumentException($msg);
        }
    
        $cache = Yii::$app->{$id};
        if ($cache->flush()) {
            $msg = "Successfully flushed cache '$id'";
        } else {
            $msg = "Problem while flushing cache '$id'";
        }
        return $msg;
    }

    /**
     * Erase all items in the digiYiiKam database table dyk_navigation_cache.
     * @return int Deleted items
     */
    public function eraseNavCache()
    {
        return DykNavigationCache::deleteAll();
    }

    /**
     * Erase all entries (should be only one) of the table and then save new values.
     * @return bool Returnvalue of model saving
     */
    public function updateLastLocation($albumid = NULL, $tagid = NULL)
    {
        DykLastPosition::deleteAll();
        $model = new DykLastPosition();
        $model->albumid = $albumid;
        $model->tagid = $tagid;
        return $model->save();
    }

    /**
     * Get the last position where the user has been (in the navigation tree).
     * If nothing is found, the fallback is the first found albumid with images.
     * @return array Nav::widget item array element.
     */
    public function getLastLocation()
    {
        $qry = DykLastPosition::find()->one();
        if (!is_null($qry))
        {
            if (!is_null($qry->albumid))
            {
                return ['/gallery/album', 'albumid' => $qry->albumid];
            }
            else
            {
                if (!is_null($qry->tagid))
                {
                    return ['/gallery/tag', 'tagid' => $qry->tagid];
                }                
            }
        }

        // Fallback
        return ['/gallery/album', 'albumid' => (new \vendor\digiyiikam\utils())->get_Albums_With_Images()[0]->id];
    }
}
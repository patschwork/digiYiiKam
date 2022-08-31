<?php

namespace app\controllers;


use app\models\DykImages;
use Yii;
use yii\web\Controller;
use app\models\DykThumbnails;
use app\models\ImageInformation;
use app\models\ImageMetadata;
use app\models\ImageTags;
use app\models\Tags;
use yii\bootstrap4\Html;
use yii\helpers\Url;
use yii\web\Response;

class GalleryController extends Controller
{

    // List all albumns
    public function actionIndex()
    {
        $albums_with_id = $this->get_albums_with_id_as_array(null);
        $param = [
            'albums_with_id' => $albums_with_id
        ];
        return $this->render('index', $param);
    }


    /**
     * @return array with album IDs containing an image which is already in the digiYiiKam database.
     */
    private function get_albums_with_id_as_array($utils = null)
    {
        if (is_null($utils)) $utils = new \vendor\digiyiikam\utils();
        $albums_with_id = array();
        foreach($utils->get_Albums_With_Images() as $row)
        {
            $albums_with_id[$row->id] = $row->relativePath;
        }
        ksort($albums_with_id);
        
        return $albums_with_id;
    }
    
    /**
     * Builds the box with the content for the EXIF information
     * @return string with HTML content
     */
    private function get_digikam_metadata_as_html($imageid, $images_RelativePath)
    {
        $ImageMetadata_Model = ImageMetadata::findOne($imageid);
        $ImageInformation_Model = ImageInformation::findOne($imageid);
        $ImageTagsWithTag_Model = ImageTags::find()->joinWith("tag")->where(['imageid' => $imageid])->asArray()->all();
        $exposureTime = -9999;
        $exposureTimeGtOne = false;
        $camara_make = "";
        $camara_model = "";
        $camara_lens = "";
        $camara_aperture = "";
        $camara_focalLength = null;
        $camara_iso = null;

        $imgInfo_rating = -1;
        $imgInfo_creationDate = null;
        $imgInfo_width = null;
        $imgInfo_height = null;
        $imgInfo_format = null;
        $tags = null;


        if (!is_null($ImageMetadata_Model))
        {
            if ($ImageMetadata_Model->exposureTime <> 0)
            {
                $exposureTimeGtOne = ($ImageMetadata_Model->exposureTime > 1.0);
                $exposureTime = ($exposureTimeGtOne) ? intval($ImageMetadata_Model->exposureTime)." Sekunden" : ("1/".intval((1/doubleval($ImageMetadata_Model->exposureTime)))) . " Sekunde";
            }
            $camara_make = $ImageMetadata_Model->make;
            $camara_model = $ImageMetadata_Model->model;
            $camara_lens = $ImageMetadata_Model->lens;
            $camara_aperture = $ImageMetadata_Model->aperture;
            $camara_focalLength = $ImageMetadata_Model->focalLength;
            $camara_iso = $ImageMetadata_Model->sensitivity;
        }

        if (!is_null($ImageInformation_Model))
        {
            $imgInfo_rating = $ImageInformation_Model->rating;
            $imgInfo_creationDate = $ImageInformation_Model->creationDate;
            $imgInfo_width = $ImageInformation_Model->width;
            $imgInfo_height = $ImageInformation_Model->height;
            $imgInfo_format = $ImageInformation_Model->format;
        }

        if (!is_null($ImageTagsWithTag_Model))
        {
            $tags = "";
            foreach($ImageTagsWithTag_Model as $tagRow)
            {
                if ($tags == "") $tags = "<b>TAGS:</b><br>";
                if ($tagRow["tag"]["pid"] !== 1)
                {
                    // $tags .= $tagRow["tag"]["name"] . "<br>";
                    $html_a_link = Html::a($tagRow["tag"]["name"],Url::to(['gallery/tag', 'tagid' => $tagRow["tag"]["id"]])) . "<br>";
                    $tags .= $html_a_link;
                }
            }
        }

        $exif = "";
        // $exif .= "<button onclick='myFunction()'>Close</button>";
        $exif .= "<small><i>DigiKam Image ID: " . $imageid . "</i></small><br>";
        // $exif .= "<small><i>Path: " . $images_RelativePath . "</i></small><br>";
        $exif .= "<b>EXIF:</b><br>";
        $exif .= "Camera: " . $camara_make . " " . $camara_model . "<br>";
        $exif .= "Objektiv: " . $camara_lens . "<br>";
        $exif .= "Blende: F" . $camara_aperture . "<br>";
        $exif .= "Belichtung: " . $exposureTime . "<br>";
        $exif .= (!is_null($camara_focalLength))                         ? "Brennweite: $camara_focalLength mm". "<br>" : "";
        $exif .= (!is_null($camara_iso))                                 ? "ISO: $camara_iso". "<br>" : "";
        $exif .= (!is_null($imgInfo_rating) && $imgInfo_rating>=0)       ? "Bewertung: $imgInfo_rating Sterne". "<br>" : "";
        $exif .= (!is_null($imgInfo_creationDate))                       ? "Datum: $imgInfo_creationDate". "<br>" : "";
        $exif .= (!is_null($imgInfo_width) && !is_null($imgInfo_height)) ? "Abmessung: $imgInfo_width x $imgInfo_height". "<br>" : "";
        $exif .= (!is_null($imgInfo_format))                             ? "Format: $imgInfo_format". "<br>" : "";
        $exif .= (!is_null($tags))                                       ? $tags : "";
        return $exif;
    }

    /**
     * Prepare the button with for the heart tag in the header to mark an image.
     * When clicked by the user, the last state will be saved to the localStorage of the browser.
     * @return \dmstr\ajaxbutton\AjaxButton::widget HTML ready btn code with toggled heart state
     */
    // {... heart-tag
    private function heart_tag_button($imageid, $tagname = "heart")
    {
        $content_set = '<i style="color: #FF0000;" class="fas fa-heart"></i>';
        $content_not_set = '<i class="far fa-heart"></i>';
        $tagModel = \app\models\Tags::findOne(['name' => $tagname]);
        if (is_null($tagModel)) $tagid = -1; else $tagid = $tagModel->id;
        $chk_image_tag_toggle = \app\models\ImageTags::findOne(['imageid' => $imageid, 'tagid' => $tagid]);

        if (!is_null($chk_image_tag_toggle)) $content = $content_set;
        else $content = $content_not_set;
    
        $ajaxBtn = \dmstr\ajaxbutton\AjaxButton::widget([
        'method' => 'post',
        'params' => ['imageid' => $imageid,'tagname' => $tagname],
        'url' => ['/gallery/setdyktagforimage'],
        'options' => ['class' => 'btn btn-dark', 'id' => "ajaxButton_Heart_Tag_$imageid"],
        'successExpression' => new \yii\web\JsExpression('function(resp,status,xhr) 
            {
                if (xhr.status === 200) 
                {
                    if (JSON.parse(localStorage.getItem("heart-tag-states")))
                    {
                        arr = JSON.parse(localStorage.getItem("heart-tag-states"));
                    }
                    else
                    {
                        var arr = [];
                    }

                    if (xhr.responseJSON.tag_image_combination_set) 
                    {
                        button.html(\''.$content_set.'\')
                        arr['.$imageid.'] = document.getElementById("ajaxButton_Heart_Tag_'.$imageid.'").outerHTML;
                        localStorage.setItem("heart-tag-states", JSON.stringify(arr));
                    }
                    else 
                    {
                        button.html(\''.$content_not_set.'\')
                        arr['.$imageid.'] = document.getElementById("ajaxButton_Heart_Tag_'.$imageid.'").outerHTML;
                        localStorage.setItem("heart-tag-states", JSON.stringify(arr));
                    }
                }
            }'),
        'errorExpression' => new \yii\web\JsExpression('function(xhr) {console.error(xhr); if (xhr.status === 404) {button.html("Error");console.error(xhr.responseJSON)}}'),
        'content' => $content,
        ]);

        return $ajaxBtn;
    }

    private function heart_tag_buttonV2($imageid, $tagname = "heart", $Images_with_This_Tag_Arr)
    {
        $content_set = '<i style="color: #FF0000;" class="fas fa-heart"></i>';
        $content_not_set = '<i class="far fa-heart"></i>';
        
        // {... V2
            // $tagModel = \app\models\Tags::findOne(['name' => $tagname]);
            // if (is_null($tagModel)) $tagid = -1; else $tagid = $tagModel->id;
            // $chk_image_tag_toggle = \app\models\ImageTags::findOne(['imageid' => $imageid, 'tagid' => $tagid]);
    
            // if (!is_null($chk_image_tag_toggle)) $content = $content_set;
            // else $content = $content_not_set;
            $content = in_array($imageid, $Images_with_This_Tag_Arr) ? $content_set : $content_not_set;
        // ...}

        
        $ajaxBtn = \dmstr\ajaxbutton\AjaxButton::widget([
        'method' => 'post',
        'params' => ['imageid' => $imageid,'tagname' => $tagname],
        'url' => ['/gallery/setdyktagforimage'],
        'options' => ['class' => 'btn btn-dark', 'id' => "ajaxButton_Heart_Tag_$imageid"],
        'successExpression' => new \yii\web\JsExpression('function(resp,status,xhr) 
            {
                if (xhr.status === 200) 
                {
                    if (JSON.parse(localStorage.getItem("heart-tag-states")))
                    {
                        arr = JSON.parse(localStorage.getItem("heart-tag-states"));
                    }
                    else
                    {
                        var arr = [];
                    }

                    if (xhr.responseJSON.tag_image_combination_set) 
                    {
                        button.html(\''.$content_set.'\')
                        arr['.$imageid.'] = document.getElementById("ajaxButton_Heart_Tag_'.$imageid.'").outerHTML;
                        localStorage.setItem("heart-tag-states", JSON.stringify(arr));
                    }
                    else 
                    {
                        button.html(\''.$content_not_set.'\')
                        arr['.$imageid.'] = document.getElementById("ajaxButton_Heart_Tag_'.$imageid.'").outerHTML;
                        localStorage.setItem("heart-tag-states", JSON.stringify(arr));
                    }
                }
            }'),
        'errorExpression' => new \yii\web\JsExpression('function(xhr) {console.error(xhr); if (xhr.status === 404) {button.html("Error");console.error(xhr.responseJSON)}}'),
        'content' => $content,
        ]);

        return $ajaxBtn;
    }
    // ...}

    /**
     * List by albums
     */
    public function actionAlbum($albumid = -1)
    {
        $utils = new \vendor\digiyiikam\utils();

        // if there are changes, reset the cache
        if ($utils->has_diff_compare_digikam_table_stats())
        {
            $utils->flushCache();
            $utils->eraseNavCache();
        }

        $config = new \vendor\digiyiikam\config();
        
        $albums_with_id = $this->get_albums_with_id_as_array($utils);
        $collectionPaths = $config->getCollectionPaths();

        // {... heart-tag
        $tagModel_parent = \app\models\Tags::find()->where(['name' => 'DigiYiiKam'])->andWhere(['pid' => 0])->one();
        if (is_null($tagModel_parent)) $tagparentid = -1; else $tagparentid = $tagModel_parent->id;
        $tagModel_child = \app\models\Tags::find()->where(['name' => 'heart'])->andWhere(['pid' => $tagparentid])->one();
        if (is_null($tagModel_child)) $heart_tagid = -1; else $heart_tagid = $tagModel_child->id;
        $heart_tags=\app\models\ImageTags::find()->select(['imageid'])->where(['tagid' => $heart_tagid])->asArray()->all();
        $heart_tags_arr = $utils->flatten($heart_tags);
        // ...}

        $images = array();
        $images_AlbumRoot = array();
        $images_RelativePath = array();
        $images_filename = array();
        $album_name = "";

        foreach($utils->get_Images_From_Albums($albumid) as $rowImages)
        {
            array_push($images, $rowImages->id);
            $images_filename[$rowImages->id]     = $rowImages->name;
            $images_AlbumRoot[$rowImages->id]    = $rowImages->albums[0]->albumRoot;
            $images_RelativePath[$rowImages->id] = $rowImages->albums[0]->relativePath;
            $album_name                          = $rowImages->albums[0]->relativePath;
        }

        $DykThumbnails_Model = DykThumbnails::find()->select(['id', 'thumbnail_filename', 'parent_image_full_filepath', 'digikam_Images_id'])->where(['IN','digikam_Images_id', $images])->all();

        $items = array();
        foreach($DykThumbnails_Model as $rowThumbnails)
        {
            // same logic as utils.get_translated_fullpath_for_source_image($imageid), but this will not call the database again, and again... 
            // maybe utils.get_translated_fullpath_for_source_image should be optimized to use existing model instances...
            $fullpath_source = 
                $collectionPaths[$images_AlbumRoot[$rowThumbnails->digikam_Images_id]]
              . substr($images_RelativePath[$rowThumbnails->digikam_Images_id],1,99999)
              . '/'
              . $images_filename[$rowThumbnails->digikam_Images_id]
              ;

            $info = pathinfo($fullpath_source);
            $file_name =  basename($fullpath_source,'.'.$info['extension']);

            $exif = $this->get_digikam_metadata_as_html($rowThumbnails->digikam_Images_id, $images_RelativePath[$rowThumbnails->digikam_Images_id]);
            
            $element = [
                    'src' => Url::to(['thumbnail/getimage', 'id' => $rowThumbnails->id]),
                    'url' => Url::to(['gallery/getfullimagefromdatabase', 'digikam_Images_id' => $rowThumbnails->digikam_Images_id]),
                    'options' => array(
                        'title' => $file_name. '.'.$info['extension'] . "  |  " . $images_RelativePath[$rowThumbnails->digikam_Images_id] , 
                        "exif" =>  $exif, 
                        // {... heart-tag
                        // "heart-tag" => $this->heart_tag_button($rowThumbnails->digikam_Images_id),
                        "heart-tag" => $this->heart_tag_buttonV2($rowThumbnails->digikam_Images_id, "heart", $heart_tags_arr),
                        "digikam_Images_id" => $rowThumbnails->digikam_Images_id
                        // ...}
                        )
            ];
            array_push($items, $element);
        }

        $param = [
            'albumid' => $albumid
           ,'album_name' => $album_name
           ,'items' => $items
           ,'albums_with_id' => $albums_with_id
        ];
        return $this->render('album4', $param);
        // return $this->render('album3', $param); // much faster
    }

    /**
     * List by tags
     */
    public function actionTag($tagid = -1)
    {
        $utils = new \vendor\digiyiikam\utils();

        // if there are changes, reset the cache
        if ($utils->has_diff_compare_digikam_table_stats())
        {
            $utils->flushCache();
            $utils->eraseNavCache();
        }

        $config = new \vendor\digiyiikam\config();
        
        $albums_with_id = $this->get_albums_with_id_as_array($utils);
        $collectionPaths = $config->getCollectionPaths();

        // {... heart-tag
        $tagModel_parent = \app\models\Tags::find()->where(['name' => 'DigiYiiKam'])->andWhere(['pid' => 0])->one();
        if (is_null($tagModel_parent)) $tagparentid = -1; else $tagparentid = $tagModel_parent->id;
        $tagModel_child = \app\models\Tags::find()->where(['name' => 'heart'])->andWhere(['pid' => $tagparentid])->one();
        if (is_null($tagModel_child)) $heart_tagid = -1; else $heart_tagid = $tagModel_child->id;
        $heart_tags=\app\models\ImageTags::find()->select(['imageid'])->where(['tagid' => $heart_tagid])->asArray()->all();
        $heart_tags_arr = $utils->flatten($heart_tags);
        // ...}
    
        $images = array();
        $images_AlbumRoot = array();
        $images_RelativePath = array();
        $images_filename = array();
        $album_name = "";

        foreach($utils->get_Images_From_Tags($tagid) as $rowImages)
        {
            array_push($images, $rowImages->id);
            $images_filename[$rowImages->id]       = $rowImages->name;
            $images_AlbumRoot[$rowImages->id]      = $rowImages->albums[0]->albumRoot;
            $images_RelativePath[$rowImages->id]   = $rowImages->albums[0]->relativePath;
            $album_name                            = $rowImages->albums[0]->relativePath;
        }

        $DykThumbnails_Model = DykThumbnails::find()->select(['id', 'thumbnail_filename', 'parent_image_full_filepath', 'digikam_Images_id'])->where(['IN','digikam_Images_id', $images])->all();

        $items = array();
        foreach($DykThumbnails_Model as $rowThumbnails)
        {
            // same logic as utils.get_translated_fullpath_for_source_image($imageid), but this will not call the database again, and again... 
            // maybe utils.get_translated_fullpath_for_source_image should be optimized to use existing model instances...
            $fullpath_source = 
                $collectionPaths[$images_AlbumRoot[$rowThumbnails->digikam_Images_id]]
              . substr($images_RelativePath[$rowThumbnails->digikam_Images_id],1,99999)
              . '/'
              . $images_filename[$rowThumbnails->digikam_Images_id]
              ;

            $info = pathinfo($fullpath_source);
            $file_name =  basename($fullpath_source,'.'.$info['extension']);

            $exif = $this->get_digikam_metadata_as_html($rowThumbnails->digikam_Images_id, $images_RelativePath[$rowThumbnails->digikam_Images_id]);
            
            $element = [
                    'src' => Url::to(['thumbnail/getimage', 'id' => $rowThumbnails->id]),
                    'url' => Url::to(['gallery/getfullimagefromdatabase', 'digikam_Images_id' => $rowThumbnails->digikam_Images_id]),
                    'options' => array(
                        'title' => $file_name. '.'.$info['extension'] . "  |  " . $images_RelativePath[$rowThumbnails->digikam_Images_id] , 
                        "exif" =>  $exif, 
                        // {... heart-tag
                        // "heart-tag" => $this->heart_tag_button($rowThumbnails->digikam_Images_id),
                        "heart-tag" => $this->heart_tag_buttonV2($rowThumbnails->digikam_Images_id, "heart", $heart_tags_arr),
                        "digikam_Images_id" => $rowThumbnails->digikam_Images_id
                        // ...}
                        )
            ];
            array_push($items, $element);
        }

        $param = [
            'tagid' => $tagid
           ,'album_name' => $album_name
           ,'items' => $items
           ,'albums_with_id' => $albums_with_id
        ];
        return $this->render('album4', $param);
    }

    /**
     * It's a medium to "stream" the orginal source file via the webserver for providing an URL for the BlueImg-Gallery items.
     * @param fullpath_source String with the full filename and path. The paths needs to be accessable from the current client.
     * @return yii\web\Response
     */
    public function actionGetfullimage($fullpath_source)
    {
        $response = Yii::$app->getResponse();
        $response->headers->set('Content-Type', mime_content_type($fullpath_source));
        $response->data = file_get_contents($fullpath_source);

        $response->format = Response::FORMAT_RAW;
        return $response->send();
    }

    /**
     * It's a medium to "stream" the the JPG-represantive image via webserver for providing an URL for the BlueImg-Gallery items.
     * If the source file is already is a JPG, is will be used from the filesystem.
     * If the source is a converted RAW-2-JPG image, it will be load from the database table DykImages.
     * @param digikam_Images_id Filter by field digikam_Images_id.
     * @return yii\web\Response
     * @example http://localhost:8888/index.php?r=gallery/getfullimagefromdatabase&digikam_Images_id=119161
     */
    public function actionGetfullimagefromdatabase($digikam_Images_id)
    {
        $response = Yii::$app->getResponse();
        $qryI = DykImages::findOne(['digikam_Images_id' => $digikam_Images_id]);
        
        $useImage = false;
        if (!is_null($qryI))
        {
            if ($qryI->not_needed_source_is_a_jpeg == 0)
            {
                $useImage = true;
            }
        }

        if ($useImage)
        {
            if ($qryI->not_needed_source_is_a_jpeg == 0)
            {
                $response->headers->set('Content-Type', $qryI->image_mime);
                $response->data = $qryI->image_blob;
            }
        }
        else
        {
            // $qryT = DykThumbnails::find()->select(['parent_image_full_filepath'])->where(['digikam_Images_id' => $digikam_Images_id])->one();
            // if (!is_null($qryT))
            // {
                // $fullpath_source = $qryT->parent_image_full_filepath;
                $fullpath_source = (new \vendor\digiyiikam\utils())->get_translated_fullpath_for_source_image($digikam_Images_id);
                $response = Yii::$app->getResponse();
                $response->headers->set('Content-Type', mime_content_type($fullpath_source));
                $response->data = file_get_contents($fullpath_source);
            // }  
            $response->format = Response::FORMAT_RAW;
            return $response->send();
        }
        $response->format = Response::FORMAT_RAW;
        return $response->send();
    }

    // {... heart-tag
    public function actionSetdyktagforimage()
    {
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;

        $imageid = null;
        $tagname = null;
        if(isset($_POST['imageid'])) $imageid = $_POST['imageid'];
        if(isset($_POST['tagname'])) $tagname = $_POST['tagname'];
        $post = implode("||",Yii::$app->request->post());

        try
        {
            if (is_null($tagname)) throw new \yii\web\NotFoundHttpException;

            $parent_tag = "DigiYiiKam";
            $chk_parent_exists = Tags::findOne(['name' => $parent_tag, 'pid' => 0]);
            if (is_null($chk_parent_exists))
            {
                // create the parent-tag
                $Tags_Model = new Tags();
                $Tags_Model->name = $parent_tag;
                $Tags_Model->pid = 0;
                if (!$Tags_Model->save())
                {
                    throw new \yii\web\NotFoundHttpException;
                }
                $chk_parent_exists = Tags::findOne(['name' => $parent_tag, 'pid' => 0]);
            }

            $chk_exists = Tags::findOne(['name' => $tagname, 'pid' => $chk_parent_exists->id]);
            if (is_null($chk_exists))
            {
                // create the parent-tag
                $Tags_Model = new Tags();
                $Tags_Model->name = $tagname;
                $Tags_Model->pid = $chk_parent_exists->id;
                if (!$Tags_Model->save())
                {
                    throw new \yii\web\NotFoundHttpException;
                }
                $chk_exists = Tags::findOne(['name' => $tagname, 'pid' => $chk_parent_exists->id]);
            }
            
            
            if (is_null($imageid)) throw new \yii\web\NotFoundHttpException;
            $image_tag_toggle = ImageTags::findOne(['imageid' => $imageid, 'tagid' => $chk_exists->id]);
            if (is_null($image_tag_toggle))
            {
                $imageTags_Model = new ImageTags();
                $imageTags_Model->imageid = $imageid;
                $imageTags_Model->tagid = $chk_exists->id;
                if (!$imageTags_Model->save())
                {
                    throw new yii\web\BadRequestHttpException;
                }
            }
            else
            {
                $image_tag_toggle->delete();
            }
            $chk_image_tag_toggle = ImageTags::findOne(['imageid' => $imageid, 'tagid' => $chk_exists->id]);

            return \Yii::createObject([
                'class' => 'yii\web\Response',
                'format' => \yii\web\Response::FORMAT_JSON,
                'data' => [
                    'message' => 'OK',
                    'tag_image_combination_set' => !is_null($chk_image_tag_toggle),
                    'code' => 200,
                ],
            ]);
        }
        catch(\Exception $e)
        {
            return \Yii::createObject([
                'class' => 'yii\web\Response',
                'format' => \yii\web\Response::FORMAT_JSON,
                'data' => [
                    'message' => $e,
                    'code' => 400,
                ],
            ]);
        }
    }
    // ...}
}

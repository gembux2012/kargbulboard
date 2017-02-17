<?php

namespace App\Modules\News\Models;

use App\Models\User;
use T4\Core\Exception;
use T4\Fs\Helpers;
use T4\Http\Uploader;
use T4\Mvc\Application;
use T4\Orm\Model;


class Story
    extends Model
{
    static protected $schema = [
        'table' => 'news_stories',
        'columns' => [
            'title' => ['type' => 'string'],
            'published' => ['type' => 'datetime'],
            'create' => ['type' => 'datetime'],
            'text' => ['type' => 'text'],
            'nopaid' => ['type' => 'integer'],
            'price' => ['type' => 'integer'],
            'mfone' => ['type' => 'string'],
            'fone' => ['type' => 'string'],
            'vip' => ['type' => 'int'],
            'view' => ['type' => 'int'],

        ],
        'relations' => [
            'topic' => ['type' => self::BELONGS_TO, 'model' => Topic::class],
            'image' => ['type' => self::HAS_MANY, 'model' => Image::class],
            'user' => ['type' => self::BELONGS_TO, 'model' => User::class],
        ]
    ];

    public function uploadImage($formFieldName)
    {

        $request = Application::getInstance()->request;
        if (!$request->existsFilesData() || !$request->isUploaded($formFieldName) || $request->isUploadedArray($formFieldName))
            return $this;

        try {
            $uploader = new Uploader($formFieldName);
            $uploader->setPath('/public/news/stories/images');
            $image = $uploader();
            if ($this->image)
                $this->deleteImage();
            $this->image = $image;
        } catch (Exception $e) {
            $this->image = null;
        }
        return $this;
    }
    /*
    public function beforeSave()
    {
        if ($this->isNew()) {
            $this->published = date('Y-m-d H:i:s', time());
        }
        return true;
    }
  */

    public function validate()
    {
         //var_dump(trim(str_replace("&nbsp;", '', strip_tags($this->text))));die;
        if (strlen(trim(str_replace("&nbsp;", '', strip_tags($this->text)))) == 0) {

            throw new \T4\Orm\Exception('1');

        }
        if(Application::getInstance()->request->post->vip == 1) {
            if (strlen(trim(str_replace("&nbsp;", '', strip_tags(Application::getInstance()->request->post->title)))) == 0
                && strlen(trim(str_replace("&nbsp;", '', strip_tags($this->title)))) == 0
            ) {

                throw new \T4\Orm\Exception('2');

            }
        }

            return true;

    }

    public static function wordcount($text)
    {
        return str_word_count(str_replace("&nbsp;", '', strip_tags($text)),null,"0123456789АаБбВвГгДдЕеЁёЖжЗзИиЙйКкЛлМмНнОоПпРрСсТтУуФфХхЦцЧчШшЩщЪъЫыЬьЭэЮюЯя");

    }

    public static function price($item)
    {
        $price = Application::getInstance()->config ->price;
        $vip=$item->vip!=0 ? $price->vip : 0;
        return $price = ['words' => (self::wordcount($item->text)-$price->nopaid) * $price->word,
            'photos' => $item->image->count()* $price->photo,
            'vip' => $vip,
            'all' => (self::wordcount($item->text)-$price->nopaid) * $price->word + $item->image->count() * $price->photo + $vip,
        ];
    }

    private static function priceWodrs($item){
        $price = Application::getInstance()->config ->price;
        $count=self::wordcount($item->text)-$price->nopaid;
       /* if($count>0 && $count <=$price->word[0]) {
            $price1 =$count*
            }
*/
        foreach($price->word as $key =>$value){
            if ($count<=$key){
                
            }

        }


    }



    public function beforeDelete()
    {
        $this->removeDirectory('/site/image/'.$this->Pk);
        return parent::beforeDelete();
    }

    function removeDirectory($path)
    {
        $dir=\T4\Fs\Helpers::getRealPath($path);
        if ($objs = glob($dir . "/*")) {
            foreach ($objs as $obj) {
                is_dir($obj) ? removeDirectory($obj) : unlink($obj);
            }
        }
        rmdir($dir);

    }
}
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
            'text' => ['type' => 'text'],
            'nopaid' => ['type' => 'integer'],
            'mfone' => ['type' => 'string'],
            'fone' => ['type' => 'string'],
            'vip' => ['type' => 'int'],

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

    public function beforeSave()
    {
        if ($this->isNew()) {
            $this->published = date('Y-m-d H:i:s', time());
        }
        return true;
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
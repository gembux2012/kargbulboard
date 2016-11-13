<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 07.04.16
 * Time: 19:05
 */

namespace App\Modules\News\Models;


use T4\Orm\Model;
use App\Models\User;
use T4\Core\Exception;
use T4\Fs\Helpers;
use T4\Mvc\Application;


class Image
    extends Model
{
    static protected $schema = [
        'columns' =>[
        'image' => ['type' => 'string'],
        'text' => ['type' => 'string'],
            ],
        'relations' =>[
            'story' => ['type'=>self::BELONGS_TO, 'model'=>Story::class],
        ]
    ];

    public function beforeDelete(){

            try {

                Helpers::removeFile(ROOT_PATH_PUBLIC . $this->image);
            } catch (\T4\Fs\Exception $e) {
                return false;
            }

        return true;
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 07.04.16
 * Time: 19:05
 */

namespace App\Modules\News\Models;


use T4\Orm\Model;

class Image
    extends Model
{
    static protected $schema = [
        'columns' =>[
        'image' => ['type' => 'string'],
        'tetx' => ['type' => 'string'],
            ],
        'relations' =>[
            'story' => ['type'=>self::BELONGS_TO, 'model'=>Story::class],
        ]
    ];

}
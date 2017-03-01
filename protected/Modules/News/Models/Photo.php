<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 15.04.16
 * Time: 6:56
 */

namespace App\Modules\News\Models;


use T4\Orm\Model;

class Photo
    extends Model
{
    static protected $schema = [
        'table' => 'images',
        'columns' => [
            'image' => ['type' => 'string'],
            'text' => ['type' => 'string'],
        ]];

}
<?php

namespace App\Modules\News;

class Module
    extends \App\Components\Module
{

    public function getAdminMenu()
    {
        return [

                ['title' => 'Рубрики объявлений', 'url' => '/admin/news/topics'],

        ];
    }

} 
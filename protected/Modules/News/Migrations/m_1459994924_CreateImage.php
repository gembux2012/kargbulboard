<?php

namespace App\Modules\News\Migrations;

use T4\Orm\Migration;

class m_1459994924_CreateImage
    extends Migration
{

    public function up()
    {
        if (!$this->existsTable('images')){
            $this->createTable('images', [
                'image' => ['type' => 'string'],
                'tetx' => ['type' => 'string'],
                '__story_id' => ['type' => 'link'],

            ]);

                }
    }

    public function down()
    {
        $this->dropTable("images");
    }

}
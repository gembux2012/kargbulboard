<?php

namespace App\Modules\News\Migrations;

use T4\Orm\Migration;
use App\Modules\News\Models\Topic;

class m_1423048590_createTopics
    extends Migration
{

    public function up()
    {
        if (!$this->existsTable('news_topics')) {
            $this->createTable('news_topics', [
                'title' => ['type' => 'string']
            ], [

            ], [
                'tree'
            ]);

            $topic = new Topic();
            $topic->title = 'Все объявления';
            $topic->save();
        }
    }

    public function down()
    {
        $this->dropTable('news_topics');
    }

}
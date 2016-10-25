<?php

namespace App\Modules\News\Migrations;

use T4\Orm\Migration;
use App\Models\User;
use App\Models\Role;


class m_1423048663_createStories
    extends Migration
{

    public function up()
    {
        if(!$this->existsTable('news_stories')) {
            $this->createTable('news_stories', [
                'title' => ['type' => 'string', 'length' => 1024],
                'published' => ['type' => 'datetime'],
                'text' => ['type' => 'text'],
                'nopaid' => ['type' => 'integer', 'default' => 0],
                'vip' => ['type' => 'integer', 'default' => 0],
                'mfone' => ['type'=>'string'],
                'fone' => ['type'=>'string'],
                '__topic_id' => ['type' => 'link'],
                '__user_id' => ['type' => 'link'],


            ], [
                'topic' => ['columns' => ['__topic_id']]
            ]);
        }

        $role = new Role();
        $role->name = 'admin';
        $role->title = 'Администратор';
        $role->save();

        $user = new User();
        $user->email = 'admin@t4.org';
        $user->password = \T4\Crypt\Helpers::hashPassword('123456');
        $user->roles->append($role);
        $user->save();



    }

    public function down()
    {
        $this->dropTable('news_stories');
    }

}
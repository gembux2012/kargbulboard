<?php

namespace App\Modules\News\Controllers;

use App\Modules\News\Models\Story;
use App\Modules\News\Models\Topic;
use App\Modules\News\Models\Image;
use T4\Http\E404Exception;
use T4\Mvc\Controller;
use T4\Orm\Migration;


class Index
    extends Controller
{
    const PAGE_SIZE = 10;

    public function actionDefault($limit = null)
    {
        $this->data->items = Story::findAll(
            [
                'order' => 'published DESC',
                'limit' => $limit,
            ]
        );
    }

    public function actionOne($id)
    {
        $this->data->item = Story::findByPK($id);
        if (empty($this->data->item)) {
            throw new E404Exception('Новость не найдена');
        }
    }

    public function  actionNewsAsMenu($mode=0)
    {
       // $this->data->items=Topic::findAllTree();
         if(Topic::findByPK(1)->hasChildren()) {
            $this->data->items = Topic::findByPK(1)->findAllChildren(); //die;
        } else {
            $this->data->items = Topic::findByPK(1);
        }
        $this->data->mode=$mode;
        //var_dump($this->data->items);die;


    }

    public function  actionPageForTopic($id = 1, $page = 1, $user_id = null)
    {
        $item = Topic::findByPK($id);
       // var_dump($item);die;
        $lft = $item->__lft;
        $rgt = $item->__rgt;

        $offset = ($page - 1) * self::PAGE_SIZE;
        $limit = self::PAGE_SIZE;
        if (null == $user_id) {
            $this->data->itemsCount = Story::findALLByQuery('SELECT * FROM news_stories  WHERE __topic_id
            IN (SELECT __id FROM news_topics WHERE __lft >=' . $lft . ' AND __rgt <= ' . $rgt . ' ORDER BY __lft)')->count();

            $this->data->items = Story::findAllByQuery('SELECT * FROM news_stories  WHERE __topic_id
            IN (SELECT __id FROM news_topics WHERE __lft >=' . $lft . ' AND __rgt <= ' . $rgt . ' ORDER BY __lft)  ORDER BY published DESC LIMIT ' . $offset . ',' . $limit);

        } else {
            $this->data->itemsCount = Story::findALLByQuery('SELECT * FROM news_stories  WHERE __topic_id
            IN (SELECT __id FROM news_topics WHERE __lft >=' . $lft . ' AND __rgt <= ' . $rgt .' AND __user_id='.$user_id. ' ORDER BY __lft)')->count();

            $this->data->items = Story::findAllByQuery('SELECT * FROM news_stories  WHERE __topic_id
            IN (SELECT __id FROM news_topics WHERE __lft >=' . $lft . ' AND __rgt <= ' . $rgt . ' AND __user_id=' . $user_id . ' ORDER BY __lft)  ORDER BY published DESC LIMIT ' . $offset . ',' . $limit);
        }

        $this->data->pageSize = self::PAGE_SIZE;
        $this->data->activePage = $page;
        $this->data->topic = $id;

    }

    public function actionAddMessage($id = null )
    {


        if (null === $id || 'new' == $id) {
            $this->data->item = new Story();

        } else {
            $this->data->item = Story::findByPK($id);
            $topic=Story::findByPK($id)->topic;
            $this->data->parenttopic=$topic->findAllParents();

        }

    }

    public function actionSave()
    {
        if (!empty($this->app->request->post->id)) {
            $item = Story::findByPK($this->app->request->post->id);
        } else {
            $item = new Story();
        }

        $item->fill($this->app->request->post);
        $item->user=$this->app->user->Pk;

        $item->save();
        $this->data->id=$item->Pk;
        $this->data->topicid=$item->topic->Pk;

    }

    public function actionBreadCrambs($id)
    {
        //var_dump($id);die;
        $last_item=Topic::findByPK($id);
        $this->data->bcs=Topic::findByPK($id)->findAllParents();
        $this->data->bc=$last_item;
        $this->data->topic_id=$id;

    }



}

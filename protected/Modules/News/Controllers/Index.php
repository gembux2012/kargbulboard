<?php

namespace App\Modules\News\Controllers;

use App\Modules\News\Models\Story;
use App\Modules\News\Models\Topic;
use App\Modules\News\Models\Image;
use T4\Core\Collection;
use T4\Core\Std;
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
        $item = Story::findByPK($id);
        if (empty($item)) {
            throw new E404Exception('Новость не найдена');
        }

        if($this->app->user) {
          if($item->user->Pk != $this->app->user->Pk){
              $item->view = $item->view + 1;
          }
        } else {
            $item->view = $item->view + 1;
        }

        $item->save();
        $this->data->item=$item;



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
        $lft = $item->__lft;
        $rgt = $item->__rgt;

        $offset = ($page - 1) * self::PAGE_SIZE;
        $limit = self::PAGE_SIZE;

        $this->data->itemsCount = Story::findALLByQuery('SELECT * FROM news_stories  WHERE published IS NOT NULL AND __topic_id
            IN (SELECT __id FROM news_topics WHERE __lft >=' . $lft . ' AND __rgt <= ' . $rgt . ' ORDER BY __lft)')->count();

            $this->data->items = Story::findAllByQuery('SELECT * FROM news_stories   WHERE published IS NOT NULL AND __topic_id
            IN (SELECT __id FROM news_topics WHERE __lft >=' . $lft . ' AND __rgt <= ' . $rgt . ' ORDER BY __lft)  ORDER BY published DESC LIMIT ' . $offset . ',' . $limit);




        $this->data->pageSize = self::PAGE_SIZE;
        $this->data->activePage = $page;
        $this->data->topic = $id;

    }

    public function  actionFind($find,$page=1)
    {
        if(!empty($find)) {
            $offset = ($page - 1) * self::PAGE_SIZE;
            $limit = self::PAGE_SIZE;                                                   // "%'.$search.'%"'

            $this->data->itemsCount = Story::findALLByQuery('SELECT * FROM news_stories WHERE text  LIKE "%' . $find . '%" AND published IS NOT NULL')->count();
            $this->data->items = Story::findALLByQuery('SELECT * FROM news_stories WHERE text LIKE "%' . $find . '%" AND published IS NOT NULL ORDER BY published DESC LIMIT ' . $offset . ',' . $limit);
        }
    }

    public function  actionPrice($id=null )
    {
        if($id!=null) {
            $item = Story::findByPK($id);

            $this->data->price = Story::price($item);
        }


    }

    public function  actionVip()
    {
     $items=Story::findALLByQuery('SELECT * FROM news_stories WHERE vip=1 AND publiched IS NOT NULL ORDER BY published');
        $vip=[];
        foreach($items as $key=>$item){
            $vip[$key]['image'][]=$item->image[0]->image;
            $vip[$key]['text'][]=$item->text;
        }
        $this->data->vip=$vip;


    }

    public function actionSlide()
    {
        $items=new Collection();
        $itemsold=Story::findAllByQuery('SELECT * FROM news_stories  WHERE vip=1 AND published IS NOT NULL');
       $items=$itemsold;
       // $items=Story::findAllByQuery('SELECT * FROM news_stories  WHERE vip=1');
       // var_dump($itemsold);
        $count=$items->count();
        if($count>3) {
            $i = 0;
            foreach ($itemsold as $item) {
                if ($i != 3 - $count % 3 && $count % 3 != 0) {
                    $items->append($itemsold[$i]);

                } else {
                    break;
                }
                $i++;
            }
        }

       $this->data->vip=$items;
    }




    public function actionAddMessage($id = null )
    {


        if (null === $id || 'new' == $id) {
            $this->data->item = new Story();

        } else {
            $item = Story::findByPK($id);
            $this->data->item = $item;
            $topic=Story::findByPK($id)->topic;
            $this->data->parenttopic=$topic->findAllParents();
            $this->data->words=Story::wordcount($item->text);

        }

    }

    public function actionMyMessage($page=1){

        if(Story::findAllBy__user_id($this->app->user->Pk)->count()==0){
            $this->app->flash->message="У вас нет объявлений";

        } else {
            $this->data->itemsCount = Story::findAllBy__user_id($this->app->user->Pk)->count();
            $this->data->pageSize = self::PAGE_SIZE;
            $this->data->activePage = $page;
            $this->data->items = Story::findAllBy__user_id($this->app->user->Pk,[
                'order' => 'published DESC',
                'offset'=> ($page-1)*self::PAGE_SIZE,
                'limit'=> self::PAGE_SIZE
            ]);

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
        $item->user = $this->app->user->Pk;

        if (!isset($this->app->request->post->vip))
            $item->vip = '0';
        else
            $item->vip = '1';

        $price = Story::price($item);
        $item->price = $price['all'];

        try {
            $item->save();

            $this->data->id = $item->Pk;
            $this->data->topicid = $item->topic->Pk;
            $tmp=Story::countAll(['where' => 'published IS NOT NULL']);
            $tmp1=$this->app->config->price->nopaidcount;
            if (Story::countAll(['where' => 'published IS NOT NULL']) <= $this->app->config->price->nopaidcount || $item->price <= 0 ||
                $item->user->role[0] == 'admin'
            ) {
                   $this->data->published = false;

            } else  if($this->app->request->post->publish == 1) {
                    $this->data->published = true;
                } else {
                    $this->data->published = false;
                }



        } catch (\T4\Orm\Exception $e) {

            $this->data->error = $e->getMessage();
        }
    }

    public function actionPublished($id)
    {
        $item = Story::findByPK($id);
        $item->published = date('Y-m-d H:i:s', time());
        $item->save();
    }


    public function actionBreadCrambs($id)
    {
        $last_item=Topic::findByPK($id);
        $this->data->bcs=Topic::findByPK($id)->findAllParents();
        $this->data->bc=$last_item;
        $this->data->topic_id=$id;

    }

    public function actionDelete($id){
        $item=Story::findByPK($id);
        $item->delete();
    }

    public function actionLoadImage($id){
        $this->data->item=Story::findByPK($id);

    }

    public function actionSaveImageText(){
        if(!isset($this->app->request->post->text)) {
            $item = Image::findByPK($this->app->request->post->id);
            $item->text = $this->app->request->post->text;
            $item->save();
        }
    }

    public function actionPhotoDelete($id){

        $item=Image::findByPK($id);
        $item->delete();
        $this->data->fotos=$item->count();
        $this->data->words=Story::wordcount($item->story->text);



    }


    public function actionUpLoadImage()
    {
       // var_dump($this->app->request->post->id);die;
        $path = '/site/image/' .  $this->app->request->post->story . '/';

        $realUploadPath = \T4\Fs\Helpers::getRealPath($path);
        if (!is_dir($realUploadPath)) {
            try {
                \T4\Fs\Helpers::mkDir($realUploadPath);
            } catch (\T4\Fs\Exception $e) {
                throw new Exception($e->getMessage());
            }
        }
        if (isset($_FILES["myfile"])) {
            $ret = array();

//	This is for custom errors;
            /*$custom_error= array();
             $custom_error['jquery-upload-file-error']="File already exists";
             echo json_encode($custom_error);
             die();
            */
            $this->data->err= $_FILES["myfile"]["error"];
            //You need to handle  both cases
            //If Any browser does not support serializing of multiple files using FormData()
            //$subclass = $this->app->request->post->subclass;


            if (!is_array($_FILES["myfile"]["name"])) //single file
            {
                $fileName = $_FILES["myfile"]["name"];
                if(!file_exists($realUploadPath. $fileName)) {
                    if (move_uploaded_file($_FILES["myfile"]["tmp_name"], $realUploadPath . $fileName)) {
                        $ret[] = $fileName;
                        $item = new Image();
                        $item->fill($this->app->request->post);
                        $item->image = $path . $fileName;
                        $item->save();
                      //  $this->data->fotos = $item->count();
                       // $this->data->words = Story::wordcount($item->story->text);

                    } else{
                        $this->data->err="Не удалось сохранить файл";
                    }
                }

            } else  //Multiple files, file[]
            {
                $fileCount = count($_FILES["myfile"]["name"]);
                for ($i = 0; $i < $fileCount; $i++) {
                    $fileName = $_FILES["myfile"]["name"][$i];
                    move_uploaded_file($_FILES["myfile"]["tmp_name"][$i], $realUploadPath . $fileName);
                    $ret[] = $fileName;

                    $in_img=imagecreatefromjpeg($path.$fileName);
                    $out_img=imagecreatetruecolor(500,500);



                    imagedestroy($in_img);
                    imagedestroy($out_img);
                    if(!file_exists($path . $fileName)) {
                        $item = new Image();
                        $item->fill($this->app->request->post);
                        $item->image = $path . $fileName;
                        //$item->story=$this->app->request->post->story;
                        $item->save();
                        $this->data->fotos=$item->count();
                        $this->data->words=Story::wordcount($item->story->text);


                    }

                }

            }
            $this->data->ret= $ret;
        }

    }




}

<?php


namespace App\Modules\Payment\Controllers;


use T4\Mvc\Controller;
use App\Modules\News\Models\Story;

class Index
    extends Controller
{
   public function actionDefault($id)
   {
      $this->data->item=Story::findByPK($id);
   }

   public function actionSetPaid()
   {
      $item = Story::findByPK($this->app->request->post->label);
      $item->published = date('Y-m-d H:i:s', time());
      $item->save();

   }
}
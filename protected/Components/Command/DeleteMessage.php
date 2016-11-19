<?php
namespace App\Components\Command;

use T4\Console\Application;
use T4\Console\Command;
use App\Modules\News\Models\Story;
class DeleteMessage
    extends Command
{
    public function actionDelete()
    {
       $interval=$this->app->config->published;
        $items=Story::findAllByQuery('SELECT * FROM news_stories  WHERE DATE_ADD(DATE(published),INTERVAL '.$interval.' DAY ) <= DATE(NOW())');
        foreach($items as $story){
            $item=Story::findByPK($story->Pk);
            $item->delete();
            $item->save();
        }
    }

}
?>
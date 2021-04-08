<?php

namespace Haxibiao\Breeze\Observers;

use Haxibiao\Content\Collectable;
use Haxibiao\Content\Collection;

class CollectableObserver
{
/**
 * Handle the Post "created" event.
 *
 * @param  \App\Post  $post
 * @return void
 */
    public function created(Collectable $collectable)
    {
        $this->countPosts($collectable);
    }

    /**
     * Handle the Post "updated" event.
     *
     * @param  \App\Post  $post
     * @return void
     */
    public function updated(Collectable $collectable)
    {
        $this->countPosts($collectable);
    }

    /**
     * Handle the Post "deleted" event.
     *
     * @param  \App\Post  $post
     * @return void
     */
    public function deleted(Collectable $collectable)
    {
        $this->countPosts($collectable);
    }

    public function countPosts(Collectable $collectable)
    {
        $collection = Collection::find($collectable->collection_id);
        if ($collection) {
            $collection->count_posts = $collection->posts()->count();
            $collection->save();
        }
    }
}

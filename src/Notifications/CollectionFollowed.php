<?php

namespace Haxibiao\Breeze\Notifications;

use Haxibiao\Content\Collection;
use Illuminate\Bus\Queueable;

class CollectionFollowed extends BreezeNotification
{
    use Queueable;
    public static $notify_event = "关注了合集";
    protected $collection;

    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    public function toArray($notifiable)
    {
        return [
            'collection_id' => $this->collection->id,
        ];
    }
}

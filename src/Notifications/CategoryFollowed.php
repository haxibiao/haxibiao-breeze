<?php

namespace Haxibiao\Breeze\Notifications;

use Haxibiao\Breeze\User;
use Haxibiao\Content\Category;
use Illuminate\Bus\Queueable;

class CategoryFollowed extends BreezeNotification
{
    use Queueable;

    public static $notify_action = "专题被关注";

    protected $category;
    protected $user;

    public function __construct(Category $category, User $user)
    {
        $this->category = $category;
        $this->user     = $user;
    }

    public function toArray($notifiable)
    {
        return [
            'type'        => 'other',
            'subtype'     => 'category_followed',
            'category_id' => $this->category->id,
            'user_id'     => $this->user->id,
            'message'     => $this->user->link() . "关注了您的专题" . $this->category->link(),
        ];
    }
}

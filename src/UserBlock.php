<?php

namespace Haxibiao\Breeze;

use Haxibiao\Breeze\Traits\UserBlockResolvers;
use Haxibiao\Breeze\User;
use Haxibiao\Content\Article;
use Illuminate\Database\Eloquent\Model;

class UserBlock extends Model
{
    use UserBlockResolvers;

    public $fillable = [
        "user_id",
        "user_block_id",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function userBlock()
    {
        return $this->belongsTo(User::class, 'user_block_id', 'id');
    }

    public function articleBlock()
    {
        return $this->belongsTo(Article::class, 'article_block_id', 'id');
    }

    public function articleReport()
    {
        return $this->belongsTo(Article::class, 'article_report_id', 'id');
    }
}

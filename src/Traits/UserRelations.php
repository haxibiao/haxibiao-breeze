<?php

namespace Haxibiao\Breeze\Traits;

use App\Action;
use App\Chat;
use App\Querylog;
use Haxibiao\Breeze\CheckIn;
use Haxibiao\Breeze\OAuth;
use Haxibiao\Breeze\UserBlock;
use Haxibiao\Breeze\UserData;
use Haxibiao\Breeze\UserProfile;
use Haxibiao\Breeze\UserRetention;
use Haxibiao\Content\Issue;
use Haxibiao\Content\IssueInvite;
use Haxibiao\Content\Solution;
use Haxibiao\Media\MovieHistory;
use Haxibiao\Sns\Feedback;
use Haxibiao\Sns\Follow;
use Haxibiao\Store\Order;
use Haxibiao\Task\Contribute;
use Haxibiao\Wallet\Exchange;
use Haxibiao\Wallet\Gold;
use Haxibiao\Wallet\Transaction;
use Haxibiao\Wallet\Wallet;
use Haxibiao\Wallet\Withdraw;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait UserRelations
{
    //关系

    public function user_profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function hasOneProfile()
    {
        return $this->hasOne(UserProfile::class);
    }

    public function user_data(): HasOne
    {
        return $this->hasOne(UserData::class);
    }

    public function user_retention(): HasOne
    {
        return $this->hasOne(UserRetention::class);
    }

    public function withdraws(): HasManyThrough
    {
        return $this->hasManyThrough(Withdraw::class, Wallet::class);
    }

    public function checkIns(): HasMany
    {
        return $this->hasMany(CheckIn::class);
    }
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function retentions()
    {
        return $this->hasMany(UserRetention::class);
    }

    public function exchanges()
    {
        return $this->hasMany(Exchange::class);
    }

    public function contributes()
    {
        return $this->hasMany(Contribute::class);
    }

    public function feedbacks()
    {
        return $this->hasMany(Feedback::class);
    }

    //问答
    public function issues()
    {
        return $this->hasMany(Issue::class);
    }
    public function resolutions()
    {
        return $this->hasMany(Solution::class);
    }
    public function solutions()
    {
        return $this->hasMany(Solution::class);
    }

    public function querylogs()
    {
        return $this->hasMany(Querylog::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function userBlock()
    {
        return $this->hasMany(UserBlock::class);
    }

    public function followingCategories()
    {
        return $this->hasMany(Follow::class)->where('followed_type', 'categories');
    }

    public function followingCollections()
    {
        return $this->hasMany(Follow::class)->where('followed_type', 'collections');
    }

    public function movieHistory(): HasMany
    {
        return $this->hasMany(MovieHistory::class);
    }

    public function followingUsers()
    {
        return $this->hasMany(Follow::class)->where('followed_type', 'users');
    }

    public function golds(): hasMany
    {
        return $this->hasMany(Gold::class);
    }

    public function actions()
    {
        return $this->hasMany(Action::class);
    }

    public function chats()
    {
        return $this->belongsToMany(Chat::class, 'chat_user')
            ->withPivot('unreads')
            ->orderBy('updated_at', 'desc');
    }

    public function wallets(): HasMany
    {
        return $this->hasMany(Wallet::class);
    }

    public function issueInvites()
    {
        return $this->hasMany(IssueInvite::class, 'user_id', 'id');
    }

    public function oauth(): HasMany
    {
        return $this->hasMany(OAuth::class);
    }
}

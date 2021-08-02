<?php

namespace Haxibiao\Breeze\Traits;

use App\Action;
use App\Chat;
use App\Curation;
use App\Querylog;
use App\UserProfile;
use Haxibiao\Breeze\CheckIn;
use Haxibiao\Breeze\OAuth;
use Haxibiao\Breeze\Role;
use Haxibiao\Breeze\User;
use Haxibiao\Breeze\UserData;
use Haxibiao\Breeze\UserRetention;
use Haxibiao\Content\Issue;
use Haxibiao\Content\IssueInvite;
use Haxibiao\Content\Solution;
use Haxibiao\Media\MovieHistory;
use Haxibiao\Sns\Feedback;
use Haxibiao\Sns\Follow;
use Haxibiao\Sns\UserBlock;
use Haxibiao\Store\Order;
use Haxibiao\Task\Contribute;
use Haxibiao\Wallet\Exchange;
use Haxibiao\Wallet\Gold;
use Haxibiao\Wallet\Transaction;
use Haxibiao\Wallet\Wallet;
use Haxibiao\Wallet\Withdraw;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait UserRelations
{
    //关系

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
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

    public function retention(): HasOne
    {
        return $this->hasOne(UserRetention::class);
    }

    public function withdraws(): HasManyThrough
    {
        if (class_exists('\App\Wallet')) {
            return $this->hasManyThrough(\App\Withdraw::class, \App\Wallet::class);
        }
        return $this->hasManyThrough(Withdraw::class, Wallet::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(\App\Level::class);
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

    public function questions()
    {
        return $this->hasMany(\App\Question::class);
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
        return $this->hasMany(Follow::class)->where('followable_type', 'categories');
    }

    public function followingCollections()
    {
        return $this->hasMany(Follow::class)->where('followable_type', 'collections');
    }

    public function movieHistory(): HasMany
    {
        return $this->hasMany(MovieHistory::class);
    }

    public function followingUsers()
    {
        return $this->hasMany(Follow::class)->where('followable_type', 'users');
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
        if (class_exists('\App\Wallet')) {
            return $this->hasMany(\App\Wallet::class);
        }

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

    public function curations(): HasMany
    {
        return $this->hasMany(Curation::class)->with('question');
    }
    // 绑定的子账号们（马甲账号
    public function vestAccounts(): HasMany
    {
        return $this->hasMany(User::class, 'master_id');
    }
    // 主账号
    public function masterAccount(): BelongsTo
    {
        return $this->belongsTo(User::class, 'master_id');
    }

    //绑定的员工账号
    public function staffAccounts(): HasMany
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    //我的求片
    public function findMovies()
    {
        return $this->belongsToMany("App\Movie", 'movie_user')->withTimestamps()
            ->withPivot(['report_status']);
    }

    //定位功能
    public function locations()
    {
        return $this->morphMany(\App\Location::class, 'located');
    }

    public function getLocationAttribute()
    {
        return $this->locations->last();
    }
}

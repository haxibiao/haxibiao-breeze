<?php

namespace Haxibiao\Breeze;

use Haxibiao\Breeze\Traits\SignInFacade;
use Haxibiao\Breeze\Traits\SignInRepo;
use Haxibiao\Breeze\Traits\SignInResolvers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SignIn extends Model
{
    use SignInRepo, SignInFacade, SignInResolvers;

    protected $fillable = [
        'user_id',
        'created_at',
        'updated_at',
        'gold_reward',
        'contribute_reward',
        'reward_rate',
    ];

    //最大签到天数
    const MAX_SIGNIN_DAYS = 7;
    // JIRA:DZ-1630 区分新老用户贡献点
    const CONTRIBUTE_REWARD = 10;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    //scope

    public function scopeUserId($query, $value)
    {
        return $query->where('user_id', $value);
    }

    //attrs

    public function getDateAttribute()
    {
        return $this->created_at->toDateString();
    }

    public function getYearAttribute()
    {
        return $this->created_at->year;
    }

    public function getMonthAttribute()
    {
        return $this->created_at->month;
    }

    public function getDayAttribute()
    {
        return $this->created_at->day;
    }

    public function getSignedAttribute()
    {
        return isset($this->id) ? true : false;
    }

    //static method

    public static function todaySigned($userId)
    {
        return SignIn::where('user_id', $userId)->where('created_at', '>=', today())->first();
    }

    public static function yesterdaySigned($userId)
    {
        return SignIn::userId($userId)
            ->whereBetween('created_at', [today()->subDay(), today()])
            ->first();
    }
}

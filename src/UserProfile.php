<?php

namespace Haxibiao\Breeze;

use Carbon\Carbon;
use Haxibiao\Breeze\Model;
use Haxibiao\Breeze\User;
use Haxibiao\Content\Post;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

/**
 * 提供用户基础资料和公开统计数据，为task何dimension提供方便的数据获取
 */
class UserProfile extends Model
{
    protected $guarded = [];
    //首先兼容答赚...
    protected $table = 'user_profiles';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function boot()
    {
        parent::boot();
        //保存时触发
        self::saving(function ($post) {
            if (Schema::hasColumn("user_profiles", 'source')) {
                if (empty($post->source)) {
                    $post->source = "unkown";
                }
            }
        });
    }

    public function setIntroductionAttribute($value)
    {
        $this->attributes['introduction'] = empty($value) ? '' : $value;
    }

    public function setBirthdayAttribute($value)
    {
        //前端数据格式组装数据格式如下: 2019年5月09日
        $value                        = str_replace(['年', '月'], '-', $value);
        $value                        = str_replace('日', '', $value);
        $age                          = now()->diffInYears($value);
        $this->attributes['birthday'] = Carbon::parse($value);
        $this->attributes['age']      = $age;
    }

    public function isIosDevice()
    {
        return $this->os == 'ios';
    }

    /**
     * 用户当日激励视频次数，每天清空
     */
    public function getTodayRewardVideoCountAttribute()
    {
        if ($this->last_reward_video_time && $this->last_reward_video_time < today()) {
            //重置为0
            $this->update(['today_reward_video_count' => 0]);
            return 0;
        }
        return $this->attributes['today_reward_video_count'] ?? 0;
    }

    public function getLatestGoldsAttribute()
    {
        $latestGolds = collect($this->golds)->sortByDesc('id');
        $golds       = $latestGolds->take(3);
        return $golds;
    }

    public function getLatestAnswersAttribute()
    {
        $latestAnswers = collect($this->answers)->sortByDesc('created_at');
        $Answers       = $latestAnswers->take(3);
        return $Answers;
    }

    public function setVerifiedAtAttribute($value)
    {
        $this->attributes['verified_at'] = $value;
    }

    public function getPostsCountAttribute()
    {
        $callable = [$this, 'postsCount'];
        return $this->getCachedAttribute('postsCount', $callable);
    }

    public function getBirthDayAttribute()
    {
        $birthday = $this->attributes['birthday'] ?? null;
        if ($birthday) {
            return $birthday;
        }
        $birth_day   = $this->attributes['birth_on_day'] ?? null;
        $birth_month = $this->attributes['birth_on_month'] ?? null;
        $birth_year  = $this->attributes['birth_on_year'] ?? null;
        if ($birth_day && $birth_month && $birth_year) {
            return Carbon::parse($birth_year . "-" . $birth_month . "-" . $birth_day . " 00:00:00");
        }
        return null;
    }

    public function postsCount()
    {
        $count = Post::where('user_id', $this->user_id)->publish()->count();

        return $count;
    }

    public function syncFollowersCount()
    {
        $this->followers_count = $this->user->followers()->count();
    }
}

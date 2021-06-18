<?php

namespace Haxibiao\Breeze\Traits;

use Haxibiao\Breeze\User;
use Haxibiao\Breeze\UserProfile;
use Haxibiao\Question\Question;
use Haxibiao\Wallet\Wallet;
use Illuminate\Support\Facades\DB;

/**
 * 答题部分的特性
 */
trait PlayWithQuestion
{
    /**
     * 排行榜-用户 财富，答题
     */
    public static function getUsersByRank($rank)
    {
        $builder = User::whereStatus(0);
        if ($rank) {

            //总提现收入排行(成功过提现)
            if ($rank == 'TOTAL_WITHDRAW') {
                $wallet = Wallet::orderBy('total_withdraw_amount', 'desc')
                    ->take(100)
                    ->get();
                $userIds     = $wallet->pluck('user_id')->toArray();
                $ids_ordered = implode(',', $userIds);
                $builder     = $builder->whereIn('id', $userIds)->orderByRaw(DB::raw("FIELD(id, $ids_ordered)"));
            }

            //答题连续答对数排行
            if ($rank == 'DOUBLE_HIT_ANSWER') {
                $profiles = UserProfile::orderBy('answers_count_today', 'desc')
                    ->take(100)
                    ->get();
                $userIds     = $profiles->pluck('user_id')->toArray();
                $ids_ordered = implode(',', $userIds);
                $builder     = $builder->whereIn('id', $userIds)->orderByRaw(DB::raw("FIELD(id, $ids_ordered)"));
            }
        } else {
            //方便调试环境是否连上prod db
            $builder = $builder->latest('id');
        }
        return $builder;
    }

    /**
     * 用户的题目
     */
    public function resolveUserQuestions($root, array $args, $context, $info)
    {
        $order  = data_get($args, 'order');
        $filter = data_get($args, 'filter');
        return User::listQuestions($root, $order, $filter);
    }

    public static function listQuestions($user, $order, $filter)
    {
        //只要不是用户已删除的问题都看到
        $qb = $user->questions()->with('video')->latest();

        if (isset($order)) {
            $qb->orderByDesc($order);
        }

        if (isset($filter)) {
            $qb->whereSubmit($filter);
        } else {
            $qb->where('submit', '<>', Question::DELETED_SUBMIT);
        }
        return $qb;
    }

    /**
     * 查询用户详情
     */
    public static function resolveUser($root, array $args, $context, $info)
    {
        app_track_event('用户页', '访问他人主页');
        if (isset($args['id'])) {
            return User::visitById($args['id']);
        }
    }

    /**
     * 访问他人主页
     */
    public static function visitById($id): User
    {
        $user = User::find($id);
        if (str_contains(request()->get('query'), "UserInfoQuery")) {
            $user->profile->increment('visited_count');
        }
        return $user;
    }

    public static function resolveCheckAccountExists($root, array $args, $context, $info)
    {
        app_track_event("个人中心", "查询账号是否存在");
        return User::checkAccountExists($args['account']);
    }

    public static function checkAccountExists($account)
    {
        return User::where('account', $account)->exists();
    }
}

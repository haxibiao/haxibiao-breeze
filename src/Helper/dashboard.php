<?php

use Haxibiao\Breeze\User;
use Haxibiao\Content\Post;
use Haxibiao\Sns\Comment;
use Haxibiao\Sns\Message;

// 简单生成运营简报的报表用

/**
 * 用户新增趋势
 *
 * @param integer $range
 * @return array
 */
function get_users_trend($range = 7)
{
    //没有数据的日期默认值为0
    for ($j = $range - 1; $j >= 0; $j--) {
        $intervalDate        = date('m-d', strtotime(now() . '-' . $j . 'day'));
        $data[$intervalDate] = 0;
    }

    $users = User::selectRaw(" distinct(date_format(created_at,'%m-%d')) as daily,count(*) as count ")
        ->whereDate('created_at', '>=', now()->subDay($range - 1))
        ->groupBy('daily')->get();

    $users->each(function ($user) use (&$data) {
        $data[$user->daily] = $user->count;
    });

    if (count($data) < $range) {
        $data[now()->toDateString()] = 0;
    }

    return $data;
}

/**
 * 动态新增趋势
 *
 * @param integer $range
 * @return array
 */
function get_posts_trend($range = 7)
{
    //没有数据的日期默认值为0
    for ($j = $range - 1; $j >= 0; $j--) {
        $intervalDate        = date('m-d', strtotime(now() . '-' . $j . 'day'));
        $data[$intervalDate] = 0;
    }

    $items = Post::selectRaw(" distinct(date_format(created_at,'%m-%d')) as daily,count(*) as count ")
        ->whereDate('created_at', '>=', now()->subDay($range - 1))
        ->groupBy('daily')->get();

    $items->each(function ($item) use (&$data) {
        $data[$item->daily] = $item->count;
    });

    if (count($data) < $range) {
        $data[now()->toDateString()] = 0;
    }

    return $data;
}

/**
 * 评论新增趋势
 *
 * @param integer $range
 * @return array
 */
function get_comments_trend($range = 7)
{
    //没有数据的日期默认值为0
    for ($j = $range - 1; $j >= 0; $j--) {
        $intervalDate        = date('m-d', strtotime(now() . '-' . $j . 'day'));
        $data[$intervalDate] = 0;
    }

    $items = Comment::selectRaw(" distinct(date_format(created_at,'%m-%d')) as daily,count(*) as count ")
        ->whereDate('created_at', '>=', now()->subDay($range - 1))
        ->groupBy('daily')->get();

    $items->each(function ($item) use (&$data) {
        $data[$item->daily] = $item->count;
    });

    if (count($data) < $range) {
        $data[now()->toDateString()] = 0;
    }

    return $data;
}

/**
 * 消息新增趋势
 *
 * @param integer $range
 * @return array
 */
function get_messages_trend($range = 7)
{
    //没有数据的日期默认值为0
    for ($j = $range - 1; $j >= 0; $j--) {
        $intervalDate        = date('m-d', strtotime(now() . '-' . $j . 'day'));
        $data[$intervalDate] = 0;
    }

    $items = Message::selectRaw(" distinct(date_format(created_at,'%m-%d')) as daily,count(*) as count ")
        ->whereDate('created_at', '>=', now()->subDay($range - 1))
        ->groupBy('daily')->get();

    $items->each(function ($item) use (&$data) {
        $data[$item->daily] = $item->count;
    });

    if (count($data) < $range) {
        $data[now()->toDateString()] = 0;
    }

    return $data;
}

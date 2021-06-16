<?php

namespace Haxibiao\Breeze\Traits;

use App\Comment;
use App\Curation;
use App\Feedback;
use App\Follow;
use App\Like;
use App\Medal;
use App\Question;
use App\Report;
use App\Withdraw;
use Haxibiao\Breeze\User;
use Haxibiao\Content\Article;
use Haxibiao\Content\Post;
use Illuminate\Support\Arr;

trait NotificationAttrs
{
    public function getDataStringAttribute()
    {
        return json_encode($this->data, JSON_UNESCAPED_UNICODE);
    }

    public function getWithdrawAttribute()
    {
        if ($withdraw_id = data_get($this, 'data.withdraw_id')) {
            return Withdraw::find($withdraw_id);
        }
    }

    //通知关联的评论
    public function getCommentAttribute()
    {
        if ($comment_id = data_get($this, 'data.comment_id')) {
            return Comment::find($comment_id);
        }
    }

    public function getFeedbackAttribute()
    {
        if ($feedback_id = data_get($this, 'data.feedback_id')) {
            return Feedback::find($feedback_id);
        }
    }

    public function getCurationAttribute()
    {
        if ($curation_id = data_get($this, 'data.curation_id')) {
            return Curation::find($curation_id);
        }
    }

    public function getFollowAttribute()
    {
        if ($follow_id = data_get($this, 'data.follow_id')) {
            return Follow::find($follow_id);
        }
    }

    public function getQuestionAttribute()
    {
        if ($question_id = data_get($this, 'data.question_id')) {
            return Question::find($question_id);
        }
    }

    public function getReportAttribute()
    {
        if ($report_id = data_get($this, 'data.report_id')) {
            return Report::find($report_id);
        }
        //旧代码里还尊重过data.id
        if ($report_id = data_get($this, 'data.id')) {
            return Report::find($report_id);
        }
    }

    public function getLikeObjAttribute()
    {
        if ($like_id = data_get($this, 'data.like_id')) {
            return Like::find($like_id);
        }
    }

    public function getMedalAttribute()
    {
        if ($medal_id = data_get($this, 'data.medal_id')) {
            return Medal::find($medal_id);
        }
    }

    public function getTimeAgoAttribute()
    {
        return time_ago($this->created_at);
    }

    /**
     * 被通知的对象（基本就是被通知的用户）
     */
    public function getNotifiableAttribute()
    {
        return $this->notifiable;
    }

    //通知关联的用户
    public function getUserAttribute()
    {
        //尊重data里缓存的用户信息，避免多余查询
        $user = new User([
            'id'     => data_get($this, 'data.user_id'),
            'name'   => data_get($this, 'data.user_name', User::DEFAULT_NAME),
            'avatar' => data_get($this, 'data.user_avatar', url(User::AVATAR_DEFAULT)),
        ]);
        return $user;
    }

    //通知关联的文章
    public function getArticleAttribute()
    {
        if ($article_id = data_get($this, 'data.article_id')) {
            return Article::find($article_id);
        }
    }

    //通知关联的动态
    public function getPostAttribute()
    {

        //被多维操作关联上的动态 - 兼容以前的 固定访问 Notification下的Post 属性习惯的
        if ("posts" === data_get($this, 'data.type')) {
            if ($data_id = data_get($this, 'data.id')) {
                return Post::find($data_id);
            }
        }

        //动态上发生的通知 - 印象视频代码
        if ($post_id = data_get($this, 'data.post_id')) {
            return Post::find($post_id);
        }

        //兼容旧的 - 点赞，评论，存过data.id
        if ($post_id = data_get($this, 'data.id')) {
            return Post::find($post_id);
        }

    }

    //通知关联的回复
    public function getReplyAttribute()
    {
        if ($reply_id = data_get($this, 'data.reply_id')) {
            return Comment::find($reply_id);
        }
    }

    public function getUserRewardAttribute()
    {
        return Arr::get($this->data, 'user_reward');
    }
}

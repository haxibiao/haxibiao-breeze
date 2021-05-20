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
use Haxibiao\Content\Article;
use Illuminate\Support\Arr;

trait NotificationAttrs
{
    public function getDataStringAttribute()
    {
        return json_encode($this->data, JSON_UNESCAPED_UNICODE);
    }

    public function getWithdrawAttribute()
    {
        $target = $this->target;

        if ($target instanceof Withdraw) {
            return $target;
        }

        if (is_null($target) && isset($this->data['withdraw_id'])) {
            return $this->target = Withdraw::find($this->data['withdraw_id']);
        }
    }

    public function getCommentAttribute()
    {
        $target = $this->target;

        if ($target instanceof Comment) {
            return $target;
        }

        if (is_null($target) && isset($this->data['comment_id'])) {
            return $this->target = Comment::find($this->data['comment_id']);
        }
    }

    public function getFeedbackAttribute()
    {
        $target = $this->target;

        if ($target instanceof Feedback) {
            return $target;
        }

        if (is_null($target) && isset($this->data['feedback_id'])) {
            return $this->target = Feedback::find($this->data['feedback_id']);
        }
    }

    public function getCurationAttribute()
    {
        $target = $this->target;

        if ($target instanceof Curation) {
            return $target;
        }

        if (is_null($target) && isset($this->data['curation_id'])) {
            return $this->target = Curation::find($this->data['curation_id']);
        }
    }

    public function getFollowAttribute()
    {
        $target = $this->target;

        if ($target instanceof Follow) {
            return $target;
        }

        if (is_null($target) && isset($this->data['follow_id'])) {
            return $this->target = Follow::withTrashed()->find($this->data['follow_id']);
        }
    }

    public function getQuestionAttribute()
    {
        $target = $this->target;
        if ($target instanceof Question) {
            return $target;
        }

        if (is_null($target) && isset($this->data['question_id'])) {
            return $this->target = Question::find($this->data['question_id']);
        }
    }

    public function getReportAttribute()
    {
        $target = $this->target;
        if ($target instanceof Report) {
            return $target;
        }
        $report_id = null;
        if (isset($this->data['report_id'])) {
            $report_id = $this->data['report_id'];
        } else if (isset($this->data['id'])) {
            $report_id = $this->data['id'];
        }

        if (is_null($target) && !is_null($report_id)) {
            return $this->target = Report::find($report_id);
        }
    }

    public function getLikeObjAttribute()
    {
        $target = $this->target;

        if ($target instanceof Like) {
            return $target;
        }

        if (is_null($target) && isset($this->data['like_id'])) {
            return $this->target = Like::find($this->data['like_id']);
        }
    }

    public function getMedalAttribute()
    {
        $target = $this->target;

        if ($target instanceof Medal) {
            return $target;
        }

        if (is_null($target) && isset($this->data['medal_id'])) {
            return $this->target = Medal::find($this->data['medal_id']);
        }
    }

    public function getTimeAgoAttribute()
    {
        return time_ago($this->created_at);
    }

    public function getUserAttribute()
    {
        return $this->notifiable;
    }

    public function getArticleAttribute()
    {
        $target = $this->target;

        if ($target instanceof Article) {
            return $target;
        }

        if (is_null($target) && isset($this->data['article_id'])) {
            return $this->target = Article::find($this->data['article_id']);
        }
    }

    public function getReplyAttribute()
    {
        $target = $this->target;

        if ($target instanceof Comment) {
            return $target;
        }

        if (is_null($target) && isset($this->data['reply_id'])) {
            return $this->target = Comment::find($this->data['reply_id']);
        }
    }

    public function getUserRewardAttribute()
    {
        return Arr::get($this->data, 'user_reward');
    }
}

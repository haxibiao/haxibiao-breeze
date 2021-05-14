<?php

namespace Haxibiao\Breeze\Listeners;

use Haxibiao\Breeze\Events\NewComment;
use Haxibiao\Breeze\Notifications\ArticleCommented;
use Haxibiao\Breeze\Notifications\CommentedNotification;
use Haxibiao\Breeze\Notifications\FeedbackCommentNotification;
use Haxibiao\Breeze\Notifications\ReplyCommentNotification;
use Haxibiao\Content\Article;
use Haxibiao\Content\Post;
use Haxibiao\Question\Notifications\QuestionCommented;
use Haxibiao\Question\Question;
use Haxibiao\Sns\Comment;
use Haxibiao\Sns\Feedback;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendNewCommentNotification implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void

     */
    public $afterCommit = true;

    public function __construct()
    {

    }

    /**
     * Handle the event.
     *
     * @param  newComment  $event
     * @return void
     */
    public function handle(NewComment $event)
    {
        //TODO: 可以对很多新的评论的时候，合并一堆的评论来聚合法一个通知，比如一个发布，一段时间汇总总评论数
        $comment = $this->comment = $event->comment;
        if (!is_null($this->comment)) {
            $commentable = $comment->commentable;

            //作品
            if (!is_null($commentable)) {
                $this->notifyCommentable($comment);
            }

            //评论作者
            if (!empty($comment->comment_id)) {
                $this->notifyCommentAuthor($comment);
            }

            //回复
            if (!empty($comment->reply_id)) {
                $this->notifyReplyAuthor($comment);
            }
        }
    }

    protected function notifyCommentable($comment)
    {
        $commentable = $comment->commentable;
        $canNotify   = $commentable->user != $comment->user;

        //TODO: 即时发送每个通知，需要改为汇总到 Listener里去决策
        if ($canNotify) {
            if ($commentable instanceof Feedback) {
                //发送反馈评论通知
                $commentable->user->notify(new FeedbackCommentNotification($commentable, $comment));
            } else if ($commentable instanceof Question) {
                //审题评论也给通知
                $commentable->user->notify(new QuestionCommented($commentable, $comment));
            } else if ($commentable instanceof Article) {
                // 发送通知，如果是作者本人就不发
                $commentable->user->notify(new ArticleCommented($comment));
            } else if ($commentable instanceof Comment) {
                $commentable->user->notify(new CommentedNotification($comment));
            } else if ($commentable instanceof Post) {
                $commentable->user->notify(new CommentedNotification($comment));
            }
        }
    }

    protected function notifyCommentAuthor($comment)
    {
        $parentComment = $comment->parent_comment;
        //通知父评论作者
        if ($parentComment->user != $comment->user) {
            $parentComment->user->notify(new ReplyCommentNotification($comment));
        }
    }

    protected function notifyReplyAuthor($comment)
    {
        $reply     = $comment->reply;
        $canNotify = !is_null($reply) && $reply->user_id != $comment->user_id;
        if ($canNotify) {
            $reply->user->notify(new ReplyCommentNotification($comment));
        }
    }
}

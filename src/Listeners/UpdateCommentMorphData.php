<?php

namespace Haxibiao\Breeze\Listeners;

use App\Comment;
use App\Events\NewComment;
use App\Feedback;
use App\Post;
use App\Question;

class UpdateCommentMorphData
{
    // public $queue = 'listeners';
    public $delay = 10;

    protected $comment;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  NewComment  $event
     * @return void
     */
    public function handle(NewComment $event)
    {
        $comment     = $this->comment     = $event->comment;
        $commentable = $comment->commentable;

        //更新父评论
        if (!is_null($comment->comment_id)) {
            $this->updateComment($comment->comment);
        }

        //题目
        if ($commentable instanceof Question) {
            return $this->updateQuestion($commentable);
        }

        //反馈
        if ($commentable instanceof Feedback) {
            return $this->updateFeedback($commentable);
        }

        //动态
        if ($commentable instanceof Post) {
            return $this->updatePost($commentable);
        }

    }

    protected function updateQuestion(Question $question)
    {
        $count_comments = $question->comments()->count();
        if ($count_comments != $question->count_comments) {
            $question->count_comments = $count_comments;
            $question->save();
        }
    }

    protected function updateFeedback(Feedback $feedback)
    {
        $feedback->comments_count         = $feedback->comments()->count();
        $feedback->publish_comments_count = $feedback->publishComments()->count();
        $feedback->save();
    }

    protected function updateComment(Comment $comment)
    {
        $comment->comments_count = $comment->comments()->count();
        $comment->save();
        return $comment;
    }

    protected function updatePost(Post $post)
    {
        $post->count_comments = $post->comments()->count();
        $post->save();
        return $post;
    }
}

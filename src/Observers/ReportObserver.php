<?php

namespace Haxibiao\Breeze\Observers;

use Haxibiao\Breeze\User;
use Haxibiao\Content\Article;
use Haxibiao\Sns\Comment;
use Haxibiao\Sns\Report;

class ReportObserver
{
    /**
     * Handle the report "created" event.
     *
     * @param  \App\Report  $report
     * @return void
     */
    public function created(Report $report)
    {
        if ($report->reportable instanceof Article) {
            $article = $report->reportable;
            $article->count_reports += 1;
            $article->save();

        } else if ($report->reportable instanceof Comment) {
            $comment = $report->reportable;
            $comment->count_reports += 1;
            $comment->save();
        } else if ($report->reportable instanceof User) {
            $user = $report->reportable;
            $user->profile->count_reports += 1;
            $user->save();
        }

    }

    /**
     * Handle the report "updated" event.
     *
     * @param  Report  $report
     * @return void
     */
    public function updated(Report $report)
    {
        //
    }

    /**
     * Handle the report "deleted" event.
     *
     * @param  Report  $report
     * @return void
     */
    public function deleted(Report $report)
    {
        if ($report->reportable instanceof Article) {
            $article = $report->reportable;
            $article->count_reports -= 1;
            $article->save();
        } else if ($report->reportable instanceof Comment) {
            $comment = $report->reportable;
            $comment->count_reports -= 1;
            $comment->save();
        } else if ($report->reportable instanceof User) {
            $user = $report->reportable;
            $user->count_reports += 1;
            $user->save();
        }

    }

    /**
     * Handle the report "restored" event.
     *
     * @param  Report  $report
     * @return void
     */
    public function restored(Report $report)
    {
        //
    }

    /**
     * Handle the report "force deleted" event.
     *
     * @param  Report  $report
     * @return void
     */
    public function forceDeleted(Report $report)
    {
        //
    }
}

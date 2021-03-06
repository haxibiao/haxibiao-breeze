<?php

namespace Haxibiao\Breeze\Listeners;

use Haxibiao\Breeze\Events\NewReport;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendNewReportNotification implements ShouldQueue
{
    public $afterCommit = true;
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
     * @param  NewReport  $event
     * @return void
     */
    public function handle(NewReport $event)
    {
        //
    }
}

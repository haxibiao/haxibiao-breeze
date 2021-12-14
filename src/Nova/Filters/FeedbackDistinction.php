<?php

namespace Haxibiao\Breeze\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class FeedbackDistinction extends Filter
{
    public $name = '反馈类别';
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    /**
     * Apply the filter to the given query.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
    {
        if ($value == 1) {
            return $query->where('content', 'like', "影视播放bug%");
        } else if ($value == 2) {
            return $query->where('content', 'not like', "影视播放bug%");
        }
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request)
    {
        return [
            '系统反馈' => 1,
            '用户反馈' => 2,
        ];
    }
}
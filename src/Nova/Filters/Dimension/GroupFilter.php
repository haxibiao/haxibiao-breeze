<?php

namespace Haxibiao\Breeze\Nova\Filters\Dimension;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\BooleanFilter;

class GroupFilter extends BooleanFilter
{
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
        $value = array_filter($value, function ($v) {
            return $v;
        });
        $value = array_keys($value);
        if (count($value)) {
            return $query->whereIn('group', $value);
        }

        return $query;
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
            '激励视频' => '激励视频',
        ];
    }
}

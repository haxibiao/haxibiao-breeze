<?php

namespace Haxibiao\Breeze\Nova;

use App\User;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Resource;

class BlackList extends Resource
{
    public static $model  = 'Haxibiao\Breeze\BlackList';
    public static $title  = 'id';
    public static $search = [
        'id', 'user_id',
    ];

    public static function label()
    {
        return "黑名单";
    }

    public static $group = '用户中心';

    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),
            // BelongsTo::make('用户', 'user', User::where('id', '>=', 30)),
            Select::make('用户', 'user_id')->options(
                User::where('id', '<=', 30)->pluck('name', 'id')
            )->displayUsingLabels(),
            // Text::make('设备号', 'device_id')->exceptOnForms(),
            // DateTime::make('到期时间', 'expired_at'),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }
}

<?php

namespace Haxibiao\Breeze\Nova;

use App\Nova\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;

class AdConfig extends Resource
{
    public static $model  = 'App\AdConfig';
    public static $title  = 'name';
    public static $search = [
        'id',
    ];

    public static $group = '配置中心';
    public static function label()
    {
        return "广告";
    }
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),
            Text::make('键', 'name'),
//            Text::make('值','value'),
            Select::make('值', 'value')->options([
                '头条' => '头条',
                '腾讯' => '腾讯',
                '百度' => '百度',
                '混合' => '混合',
            ])->displayUsingLabels(),
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

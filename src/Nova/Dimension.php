<?php

namespace Haxibiao\Breeze\Nova;

use App\Nova\Resource;
use Haxibiao\Breeze\Nova\Metrics\SiteSpiderTrend;
use Haxibiao\Breeze\Nova\Metrics\SiteTrafficTrend;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;

class Dimension extends Resource
{
    public static $model  = 'App\Dimension';
    public static $title  = 'name';
    public static $search = [
        'name', 'group',
    ];

    public static $group = "数据中心";
    public static function label()
    {
        return '维度数据';
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),
            Text::make('分组', 'group')->exceptOnForms(),
            Text::make('名称', 'name')->exceptOnForms(),
            Text::make('日', 'date')->exceptOnForms(),
            Text::make('时', 'hour')->exceptOnForms(),
            Number::make('数值', 'value')->exceptOnForms(),
            Number::make('更新次数', 'count')->exceptOnForms(),
            DateTime::make('更新时间', 'updated_at')->exceptOnForms(),
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
        return [

            //下面可以添加当前项目关心的维度报表

            // (new AdShowPartition)->width('1/4'),
            // (new AdClickPartition)->width('1/4'),
            // (new AdShowTrend)->width('1/4'),
            // (new AdClickTrend)->width('1/4'),

            // (new RewardVideoPartition)->width('1/4'),
            // (new FullVideoPartition)->width('1/4'),
            // (new RewardVideoTrend)->width('1/4'),
            // (new FullVideoTrend)->width('1/4'),
            (new SiteSpiderTrend)->width('1/4'),
            (new SiteTrafficTrend)->width('1/4'),
        ];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [
            new Filters\Dimension\CreatedDateFilter,
            new Filters\Dimension\GroupFilter,
        ];
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

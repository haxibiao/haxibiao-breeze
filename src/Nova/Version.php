<?php

namespace Haxibiao\Breeze\Nova;

use App\Nova\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Status;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;

class Version extends Resource
{
    public static $model  = 'App\Version';
    public static $title  = 'name';
    public static $search = [
        'id',
    ];
    public static $globallySearchable = false;

    public static $group = '配置中心';
    public static function label()
    {
        return '版本';
    }

    public static $parent = null;
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),
            Text::make('版本', 'name'),
            Number::make('build'),
            Text::make('包名', 'package'),
            Textarea::make('更新描述', 'description'),
            Number::make('大小', 'size')->step(0.01)->displayUsing(function ($value) {
                return formatBytes($value);
            }),
            Select::make('系统', 'os')->options($this::getOses()),
            Select::make('状态', 'status')->options($this::getStatuses())->onlyOnForms(),
            Status::make('状态', function () {
                return $this->getStatusToChinese();
            })->loadingWhen(['运行中'])
                ->failedWhen([
                    '已下架',
                    '已删除',
                ]),
            Select::make('类型', 'type')->options($this::getTypes())->displayUsingLabels(),
            Boolean::make('是否强制更新', 'is_force'),
            Text::make('下载地址', 'url'),
            DateTime::make('发布时间', 'created_at')->exceptOnForms(),
            DateTime::make('更新时间', 'updated_at')->hideWhenCreating(),
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

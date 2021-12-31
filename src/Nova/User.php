<?php

namespace Haxibiao\Breeze\Nova;

use Haxibiao\Breeze\Nova\Actions\User\AddMasterAccount;
use Haxibiao\Breeze\Nova\Actions\User\UpdateUserRole;
use Haxibiao\Breeze\Nova\Actions\User\UpdateUserStatus;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Avatar;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Resource;

class User extends Resource
{
    public static $model  = \Haxibiao\Breeze\User::class;
    public static $title  = 'name';
    public static $search = [
        'id', 'name', 'email', 'account',
    ];

    public static $group = "用户中心";

    public static function label()
    {
        return '用户';
    }

    public static $with = ['profile'];

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

            Avatar::make('头像', 'avatar')->disk('cos')->hideFromIndex()
                ->store(function (Request $request, $model) {
                    return $model->saveAvatar($request->file('avatar'));
                })->thumbnail(function () {
                return $this->avatar_url;
            }),

            Text::make('昵称', 'name')
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make('账号/手机', 'account')
                ->rules('required', 'max:255'),

            Text::make('身份', function () {return $this->role_name;}),

            Text::make('状态', function () {return $this->status_name;}),

            Text::make('渠道来源', 'profile.package'),
            Text::make('APP版本', 'profile.version'),
            Text::make('Email')
                ->sortable()
                ->rules('required', 'email', 'max:254')
                ->creationRules('unique:users,email')
                ->updateRules('unique:users,email,{{resourceId}}'),

            Password::make('Password')
                ->onlyOnForms()
                ->creationRules('required', 'string', 'min:8')
                ->updateRules('nullable', 'string', 'min:8'),

            DateTime::make('创建', 'created_at')
                ->hideWhenUpdating()->hideWhenCreating(),
            DateTime::make('登录', 'updated_at')
                ->hideWhenUpdating()->hideWhenCreating(),
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
        return [
            new AddMasterAccount,
            new UpdateUserStatus,
            new UpdateUserRole,
        ];
    }
}
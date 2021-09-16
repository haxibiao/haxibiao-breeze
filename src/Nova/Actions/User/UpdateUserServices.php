<?php

namespace Haxibiao\Breeze\Nova\Actions\User;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\Actionable;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Nova;

class UpdateUserServices extends Action
{

    public $name = '服务项目变更';
    use InteractsWithQueue, Queueable, SerializesModels, Actionable;

    public function uriKey()
    {
        return str_slug(Nova::humanize($this));
    }

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        if (!isset($fields->user_id)) {
            return Action::danger('没有选择操作用户');
        }
        $user = User::find($fields->user_id);
        $user->services()->sync($models->pluck('id')->toArray());

    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        $store_ids = \App\Store::where("user_id", getUser()->id)->pluck('id')->toArray();
        return [
            Select::make('技师', 'user_id')
                ->options(
                    User::where('role_id', User::TECHNICIAN_USER)
                        ->rightJoin('technician_profiles', function ($join) {
                            return $join->on('users.id', 'technician_profiles.user_id');
                        })
                        ->whereIn('technician_profiles.store_id', $store_ids)
                        ->get()
                        ->pluck('name', 'id')->toArray(),
                ),
        ];

    }
}

<?php

namespace Haxibiao\Breeze\Nova\Actions\User;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\Actionable;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Nova;

class AddMasterAccount extends Action
{

    public $name = '绑定马甲到运营账户';
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
        $masterId = $fields->master_id;

        if (!isset($masterId)) {
            return Action::danger('请选择一个运营账号为主账号');
        }

        DB::beginTransaction();
        $count = 0;
        $total = $models->count();
        info($masterId);
        try {
            foreach ($models as $model) {
                //只要不是运营以上用户，都能绑定到运营账户下被模拟
                // if ($model->role_id == User::VEST_STATUS)
                {
                    $model->master_id = $masterId;
                    $count++;
                }
                $model->save();
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            DB::rollBack();
            return Action::danger('数据批量变更失败，数据回滚');
        }
        DB::commit();
        return Action::message('一共选择了' . $total . '条数据' . $count . '条数据成功更新');

    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Select::make('要关联的运营主账号', 'master_id')->options(
                function () {
                    return User::whereIn('role_id', [User::ADMIN_STATUS, User::EDITOR_STATUS])
                        ->pluck('name', 'id')->toArray();
                }
            ),
        ];

    }
}

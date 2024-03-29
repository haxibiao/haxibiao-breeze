<?php

namespace Haxibiao\Breeze\Nova\Actions\Feedback;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Nova;

class FeedbackStatus extends Action
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $name = '审核反馈';
    public function uriKey()
    {
        return str_slug(Nova::humanize($this));
    }

    public function handle(ActionFields $fields, Collection $models)
    {
        DB::beginTransaction();
        try {
            foreach ($models as $model) {
                $model->status = $fields->status;
                $model->save();
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            DB::rollBack();
            return Action::danger('数据批量变更失败，数据回滚');
        }
        DB::commit();
        return Action::message('修改成功!,成功修改掉' . count($models) . '条数据');
    }

    public function fields()
    {
        return [
            Select::make('状态', 'status')->options(
                [
                    0 => '待处理',
                    1 => '以驳回',
                    2 => '已处理',
                ])
                ->withMeta([
                    'value' => 2,
                ]),
        ];
    }
}

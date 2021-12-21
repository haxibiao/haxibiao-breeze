<?php

namespace Haxibiao\Breeze\Nova\Actions\Feedback;

use App\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Nova;

class FeedbackComment extends Action
{
    use InteractsWithQueue, Queueable, SerializesModels;
    public $name = '回复反馈';
    public function uriKey()
    {
        return str_slug(Nova::humanize($this));
    }

    public function handle(ActionFields $fields, Collection $models)
    {
        if (!isset($fields->body) or !isset($fields->status)) {
            return Action::danger('状态或者内容不能为空');
        }

        foreach ($models as $model) {
            $comments                   = new Comment();
            $comments->body             = $fields->body;
            $comments->status           = $fields->status;
            $comments->commentable_type = 'feedbacks';
            $comments->commentable_id   = $model->id;
            $comments->save();
            $model->update(['status' => 2]);
        }

        return Action::message('共有' . count($models) . '条反馈创建评论成功');
    }

    public function fields()
    {
        return [
            Textarea::make('评论', 'body'),
            Select::make('状态', 'status')
                ->withMeta([
                    'value' => 1,
                ])
                ->options(
                    [
                        1 => '公开',
                        0 => '私密',
                    ]
                ),
        ];
    }
}
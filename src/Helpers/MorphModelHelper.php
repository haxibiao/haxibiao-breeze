<?php
namespace Haxibiao\Breeze\Helpers;

class MorphModelHelper
{
    public static function morphModels()
    {
        return [
            'users'        => 'App\User',
            'feedbacks'    => 'App\Feedback',
            'comments'     => 'App\Comment',
            'questions'    => 'App\Question',
            'audit'        => 'App\Audit',
            'explanations' => 'App\Explanation',
            'sign_ins'     => 'App\SignIn',
            'videos'       => 'App\Video',
            'posts'        => 'App\Post',
            'categories'   => 'App\Category',
            'tags'         => 'App\Tag',
            'withdraws'    => 'App\Withdraw',
            'reply'        => 'App\Comment',
            'curations'    => 'App\Curation',
            'follows'      => 'App\Follow',
            'likes'        => 'App\Like',
            'medals'       => 'App\Medal',
            'articles'     => 'App\Article',
            'movies'       => 'App\Movie',
            'luckydraws'   => 'App\LuckyDraw',
            'invitations'  => 'App\Invitation',
            'exchanges'    => 'App\Exchange',
        ];
    }

    public static function getAlias($model)
    {
        $models     = array_flip(self::morphModels());
        $modelClass = get_class($model);

        return array_key_exists($modelClass, $models) ? $models[$modelClass] : null;
    }

    public static function getModel($alias)
    {
        $models = self::morphModels();

        return array_key_exists($alias, $models) ? $models[$alias] : null;
    }
}

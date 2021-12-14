<?php

namespace Haxibiao\Breeze;

use Illuminate\Database\Eloquent\Model;

class Seo extends Model
{
    protected $fillable = ['group', 'name', 'value'];

    public static function getValue($name, $group = null)
    {
        $qb = self::whereName($name);
        if ($group) {
            $qb = $qb->whereGroup($group);
        }
        $item = $qb->first();
        return $item ? $item->value : '';
    }

    public function resolveSeos($rootValue, $args, $context, $resolveInfo)
    {
        $name  = $args['name'] ?? null;
        $group = $args['group'] ?? null;
        return Seo::query()->when($name, function ($qb) use ($name) {
            $qb->where('name', $name);
        })->when($group, function ($qb) use ($group) {
            $qb->where('group', $group);
        })->get();
    }
}

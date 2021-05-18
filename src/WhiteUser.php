<?php

namespace Haxibiao\Breeze;

use Illuminate\Database\Eloquent\Model;

class WhiteUser extends Model
{
    use \Laravel\Nova\Actions\Actionable;

    protected $fillable = [
        'account',
        'name',
    ];
}

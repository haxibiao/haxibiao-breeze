<?php

namespace Haxibiao\Breeze;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppId extends Model
{
    use HasFactory;

    public function scopeByValue($query, $value)
    {
        return $query->where('value', $value);
    }
}

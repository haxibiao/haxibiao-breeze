<?php

namespace Haxibiao\Breeze;

use Haxibiao\Breeze\Traits\OAuthRepo;
use Haxibiao\Breeze\Traits\OAuthResolvers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OAuth extends Model
{
    use OAuthResolvers;
    use OAuthRepo;

    protected $guarded = [
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeOfType($query, $value)
    {
        return is_array($value) ? $query->whereIn('oauth_type', $value) : $query->where('oauth_type', $value);
    }

    public function scopeUnionId($query, $type, $value)
    {
        $field = OAuth::getUnionIdField($type);
        return $query->where($field, $value);
    }

}

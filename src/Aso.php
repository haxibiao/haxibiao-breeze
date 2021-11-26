<?php

namespace Haxibiao\Breeze;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Aso extends Model
{
    protected $fillable = ['group', 'name', 'value'];

    public function QQNumber($root, array $args, $context)
    {
        return optional(self::where('name', '动态修改群qq号')->first())->value;
    }

    public static function getValue($name, $domain = null)
    {
        $qb = Aso::whereName($name);
        if ($domain) {
            $qb = $qb->whereDomain($domain);
        }
        $item = $qb->first();
        return $item->value ?? '';
    }

    public function saveDownloadImage($file, $name)
    {
        if ($file) {
            $value = 'images/' . env('APP_NAME') . '.qrcode.png';
            $disk  = Storage::cloud();
            $disk->put($value, @file_get_contents($file->path()));
            return $disk->url($value);
        }
    }
}

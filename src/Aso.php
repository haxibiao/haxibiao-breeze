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

    public static function getValue($group, $name)
    {
        $item = Aso::whereGroup($group)->whereName($name)->first();
        return $item ? $item->value : '';
    }

    public function saveDownloadImage($file, $name)
    {
        if ($file) {
            $aso      = Aso::where('name', $name)->first();
            $aso_path = parse_url($aso->value, PHP_URL_PATH);
            Storage::put($aso_path, file_get_contents($file->path()));
            return Storage::url($aso_path);
        }
    }
}

<?php

namespace Haxibiao\Breeze;

use Haxibiao\Breeze\Traits\ModelHelpers;
use Illuminate\Database\Eloquent\Model as BaseModel;

class Model extends BaseModel
{
    protected $guarded = [];
    use ModelHelpers;

    //兼容以前部分model 自己操作json字段
    public function setJsonData($key, $value)
    {
        $data       = (array) $this->json;
        $data[$key] = $value;
        $this->json = $data;

        return $this;
    }
}

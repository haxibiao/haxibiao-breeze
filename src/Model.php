<?php

namespace Haxibiao\Base;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Support\Facades\Auth;

class Model extends BaseModel
{
    //只保存数据，不更新时间
    public function saveDataOnly()
    {
        //获取model里面的事件
        $dispatcher = self::getEventDispatcher();

        //不触发事件
        self::unsetEventDispatcher();
        $this->timestamps = false;
        $this->save();

        //启用事件
        self::setEventDispatcher($dispatcher);
    }

    //time
    public function getTimeAgoAttribute()
    {
        return diffForHumansCN($this->created_at);
    }

    public function timeAgo()
    {
        return diffForHumansCN($this->created_at);
    }

    public function createdAt()
    {
        return diffForHumansCN($this->created_at);
    }

    public function updatedAt()
    {
        return diffForHumansCN($this->updated_at);
    }

    public function editedAt()
    {
        return diffForHumansCN($this->edited_at);
    }

    //self
    public function isSelf()
    {
        return Auth::check() && Auth::id() == $this->user_id;
    }

    public function isOfUser($user)
    {
        return $user && $user->id == $this->user_id;
    }

    //json
    public function jsonData($key = null)
    {
        if (!empty($this->json) && is_string($this->json)) {
            $jsonData = json_decode($this->json, true);
            if (empty($jsonData)) {
                $jsonData = [];
            }

            if (!empty($key)) {
                if (array_key_exists($key, $jsonData)) {
                    return $jsonData[$key];
                }
                return null;
            }
            return $jsonData;
        }
    }

    public function setJsonData($key, $value)
    {
        $data       = (array) $this->json;
        $data[$key] = $value;
        $this->json = $data;

        return $this;
    }
    /*
     * 查询时排除相关列,减少传输消耗
     * @param  [type] $query [description]
     * @param  array  $value [description]
     * @return [type]        [description]
     */
    public function scopeExclude($query, $value = array())
    {
        //获取该表所有列
        $columns = $this->getTableColumns();
        //需要获取列名
        $real_columns = array_diff($columns, (array) $value);

        $tableName      = $this->getTable();
        $format_colomns = array_map(function ($name) use ($tableName) {
            return $tableName . '.' . $name;
        }, $real_columns);
        return $query->select($format_colomns);
    }

    public function getTableColumns()
    {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }
}

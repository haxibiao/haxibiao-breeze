<?php

namespace Haxibiao\Breeze;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $guarded = [
    ];

    const NORMAL_USER     = 1;
    const MODERATOR_USER  = 2;
    const EDITOR_USER     = 3;
    const ROOT_USER       = 4;
    const TECHNICIAN_USER = 9; //技师

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function isNormalUser()
    {
        return $this->id == self::NORMAL_USER;
    }

    public function isTechnicianUser()
    {
        return $this->id == self::TECHNICIAN_USER;
    }

    public function isEditor()
    {
        return $this->id == self::EDITOR_USER;
    }

    public function hasEditor()
    {
        return $this->id >= self::EDITOR_USER;
    }

    public function hasAdmin()
    {
        return $this->id == self::ROOT_USER;
    }

    public function hasModerator()
    {
        return $this->id >= self::MODERATOR_USER;
    }

    public static function getRoles()
    {
        return [
            self::MODERATOR_USER => '版主',
            self::EDITOR_USER    => '编辑员',
            self::ROOT_USER      => '管理员',
        ];
    }
}

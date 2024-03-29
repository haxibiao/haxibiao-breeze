<?php

namespace Haxibiao\Breeze\Http\Api;

use App\Http\Controllers\Controller;
use App\SignIn;
use App\User;
use App\Video;
use Haxibiao\Breeze\Dimension;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class DimensionDataController extends Controller
{
    public function get(Request $request){
        $group = $request->get('group');
        $days  = $request->get('days',3);
        $days  = now()->subDays($days)->toDateString();
        $qb = Dimension::query()->whereDate('date','>',$days);
        if($group){
            $qb = $qb->whereGroup($group);
        }
        return $qb->paginate();
    }

    public function index(Request $request)
    {
        $data       = [];
        $dimensions = !empty($request->get('dimension')) ? explode(',', $request->get('dimension')) : [];
        if (is_array($dimensions) && count($dimensions)) {
            $data = Arr::only($this->dimenionData(), $dimensions);
            foreach ($data as &$item) {
                $item = $item();
            }
        }

        return $data;
    }

    public function dimenionData()
    {
        return [
            'NEW_USERS_YESTERDAY' => function () {
                return User::yesterday()->count();
            },
            'TOTAL_USERS'         => function () {
                return User::count();
            },
            'NEW_USERS_TODAY'     => function () {
                return User::today()->count();
            },
            'ACTIVE_USERS_TODAY'  => function () {
                $bySign = in_array(config('app.name'), ['datizhuanqian']);
                return $bySign ? SignIn::today()->count() : User::where('updated_at', '>=', today())->count();
            },
            'TOTAL_VIDEOS'        => function () {
                return Video::count();
            },
        ];
    }

}

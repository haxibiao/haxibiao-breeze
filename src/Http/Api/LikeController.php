<?php

namespace Haxibiao\Breeze\Http\Api;

use App\Http\Controllers\Controller;
use App\Like;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    /**
     * 用户点赞/取消点赞
     */
    public function toggle(Request $request, $id, $type)
    {
        $like = new Like();
        $user = $request->user();
        $data = [
            'user_id'      => $user->id,
            'likable_id'   => $id,
            'likable_type' => str_plural($type),
        ];
        $like->toggleLike($data);
        return $like->likeUsers($data);
    }

    public function getForGuest(Request $request, $id, $type)
    {
        return $this->get($request, $id, $type);
    }

    public function get(Request $request, $id, $type)
    {
        $like = new Like();
        $data = [
            'likable_id'   => $id,
            'likable_type' => str_plural($type),
        ];
        return $like->likeUsers($data);
    }
}

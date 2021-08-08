<?php

namespace Haxibiao\Breeze\Http\Controllers;

use App\Chat;
use App\User;

class ChatController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function chat($uid)
    {
        $uid  = intval($uid);
        $with = User::findOrFail($uid);
        $user = request()->user();

        $uids = [$with->id, $user->id];

        $chat = Chat::store($uids);

        return redirect_to('/notification/#chat/' . $chat->id);
    }
}

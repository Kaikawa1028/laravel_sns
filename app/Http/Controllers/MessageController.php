<?php

namespace App\Http\Controllers;

use App\Events\PostMessage;
use Illuminate\Http\Request;
use App\User;
use App\Room;
use App\Message;
use Illuminate\Support\Facades\Auth;
use phpDocumentor\Reflection\Types\Boolean;

class MessageController extends Controller
{
    public function index(string $name)
    {
        $user = User::where('name', $name)->first();

        if(!$room = $this->checkRoomExist($user->id, Auth::user()->id)) {
            $room = new Room();
            $room->user_1_id = Auth::user()->id;
            $room->user_2_id = $user->id;
            $room->save();
        }

        return view('users.message')
            ->with('user', $user)
            ->with('room', $room);
    }

    public function store(Request $request)
    {
        $message = new Message();
        $message->fill($request->all())->save();

        event((new PostMessage($message))->dontBroadcastToCurrentUser());

        return ['message' => $message];
    }

    private function checkRoomExist(int $user_1_id, int $user_2_id)
    {
        $room = Room::with('messages')->where('user_1_id', $user_1_id)->where('user_2_id', $user_2_id)->first();

        if(!empty($room)) {
            return $room;
        }

        $room = Room::with('messages')->where('user_1_id', $user_2_id)->where('user_2_id', $user_1_id)->first();

        if(!empty($room)) {
            return $room;
        }

        return false;
    }
}

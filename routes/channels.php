<?php

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('post-comment-channel.{room_id}', function ($user, $room_id) {
    $room = App\Room::where('id', $room_id)->first();

    if((int )$user->id === (int )$room->user_1_id) {
        return true;
    }elseif ((int )$user->id === (int )$room->user_2_id) {
        return true;
    }
    return false;
});

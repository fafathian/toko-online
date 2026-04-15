<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\ChatRoom;
use Illuminate\Support\Facades\Auth;

Broadcast::channel('chat.{roomId}', function ($user, $roomId) {
    $room = ChatRoom::find($roomId);

    if (!$room) return false;

    // Izinkan buyer atau seller (user yang punya toko tersebut)
    $isBuyer  = $room->buyer_id === $user->id;
    $isSeller = $room->store->user_id === $user->id;

    return $isBuyer || $isSeller;
});

Broadcast::channel('user.{id}', function ($user, $id) {
    // Akan mengembalikan nilai TRUE jika ID user yang login sama dengan ID channel yang diminta
    return (int) $user->id === (int) $id;
});

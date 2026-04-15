<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ChatController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // Ambil room sebagai buyer
        $buyerRooms = ChatRoom::with(['store', 'latestMessage', 'order'])
            ->where('buyer_id', $userId)
            ->get()
            ->map(fn($room) => array_merge($room->toArray(), [
                'unread_count' => $room->unreadForBuyer(),
                'role'         => 'buyer',
            ]));

        // Ambil room sebagai seller
        $myStoreIds  = Store::where('user_id', $userId)->pluck('id');
        $sellerRooms = ChatRoom::with(['buyer', 'latestMessage', 'order'])
            ->whereIn('store_id', $myStoreIds)
            ->get()
            ->map(fn($room) => array_merge($room->toArray(), [
                'unread_count' => $room->unreadForSeller(),
                'role'         => 'seller',
            ]));

        $rooms = $buyerRooms->concat($sellerRooms)->sortByDesc('updated_at')->values();

        return Inertia::render('Chat/Index', ['rooms' => $rooms]);
    }

    // Buka atau buat room chat
    public function show($roomId)
    {
        $userId = Auth::id();
        $room   = ChatRoom::with(['store', 'buyer', 'order'])->findOrFail($roomId);

        // Cek akses
        $isBuyer  = $room->buyer_id === $userId;
        $isSeller = $room->store->user_id === $userId;

        abort_unless($isBuyer || $isSeller, 403);

        // Ambil pesan dengan pagination
        $messages = ChatMessage::with('user')
            ->where('chat_room_id', $roomId)
            ->oldest()
            ->get();

        // Mark as read
        if ($isBuyer) {
            ChatMessage::where('chat_room_id', $roomId)
                ->where('user_id', '!=', $userId)
                ->update(['is_read' => true]);
            $room->update(['buyer_last_read' => now()]);
        } else {
            ChatMessage::where('chat_room_id', $roomId)
                ->where('user_id', $room->buyer_id)
                ->update(['is_read' => true]);
            $room->update(['seller_last_read' => now()]);
        }

        return Inertia::render('Chat/Show', [
            'room'     => $room,
            'messages' => $messages,
            'role'     => $isBuyer ? 'buyer' : 'seller',
        ]);
    }

    // Buat room baru (dari halaman order)
    public function createRoom(Request $request)
    {
        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'order_id' => 'nullable|exists:orders,id',
        ]);

        $room = ChatRoom::firstOrCreate([
            'buyer_id' => Auth::id(),
            'store_id' => $request->store_id,
            'order_id' => $request->order_id,
        ]);

        return redirect()->route('chat.show', $room->id);
    }

    // Kirim pesan
    public function sendMessage(Request $request, $roomId)
    {
        $request->validate(['message' => 'required|string|max:1000']);

        // Tambahkan relasi 'store' agar kita bisa tahu siapa pemilik tokonya
        $room   = ChatRoom::with('store')->findOrFail($roomId);
        $userId = Auth::id();

        $isBuyer  = $room->buyer_id === $userId;
        $isSeller = $room->store->user_id === $userId;
        abort_unless($isBuyer || $isSeller, 403);

        $message = ChatMessage::create([
            'chat_room_id' => $roomId,
            'user_id'      => $userId,
            'message'      => $request->message,
            'type'         => 'text',
        ]);

        $message->load('user');

        // KUNCI PERBAIKAN: Tentukan siapa penerimanya!
        // Jika yang kirim pembeli, maka penerimanya penjual (store->user_id). Jika tidak, sebaliknya.
        $receiverId = $isBuyer ? $room->store->user_id : $room->buyer_id;

        // Kirim $receiverId ini ke dalam Event
        broadcast(new MessageSent($message, $receiverId))->toOthers();

        // Update room timestamp
        $room->touch();

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }
}

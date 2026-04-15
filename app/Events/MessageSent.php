<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\ChatMessage;


class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $receiverId;
    /**
     * Create a new event instance.
     */
    public function __construct(ChatMessage $message, $receiverId)
    {
        $this->message = $message;
        $this->receiverId = $receiverId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            // 1. Kirim ke ruang chat (Untuk update pesan langsung)
            new PrivateChannel('chat.' . $this->message->chat_room_id),

            // 2. Kirim ke notifikasi global user (Untuk nambah angka merah di Navbar)
            new PrivateChannel('user.' . $this->receiverId),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id'           => $this->message->id,
            'chat_room_id' => $this->message->chat_room_id,
            'user_id'      => $this->message->user_id,
            'message'      => $this->message->message,
            'type'         => $this->message->type,
            'user'         => [
                'id'   => $this->message->user->id,
                'name' => $this->message->user->name,
            ],
            'created_at'   => $this->message->created_at->toIso8601String(),
        ];
    }

    // Broadcast secara realtime tanpa queue delay
    public function broadcastAs(): string
    {
        return 'MessageSent';
    }
}

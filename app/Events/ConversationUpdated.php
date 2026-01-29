<?php

namespace App\Events;

use App\Models\Conversation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConversationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Conversation $conversation;

    /**
     * Create a new event instance.
     */
    public function __construct(Conversation $conversation)
    {
        $this->conversation = $conversation->load(['guest:id,name,avatar', 'host:id,name,avatar']);
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("user.{$this->conversation->guest_id}"),
            new PrivateChannel("user.{$this->conversation->host_id}"),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'conversation.updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'conversation' => [
                'id' => $this->conversation->id,
                'subject' => $this->conversation->subject,
                'type' => $this->conversation->type,
                'status' => $this->conversation->status,
                'last_message_at' => $this->conversation->last_message_at?->toISOString(),
                'last_message_content' => $this->conversation->last_message_content,
                'guest' => [
                    'id' => $this->conversation->guest->id,
                    'name' => $this->conversation->guest->name,
                    'avatar' => $this->conversation->guest->avatar_url,
                ],
                'host' => [
                    'id' => $this->conversation->host->id,
                    'name' => $this->conversation->host->name,
                    'avatar' => $this->conversation->host->avatar_url,
                ]
            ]
        ];
    }
}
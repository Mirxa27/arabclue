<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Models\Property;
use App\Events\MessageSent;
use App\Events\ConversationUpdated;
use App\Notifications\NewMessageNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;

class MessagingService
{
    /**
     * Create or get conversation between users
     */
    public function getOrCreateConversation(
        int $guestId,
        int $hostId,
        ?int $propertyId = null,
        ?int $bookingId = null
    ): Conversation {
        // Try to find existing conversation
        $conversation = Conversation::where('guest_id', $guestId)
            ->where('host_id', $hostId)
            ->when($propertyId, fn($q) => $q->where('property_id', $propertyId))
            ->when($bookingId, fn($q) => $q->where('booking_id', $bookingId))
            ->where('status', Conversation::STATUS_ACTIVE)
            ->first();

        if (!$conversation) {
            $subject = 'Property Inquiry';
            $type = Conversation::TYPE_INQUIRY;

            if ($propertyId) {
                $property = Property::find($propertyId);
                $subject = $property ? "Inquiry about {$property->title}" : $subject;
            }

            if ($bookingId) {
                $subject = 'Booking Discussion';
                $type = Conversation::TYPE_BOOKING;
            }

            $conversation = Conversation::create([
                'guest_id' => $guestId,
                'host_id' => $hostId,
                'property_id' => $propertyId,
                'booking_id' => $bookingId,
                'subject' => $subject,
                'type' => $type,
                'status' => Conversation::STATUS_ACTIVE
            ]);
        }

        return $conversation;
    }

    /**
     * Send message in conversation
     */
    public function sendMessage(
        int $conversationId,
        int $senderId,
        string $content,
        array $options = []
    ): Message {
        return DB::transaction(function () use ($conversationId, $senderId, $content, $options) {
            $conversation = Conversation::findOrFail($conversationId);
            
            // Verify sender is part of conversation
            if (!$conversation->hasParticipant($senderId)) {
                throw new \Exception('User is not part of this conversation');
            }

            $receiverId = $conversation->guest_id === $senderId 
                ? $conversation->host_id 
                : $conversation->guest_id;

            $message = Message::create([
                'conversation_id' => $conversationId,
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'content' => $content,
                'type' => $options['type'] ?? Message::TYPE_TEXT,
                'attachments' => $options['attachments'] ?? [],
                'reply_to_id' => $options['reply_to_id'] ?? null,
                'is_system_message' => $options['is_system_message'] ?? false,
                'metadata' => $options['metadata'] ?? []
            ]);

            // Update conversation last message info
            $conversation->updateLastMessage($message);

            // Fire events for real-time updates
            Event::dispatch(new MessageSent($message));
            Event::dispatch(new ConversationUpdated($conversation));

            return $message;
        });
    }

    /**
     * Get conversation messages with pagination
     */
    public function getConversationMessages(
        int $conversationId,
        int $userId,
        int $page = 1,
        int $perPage = 50
    ): array {
        $conversation = Conversation::findOrFail($conversationId);
        
        // Verify user has access
        if (!$conversation->hasParticipant($userId)) {
            throw new \Exception('User cannot access this conversation');
        }

        $messages = $conversation->messages()
            ->with(['sender:id,name,avatar', 'replyTo'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        // Mark messages as read for the user
        $this->markMessagesAsRead($conversationId, $userId);

        return [
            'messages' => $messages->items(),
            'pagination' => [
                'current_page' => $messages->currentPage(),
                'total_pages' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
                'has_more' => $messages->hasMorePages()
            ]
        ];
    }

    /**
     * Get user's conversations
     */
    public function getUserConversations(
        int $userId,
        array $filters = []
    ): \Illuminate\Support\Collection {
        $query = Conversation::forUser($userId)
            ->notArchivedForUser($userId)
            ->with([
                'guest:id,name,avatar',
                'host:id,name,avatar',
                'property:id,title,slug',
                'booking:id,booking_reference,status',
                'latestMessage'
            ])
            ->orderBy('last_message_at', 'desc');

        // Apply filters
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['unread_only']) && $filters['unread_only']) {
            $query->withUnreadForUser($userId);
        }

        $conversations = $query->get();

        // Add unread count and other participant info
        return $conversations->map(function ($conversation) use ($userId) {
            $conversation->unread_count = $conversation->getUnreadCountForUser($userId);
            $conversation->other_participant = $conversation->getOtherParticipant($userId);
            return $conversation;
        });
    }

    /**
     * Mark messages as read
     */
    public function markMessagesAsRead(int $conversationId, int $userId): void
    {
        $conversation = Conversation::findOrFail($conversationId);
        $conversation->markAsReadForUser($userId);
    }

    /**
     * Archive conversation for user
     */
    public function archiveConversation(int $conversationId, int $userId): void
    {
        $conversation = Conversation::findOrFail($conversationId);
        
        if (!$conversation->hasParticipant($userId)) {
            throw new \Exception('User cannot access this conversation');
        }

        $conversation->archiveForUser($userId);
    }

    /**
     * Delete message (soft delete)
     */
    public function deleteMessage(int $messageId, int $userId): void
    {
        $message = Message::findOrFail($messageId);
        
        if ($message->sender_id !== $userId) {
            throw new \Exception('User can only delete their own messages');
        }

        $message->delete();
    }

    /**
     * Edit message content
     */
    public function editMessage(int $messageId, int $userId, string $newContent): Message
    {
        $message = Message::findOrFail($messageId);
        
        if ($message->sender_id !== $userId) {
            throw new \Exception('User can only edit their own messages');
        }

        if ($message->created_at->lt(now()->subMinutes(15))) {
            throw new \Exception('Messages can only be edited within 15 minutes');
        }

        $message->editContent($newContent);
        
        return $message;
    }

    /**
     * Add attachment to message
     */
    public function addMessageAttachment(int $messageId, array $attachment): void
    {
        $message = Message::findOrFail($messageId);
        $message->addAttachment($attachment);
    }

    /**
     * Search messages
     */
    public function searchMessages(
        int $userId,
        string $query,
        array $filters = []
    ): \Illuminate\Support\Collection {
        $builder = Message::searchByContent($query, $userId);

        // Apply filters
        if (isset($filters['conversation_id'])) {
            $builder->where('conversation_id', $filters['conversation_id']);
        }

        if (isset($filters['date_from'])) {
            $builder->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $builder->where('created_at', '<=', $filters['date_to']);
        }

        return $builder->get();
    }

    /**
     * Get conversation statistics
     */
    public function getConversationStats(int $conversationId): array
    {
        return Message::getStatsForConversation($conversationId);
    }

    /**
     * Get user messaging statistics
     */
    public function getUserMessagingStats(int $userId): array
    {
        $conversationStats = Conversation::getStatsForUser($userId);
        
        $messageStats = [
            'total_sent' => Message::where('sender_id', $userId)->count(),
            'total_received' => Message::where('receiver_id', $userId)->count(),
            'unread_messages' => Message::where('receiver_id', $userId)
                ->whereNull('read_at')
                ->count()
        ];

        return array_merge($conversationStats, $messageStats);
    }

    public function createMessage(Conversation $conversation, User $user, array $validated): Message
    {
        $attachments = [];
        if (isset($validated['attachments'])) {
            foreach ($validated['attachments'] as $file) {
                $path = $file->store('message-attachments', 'public');
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType()
                ];
            }
        }

        $receiverId = $conversation->guest_id === $user->id
            ? $conversation->host_id
            : $conversation->guest_id;

        return Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'receiver_id' => $receiverId,
            'message' => $validated['message'],
            'attachments' => $attachments
        ]);
    }

    public function sendMessageNotification(Message $message): void
    {
        $recipient = $message->receiver;
        $recipient->notify(new NewMessageNotification($message));
    }

    /**
     * Send booking inquiry message
     */
    public function sendBookingInquiry(
        int $guestId,
        int $hostId,
        int $propertyId,
        array $bookingDetails
    ): Message {
        $conversation = $this->getOrCreateConversation($guestId, $hostId, $propertyId);
        
        return Message::createBookingInquiry(
            $conversation->id,
            $guestId,
            $hostId,
            $bookingDetails
        );
    }

    /**
     * Send booking response message
     */
    public function sendBookingResponse(
        int $conversationId,
        int $hostId,
        string $response,
        array $responseData = []
    ): Message {
        $conversation = Conversation::findOrFail($conversationId);
        
        return Message::createBookingResponse(
            $conversationId,
            $hostId,
            $conversation->guest_id,
            $response,
            $responseData
        );
    }

    /**
     * Send system message
     */
    public function sendSystemMessage(
        int $conversationId,
        string $content,
        array $metadata = []
    ): Message {
        return Message::createSystemMessage($conversationId, $content, $metadata);
    }

    /**
     * Block/unblock user in messaging
     */
    public function toggleUserBlock(int $userId, int $targetUserId): array
    {
        $cacheKey = "user_blocked_{$userId}_{$targetUserId}";
        $isBlocked = Cache::get($cacheKey, false);
        
        if ($isBlocked) {
            Cache::forget($cacheKey);
            return ['blocked' => false, 'message' => 'User unblocked'];
        } else {
            Cache::put($cacheKey, true, now()->addDays(30));
            return ['blocked' => true, 'message' => 'User blocked'];
        }
    }

    /**
     * Check if user is blocked
     */
    public function isUserBlocked(int $userId, int $targetUserId): bool
    {
        return Cache::get("user_blocked_{$userId}_{$targetUserId}", false) ||
               Cache::get("user_blocked_{$targetUserId}_{$userId}", false);
    }

    /**
     * Get online users (simplified implementation)
     */
    public function getOnlineUsers(array $userIds = []): array
    {
        $onlineUsers = [];
        
        foreach ($userIds as $userId) {
            if (Cache::has("user_online_{$userId}")) {
                $onlineUsers[] = $userId;
            }
        }
        
        return $onlineUsers;
    }

    /**
     * Set user online status
     */
    public function setUserOnline(int $userId): void
    {
        Cache::put("user_online_{$userId}", true, now()->addMinutes(5));
    }

    /**
     * Set user offline
     */
    public function setUserOffline(int $userId): void
    {
        Cache::forget("user_online_{$userId}");
    }

    /**
     * Get conversation participants with online status
     */
    public function getConversationParticipants(int $conversationId): array
    {
        $conversation = Conversation::with(['guest', 'host'])->findOrFail($conversationId);
        
        $participants = [
            [
                'id' => $conversation->guest->id,
                'name' => $conversation->guest->name,
                'avatar' => $conversation->guest->avatar_url,
                'role' => 'guest',
                'online' => Cache::has("user_online_{$conversation->guest->id}")
            ],
            [
                'id' => $conversation->host->id,
                'name' => $conversation->host->name,
                'avatar' => $conversation->host->avatar_url,
                'role' => 'host',
                'online' => Cache::has("user_online_{$conversation->host->id}")
            ]
        ];

        return $participants;
    }
}

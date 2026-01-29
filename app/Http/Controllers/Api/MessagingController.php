<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\MessagingService;
use Carbon\Carbon;
use App\Http\Requests\StoreMessageRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Messaging API Controller - User Communication Management
 * 
 * Handles conversations and messages between guests and hosts
 * with real-time messaging capabilities and booking integration
 * 
 * @package App\Http\Controllers\Api
 * @version 1.0.0
 */
class MessagingController extends Controller
{
    protected MessagingService $messagingService;

    public function __construct(MessagingService $messagingService)
    {
        $this->messagingService = $messagingService;
        $this->middleware('auth:sanctum');
    }

    /**
     * Get user's conversations
     */
    public function getConversations(Request $request): JsonResponse
    {
        $user = $request->user();
        $page = $request->get('page', 1);
        $perPage = min($request->get('per_page', 20), 50);
        $status = $request->get('status', 'active');

        try {
            $conversations = Conversation::where(function ($query) use ($user) {
                    $query->where('guest_id', $user->id)
                          ->orWhere('host_id', $user->id);
                })
                ->when($status === 'active', fn($q) => $q->where('status', 'active'))
                ->when($status === 'archived', fn($q) => $q->where(function ($q) use ($user) {
                    if ($user->role === 'guest') {
                        $q->where('guest_archived', true);
                    } else {
                        $q->where('host_archived', true);
                    }
                }))
                ->with(['guest', 'host', 'property', 'booking', 'latestMessage'])
                ->orderByDesc('last_message_at')
                ->paginate($perPage, ['*'], 'page', $page);

            $formattedConversations = $conversations->getCollection()->map(function ($conversation) use ($user) {
                $otherUser = $conversation->getOtherParticipant($user->id);
                $unreadCount = $conversation->getUnreadCountForUser($user->id);

                return [
                    'id' => $conversation->id,
                    'subject' => $conversation->subject,
                    'type' => $conversation->type,
                    'status' => $conversation->status,
                    'last_message_at' => $conversation->last_message_at,
                    'unread_count' => $unreadCount,
                    'other_user' => [
                        'id' => $otherUser->id,
                        'name' => $otherUser->name,
                        'avatar' => $otherUser->avatar,
                        'role' => $otherUser->role,
                        'verified' => $otherUser->identity_verified
                    ],
                    'property' => $conversation->property ? [
                        'id' => $conversation->property->id,
                        'title' => $conversation->property->title,
                        'slug' => $conversation->property->slug,
                        'image' => $conversation->property->primary_image_url
                    ] : null,
                    'booking' => $conversation->booking ? [
                        'id' => $conversation->booking->id,
                        'reference' => $conversation->booking->booking_reference,
                        'status' => $conversation->booking->status,
                        'check_in' => $conversation->booking->check_in,
                        'check_out' => $conversation->booking->check_out
                    ] : null,
                    'last_message' => $conversation->latestMessage->first() ? [
                        'content' => $conversation->latestMessage->first()->excerpt,
                        'sender_id' => $conversation->latestMessage->first()->sender_id,
                        'created_at' => $conversation->latestMessage->first()->created_at
                    ] : null
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'conversations' => $formattedConversations,
                    'pagination' => [
                        'current_page' => $conversations->currentPage(),
                        'last_page' => $conversations->lastPage(),
                        'per_page' => $conversations->perPage(),
                        'total' => $conversations->total()
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Failed to retrieve conversations'
            ], 500);
        }
    }

    /**
     * Send a message
     */
    public function sendMessage(StoreMessageRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();
        $conversation = Conversation::findOrFail($validated['conversation_id']);

        // Check if user is participant
        if (!$conversation->hasParticipant($user->id)) {
            return response()->json([
                'error' => true,
                'message' => 'Unauthorized access to conversation'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $message = $this->messagingService->createMessage($conversation, $user, $validated);

            // Update conversation
            $conversation->updateLastMessage($message);

            DB::commit();

            $this->messagingService->sendMessageNotification($message);

            return response()->json([
                'success' => true,
                'data' => [
                    'message' => $message->load('sender:id,name,avatar')
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'error' => true,
                'message' => 'Failed to send message'
            ], 500);
        }
    }

    /**
     * Get messages for a conversation
     */
    public function getMessages(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        // Check if user is participant
        if (!$conversation->hasParticipant($user->id)) {
            return response()->json([
                'error' => true,
                'message' => 'Unauthorized access to conversation'
            ], 403);
        }

        $page = $request->get('page', 1);
        $perPage = min($request->get('per_page', 50), 100);

        try {
            $messages = $conversation->messages()
                ->with('sender')
                ->orderByDesc('created_at')
                ->paginate($perPage, ['*'], 'page', $page);

            $formattedMessages = $messages->getCollection()->map(function ($message) {
                return [
                    'id' => $message->id,
                    'content' => $message->message,
                    'sender' => [
                        'id' => $message->sender->id,
                        'name' => $message->sender->name,
                        'avatar' => $message->sender->avatar
                    ],
                    'attachments' => $message->attachments,
                    'is_system_message' => $message->is_system_message,
                    'read_at' => $message->read_at,
                    'created_at' => $message->created_at
                ];
            })->reverse()->values();

            return response()->json([
                'success' => true,
                'data' => [
                    'messages' => $formattedMessages,
                    'pagination' => [
                        'current_page' => $messages->currentPage(),
                        'last_page' => $messages->lastPage(),
                        'per_page' => $messages->perPage(),
                        'total' => $messages->total()
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Failed to retrieve messages'
            ], 500);
        }
    }

    /**
     * Mark messages as read
     */
    public function markAsRead(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        // Check if user is participant
        if (!$conversation->hasParticipant($user->id)) {
            return response()->json([
                'error' => true,
                'message' => 'Unauthorized access to conversation'
            ], 403);
        }

        try {
            $updatedCount = Message::where('conversation_id', $conversation->id)
                ->where('receiver_id', $user->id)
                ->whereNull('read_at')
                ->update(['read_at' => Carbon::now()]);

            // Mark conversation as read for user
            $conversation->markAsReadForUser($user->id);

            return response()->json([
                'success' => true,
                'data' => [
                    'marked_count' => $updatedCount
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Failed to mark messages as read'
            ], 500);
        }
    }

    /**
     * Archive conversation
     */
    public function archiveConversation(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        // Check if user is participant
        if (!$conversation->hasParticipant($user->id)) {
            return response()->json([
                'error' => true,
                'message' => 'Unauthorized access to conversation'
            ], 403);
        }

        try {
            $conversation->archiveForUser($user->id);

            return response()->json([
                'success' => true,
                'message' => 'Conversation archived successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Failed to archive conversation'
            ], 500);
        }
    }

    /**
     * Unarchive conversation
     */
    public function unarchiveConversation(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        // Check if user is participant
        if (!$conversation->hasParticipant($user->id)) {
            return response()->json([
                'error' => true,
                'message' => 'Unauthorized access to conversation'
            ], 403);
        }

        try {
            $conversation->unarchiveForUser($user->id);

            return response()->json([
                'success' => true,
                'message' => 'Conversation unarchived successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Failed to unarchive conversation'
            ], 500);
        }
    }

    /**
     * Get conversation participants
     */
    public function getParticipants(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        // Check if user is participant
        if (!$conversation->hasParticipant($user->id)) {
            return response()->json([
                'error' => true,
                'message' => 'Unauthorized access to conversation'
            ], 403);
        }

        try {
            $participants = [
                'guest' => [
                    'id' => $conversation->guest->id,
                    'name' => $conversation->guest->name,
                    'avatar' => $conversation->guest->avatar
                ],
                'host' => [
                    'id' => $conversation->host->id,
                    'name' => $conversation->host->name,
                    'avatar' => $conversation->host->avatar
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $participants
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Failed to retrieve participants'
            ], 500);
        }
    }

    /**
     * Set user online status
     */
    public function setOnlineStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:online,offline'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $userId = $request->user()->id;
            
            if ($request->status === 'online') {
                $this->messagingService->setUserOnline($userId);
            } else {
                $this->messagingService->setUserOffline($userId);
            }

            return response()->json([
                'success' => true,
                'message' => 'Status updated'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Failed to update status'
            ], 500);
        }
    }

    /**
     * Get messaging statistics
     */
    public function getStats(Request $request): JsonResponse
    {
        try {
            $stats = $this->messagingService->getUserMessagingStats($request->user()->id);

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Failed to get stats'
            ], 500);
        }
    }

    /**
     * Block/unblock user
     */
    public function toggleUserBlock(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            if ($request->user_id === $request->user()->id) {
                return response()->json([
                    'error' => true,
                    'message' => 'Cannot block yourself'
                ], 400);
            }

            $result = $this->messagingService->toggleUserBlock(
                $request->user()->id,
                $request->user_id
            );

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Failed to toggle block status'
            ], 500);
        }
    }

    /**
     * Create support conversation
     */
    public function createSupportConversation(Request $request): JsonResponse
    {
        $user = $request->user();

        try {
            // Check if user already has an active support conversation
            $existingConversation = Conversation::where('guest_id', $user->id)
                ->where('type', 'support')
                ->where('status', 'active')
                ->first();

            if ($existingConversation) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'conversation' => [
                            'id' => $existingConversation->id,
                            'subject' => $existingConversation->subject,
                            'type' => $existingConversation->type,
                            'status' => $existingConversation->status,
                            'created_at' => $existingConversation->created_at
                        ]
                    ],
                    'message' => 'Support conversation already exists'
                ]);
            }

            // Create new support conversation
            $conversation = Conversation::create([
                'guest_id' => $user->id,
                'host_id' => 1, // Support team user ID
                'type' => 'support',
                'subject' => 'Support Request',
                'status' => 'active',
                'last_message_at' => now()
            ]);

            // Send welcome message from support
            $welcomeMessage = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => 1, // Support team user ID
                'receiver_id' => $user->id,
                'message' => 'Hello! How can we help you today? Our support team is here to assist you with any questions or issues you may have.',
                'is_system_message' => true
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'conversation' => [
                        'id' => $conversation->id,
                        'subject' => $conversation->subject,
                        'type' => $conversation->type,
                        'status' => $conversation->status,
                        'created_at' => $conversation->created_at
                    ]
                ],
                'message' => 'Support conversation created successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Failed to create support conversation'
            ], 500);
        }
    }
}

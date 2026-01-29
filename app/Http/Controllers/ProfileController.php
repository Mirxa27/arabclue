<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wishlist;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Display user profile
     */
    public function index()
    {
        $user = Auth::user();
        $user->load(['wishlists.property:id,title,slug,city,price_per_night,images']);

        $stats = [
            'total_bookings' => $user->bookings()->count(),
            'completed_trips' => $user->bookings()->where('status', 'completed')->count(),
            'wishlist_count' => $user->wishlists()->count(),
            'reviews_given' => $user->reviews()->count(),
            'member_since' => $user->created_at->format('Y')
        ];

        return view('profile.index', compact('user', 'stats'));
    }

    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other,prefer_not_to_say',
            'language' => 'nullable|string|max:10',
            'currency' => 'nullable|string|max:10',
            'timezone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_relationship' => 'nullable|string|max:100',
            'marketing_emails' => 'boolean',
            'booking_notifications' => 'boolean',
            'message_notifications' => 'boolean',
            'review_notifications' => 'boolean',
            'promotion_notifications' => 'boolean'
        ]);

        try {
            $user->update($validated);

            return redirect()->route('profile.index')
                           ->with('success', 'Profile updated successfully!');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update profile: ' . $e->getMessage()]);
        }
    }

    /**
     * Update user avatar
     */
    public function updateAvatar(Request $request)
    {
        $validated = $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            $user = Auth::user();

            // Delete old avatar if exists
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Store new avatar
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->update(['avatar' => $path]);

            return redirect()->route('profile.index')
                           ->with('success', 'Avatar updated successfully!');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update avatar: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete user account
     */
    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'password' => 'required|string',
            'reason' => 'nullable|string|max:500'
        ]);

        $user = Auth::user();

        // Verify password
        if (!Hash::check($validated['password'], $user->password)) {
            return back()->withErrors(['password' => 'Invalid password']);
        }

        try {
            // Check for active bookings
            $activeBookings = $user->bookings()
                ->whereIn('status', ['pending', 'confirmed'])
                ->where('check_out', '>', now())
                ->count();

            if ($activeBookings > 0) {
                return back()->withErrors(['error' => 'Cannot delete account with active bookings. Please complete or cancel your bookings first.']);
            }

            // Soft delete user
            $user->update([
                'deleted_reason' => $validated['reason'] ?? 'User requested deletion',
                'deleted_at' => now()
            ]);

            // Logout user
            Auth::logout();

            return redirect()->route('home')
                           ->with('success', 'Account deleted successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete account: ' . $e->getMessage()]);
        }
    }

    /**
     * Display user's wishlist
     */
    public function wishlist()
    {
        $user = Auth::user();
        $wishlists = $user->wishlists()
            ->with(['property:id,title,slug,city,country,price_per_night,images,average_rating'])
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('profile.wishlist', compact('wishlists'));
    }

    /**
     * Display user's messages
     */
    public function messages()
    {
        $user = Auth::user();
        $conversations = Conversation::where('user_id', $user->id)
            ->orWhere('host_id', $user->id)
            ->with(['user:id,name,avatar', 'host:id,name,avatar', 'property:id,title,slug'])
            ->withCount(['messages as unread_count' => function($query) use ($user) {
                $query->where('sender_id', '!=', $user->id)
                      ->whereNull('read_at');
            }])
            ->orderBy('updated_at', 'desc')
            ->paginate(15);

        return view('profile.messages', compact('conversations'));
    }

    /**
     * Display specific conversation
     */
    public function conversation(Conversation $conversation)
    {
        $user = Auth::user();

        // Check authorization
        if ($conversation->user_id !== $user->id && $conversation->host_id !== $user->id) {
            abort(403, 'Unauthorized access to conversation');
        }

        $conversation->load([
            'user:id,name,avatar',
            'host:id,name,avatar',
            'property:id,title,slug,images'
        ]);

        $messages = $conversation->messages()
            ->with('sender:id,name,avatar')
            ->orderBy('created_at', 'asc')
            ->paginate(50);

        // Mark messages as read
        $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('profile.conversation', compact('conversation', 'messages'));
    }

    /**
     * Send message in conversation
     */
    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'message' => 'required|string|max:1000',
            'message_type' => 'nullable|in:text,booking_inquiry,booking_response'
        ]);

        $user = Auth::user();
        $conversation = Conversation::findOrFail($validated['conversation_id']);

        // Check authorization
        if ($conversation->user_id !== $user->id && $conversation->host_id !== $user->id) {
            abort(403, 'Unauthorized to send message in this conversation');
        }

        try {
            $message = $conversation->messages()->create([
                'sender_id' => $user->id,
                'message' => $validated['message'],
                'message_type' => $validated['message_type'] ?? 'text'
            ]);

            // Update conversation timestamp
            $conversation->touch();

            return redirect()->route('messages.show', $conversation)
                           ->with('success', 'Message sent successfully!');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to send message: ' . $e->getMessage()]);
        }
    }

    /**
     * Show identity verification form
     */
    public function verifyIdentity()
    {
        $user = Auth::user();

        if ($user->identity_verified) {
            return redirect()->route('profile.index')
                           ->with('info', 'Your identity is already verified.');
        }

        return view('profile.verify-identity');
    }

    /**
     * Process identity verification
     */
    public function processIdentityVerification(Request $request)
    {
        $validated = $request->validate([
            'document_type' => 'required|in:passport,national_id,driving_license',
            'document_number' => 'required|string|max:50',
            'document_front' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'document_back' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'selfie' => 'required|image|mimes:jpeg,png,jpg|max:5120'
        ]);

        try {
            $user = Auth::user();

            // Store documents
            $frontPath = $request->file('document_front')->store('identity/documents', 'private');
            $selfiePath = $request->file('selfie')->store('identity/selfies', 'private');
            
            $backPath = null;
            if ($request->hasFile('document_back')) {
                $backPath = $request->file('document_back')->store('identity/documents', 'private');
            }

            // Update user verification status
            $user->update([
                'identity_verified' => false, // Will be verified by admin
                'identity_verification_status' => 'pending',
                'identity_documents' => [
                    'type' => $validated['document_type'],
                    'number' => $validated['document_number'],
                    'front_path' => $frontPath,
                    'back_path' => $backPath,
                    'selfie_path' => $selfiePath,
                    'submitted_at' => now()
                ]
            ]);

            return redirect()->route('profile.index')
                           ->with('success', 'Identity verification documents submitted successfully. We will review them within 24-48 hours.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to submit identity verification: ' . $e->getMessage()]);
        }
    }

    /**
     * Show notification settings
     */
    public function notificationSettings()
    {
        $user = Auth::user();
        return view('profile.notification-settings', compact('user'));
    }

    /**
     * Update notification settings
     */
    public function updateNotificationSettings(Request $request)
    {
        $validated = $request->validate([
            'email_notifications' => 'boolean',
            'push_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'marketing_emails' => 'boolean',
            'booking_notifications' => 'boolean',
            'message_notifications' => 'boolean',
            'review_notifications' => 'boolean',
            'promotion_notifications' => 'boolean'
        ]);

        try {
            Auth::user()->update($validated);

            return redirect()->route('profile.notification-settings')
                           ->with('success', 'Notification settings updated successfully!');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update notification settings: ' . $e->getMessage()]);
        }
    }

    /**
     * Show privacy settings
     */
    public function privacySettings()
    {
        $user = Auth::user();
        return view('profile.privacy-settings', compact('user'));
    }

    /**
     * Update privacy settings
     */
    public function updatePrivacySettings(Request $request)
    {
        $validated = $request->validate([
            'profile_visibility' => 'required|in:public,private,friends',
            'show_email' => 'boolean',
            'show_phone' => 'boolean',
            'allow_messages' => 'boolean',
            'data_sharing' => 'boolean'
        ]);

        try {
            Auth::user()->update($validated);

            return redirect()->route('profile.privacy-settings')
                           ->with('success', 'Privacy settings updated successfully!');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update privacy settings: ' . $e->getMessage()]);
        }
    }
}

@extends('layouts.admin')

@section('title', 'Edit User - ' . $user->name)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow-lg p-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Edit User</h1>
            <p class="text-gray-600 mt-2">Update user information and settings</p>
        </div>

        <form action="{{ route('admin.users.update', $user) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @method('PUT')

            <!-- Basic Information -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Basic Information</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Full Name *</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email Address *</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700">User Role *</label>
                        <select id="role" name="role" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="guest" {{ old('role', $user->role) === 'guest' ? 'selected' : '' }}>Guest</option>
                            <option value="host" {{ old('role', $user->role) === 'host' ? 'selected' : '' }}>Host</option>
                            <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="super_admin" {{ old('role', $user->role) === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                        </select>
                        @error('role')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Account Status *</label>
                        <select id="status" name="status" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="suspended" {{ old('status', $user->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                            <option value="banned" {{ old('status', $user->status) === 'banned' ? 'selected' : '' }}>Banned</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="language" class="block text-sm font-medium text-gray-700">Preferred Language</label>
                        <select id="language" name="language"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="en" {{ old('language', $user->language ?? 'en') === 'en' ? 'selected' : '' }}>English</option>
                            <option value="ar" {{ old('language', $user->language) === 'ar' ? 'selected' : '' }}>العربية</option>
                        </select>
                        @error('language')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Password Change -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Change Password</h2>
                <p class="text-gray-600 mb-4">Leave blank if you don't want to change the password</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                        <input type="password" id="password" name="password"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-sm text-gray-500">Minimum 8 characters</p>
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>

            <!-- Profile Information -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Profile Information</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="date_of_birth" class="block text-sm font-medium text-gray-700">Date of Birth</label>
                        <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', $user->date_of_birth) }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('date_of_birth')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="gender" class="block text-sm font-medium text-gray-700">Gender</label>
                        <select id="gender" name="gender"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select gender</option>
                            <option value="male" {{ old('gender', $user->gender) === 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender', $user->gender) === 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ old('gender', $user->gender) === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('gender')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="nationality" class="block text-sm font-medium text-gray-700">Nationality</label>
                        <input type="text" id="nationality" name="nationality" value="{{ old('nationality', $user->nationality) }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('nationality')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700">City</label>
                        <input type="text" id="city" name="city" value="{{ old('city', $user->city) }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('city')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6">
                    <label for="bio" class="block text-sm font-medium text-gray-700">Bio</label>
                    <textarea id="bio" name="bio" rows="3"
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('bio', $user->bio) }}</textarea>
                    @error('bio')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Profile Picture -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Profile Picture</h2>
                
                <div class="flex items-center space-x-6">
                    <div class="relative">
                        <img id="avatarPreview" 
                             src="{{ $user->avatar ? Storage::url($user->avatar) : '/images/default-avatar.png' }}" 
                             alt="Profile Picture" 
                             class="w-24 h-24 rounded-full object-cover border-4 border-gray-200">
                    </div>
                    <div>
                        <label for="avatar" class="block text-sm font-medium text-gray-700">Upload New Profile Picture</label>
                        <input type="file" id="avatar" name="avatar" accept="image/*"
                               class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                               onchange="previewAvatar(this)">
                        <p class="mt-1 text-sm text-gray-500">JPG, PNG, max 2MB</p>
                        @error('avatar')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Verification Status -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Verification Status</h2>
                
                <div class="space-y-4">
                    <div class="flex items-center">
                        <input type="checkbox" id="email_verified" name="email_verified" value="1"
                               {{ old('email_verified', $user->email_verified_at ? true : false) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="email_verified" class="ml-2 text-sm text-gray-700">
                            Email verified
                            @if($user->email_verified_at)
                                <span class="text-green-600">({{ $user->email_verified_at->format('M d, Y') }})</span>
                            @endif
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="phone_verified" name="phone_verified" value="1"
                               {{ old('phone_verified', $user->phone_verified_at ? true : false) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="phone_verified" class="ml-2 text-sm text-gray-700">
                            Phone verified
                            @if($user->phone_verified_at)
                                <span class="text-green-600">({{ $user->phone_verified_at->format('M d, Y') }})</span>
                            @endif
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="identity_verified" name="identity_verified" value="1"
                               {{ old('identity_verified', $user->identity_verified) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="identity_verified" class="ml-2 text-sm text-gray-700">
                            Identity verified
                        </label>
                    </div>
                </div>
            </div>

            <!-- Notification Preferences -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Notification Preferences</h2>
                
                <div class="space-y-4">
                    <div class="flex items-center">
                        <input type="checkbox" id="email_notifications" name="email_notifications" value="1"
                               {{ old('email_notifications', $user->email_notifications ?? true) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="email_notifications" class="ml-2 text-sm text-gray-700">
                            Email notifications
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="sms_notifications" name="sms_notifications" value="1"
                               {{ old('sms_notifications', $user->sms_notifications ?? false) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="sms_notifications" class="ml-2 text-sm text-gray-700">
                            SMS notifications
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="marketing_emails" name="marketing_emails" value="1"
                               {{ old('marketing_emails', $user->marketing_emails ?? false) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="marketing_emails" class="ml-2 text-sm text-gray-700">
                            Marketing emails
                        </label>
                    </div>
                </div>
            </div>

            <!-- Account Statistics -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Account Statistics</h2>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600">{{ $user->created_at->format('M d, Y') }}</div>
                        <div class="text-sm text-gray-600">Member Since</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">{{ $user->last_login_at ? $user->last_login_at->format('M d, Y') : 'Never' }}</div>
                        <div class="text-sm text-gray-600">Last Login</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-purple-600">{{ $user->bookings_count ?? 0 }}</div>
                        <div class="text-sm text-gray-600">Total Bookings</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-orange-600">{{ $user->properties_count ?? 0 }}</div>
                        <div class="text-sm text-gray-600">Properties Listed</div>
                    </div>
                </div>
            </div>

            <!-- Admin Notes -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Admin Notes</h2>
                
                <div>
                    <label for="admin_notes" class="block text-sm font-medium text-gray-700">Internal Notes</label>
                    <textarea id="admin_notes" name="admin_notes" rows="3"
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Internal notes about this user (not visible to user)...">{{ old('admin_notes', $user->admin_notes) }}</textarea>
                    @error('admin_notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex justify-between items-center pt-6 border-t border-gray-200">
                <a href="{{ route('admin.users.index') }}" 
                   class="px-6 py-3 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                
                <div class="flex space-x-4">
                    @if($user->status === 'suspended' || $user->status === 'banned')
                        <button type="button" onclick="reactivateUser()" 
                                class="px-6 py-3 border border-green-600 text-green-600 rounded-md hover:bg-green-50 transition-colors">
                            Reactivate Account
                        </button>
                    @endif
                    
                    <button type="submit" 
                            class="px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                        Update User
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatarPreview').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function reactivateUser() {
    if (confirm('Are you sure you want to reactivate this user account?')) {
        document.getElementById('status').value = 'active';
        document.querySelector('form').submit();
    }
}
</script>
@endsection

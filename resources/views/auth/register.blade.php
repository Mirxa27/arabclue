<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | HabibiStay</title>
    <meta name="description" content="Create your HabibiStay account and start your journey">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --brand-blue: #2957c3;
            --brand-blue-light: #4a6cf7;
            --brand-blue-dark: #1e3a8a;
        }
        
        .brand-gradient {
            background: linear-gradient(135deg, var(--brand-blue) 0%, var(--brand-blue-light) 100%);
        }
        
        .auth-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        }
        
        .auth-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .social-btn {
            transition: all 0.3s ease;
        }
        
        .social-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }
        
        .password-strength {
            height: 4px;
            border-radius: 2px;
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="auth-container">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Header -->
            <div class="text-center">
                <a href="/" class="text-4xl font-bold text-blue-600">HabibiStay</a>
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900">Create your account</h2>
                <p class="mt-2 text-sm text-gray-600">
                    Already have an account?
                    <a href="/login" class="font-medium text-blue-600 hover:text-blue-500">Sign in</a>
                </p>
            </div>

            <!-- Register Form -->
            <div class="auth-card p-8">
                <form class="space-y-6" action="/register" method="POST">
                    @csrf
                    
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Full name</label>
                        <div class="mt-1 relative">
                            <input id="name" name="name" type="text" autocomplete="name" required 
                                   class="appearance-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                                   placeholder="Enter your full name"
                                   value="{{ old('name') }}">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <i class="fas fa-user text-gray-400"></i>
                            </div>
                        </div>
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                        <div class="mt-1 relative">
                            <input id="email" name="email" type="email" autocomplete="email" required 
                                   class="appearance-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                                   placeholder="Enter your email"
                                   value="{{ old('email') }}">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <i class="fas fa-envelope text-gray-400"></i>
                            </div>
                        </div>
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Phone -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">Phone number</label>
                        <div class="mt-1 relative">
                            <input id="phone" name="phone" type="tel" autocomplete="tel" 
                                   class="appearance-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                                   placeholder="Enter your phone number"
                                   value="{{ old('phone') }}">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <i class="fas fa-phone text-gray-400"></i>
                            </div>
                        </div>
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <div class="mt-1 relative">
                            <input id="password" name="password" type="password" autocomplete="new-password" required 
                                   class="appearance-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                                   placeholder="Create a password"
                                   onkeyup="checkPasswordStrength()">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <button type="button" onclick="togglePassword('password')" class="text-gray-400 hover:text-gray-600">
                                    <i id="passwordIcon" class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <!-- Password Strength Indicator -->
                        <div class="mt-2">
                            <div class="password-strength bg-gray-200" id="passwordStrength"></div>
                            <p id="passwordStrengthText" class="text-xs text-gray-500 mt-1">Password strength</p>
                        </div>
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm password</label>
                        <div class="mt-1 relative">
                            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required 
                                   class="appearance-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                                   placeholder="Confirm your password">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <button type="button" onclick="togglePassword('password_confirmation')" class="text-gray-400 hover:text-gray-600">
                                    <i id="confirmPasswordIcon" class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        @error('password_confirmation')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Terms Agreement -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input id="terms" name="terms" type="checkbox" required
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="terms" class="text-gray-700">
                                I agree to the 
                                <a href="/terms" target="_blank" class="text-blue-600 hover:text-blue-500">Terms of Service</a> 
                                and 
                                <a href="/privacy" target="_blank" class="text-blue-600 hover:text-blue-500">Privacy Policy</a>
                            </label>
                        </div>
                    </div>

                    <!-- Marketing Emails -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input id="marketing" name="marketing" type="checkbox"
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="marketing" class="text-gray-700">
                                Send me special offers, travel tips, and updates
                            </label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button type="submit" 
                                class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white brand-gradient hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300">
                            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <i class="fas fa-user-plus text-blue-300 group-hover:text-blue-200"></i>
                            </span>
                            Create account
                        </button>
                    </div>
                </form>

                <!-- Divider -->
                <div class="mt-6">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white text-gray-500">Or sign up with</span>
                        </div>
                    </div>
                </div>

                <!-- Social Login -->
                <div class="mt-6 grid grid-cols-3 gap-3">
                    <button type="button" onclick="socialLogin('google')" 
                            class="social-btn w-full inline-flex justify-center py-3 px-4 border border-gray-300 rounded-lg shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <i class="fab fa-google text-red-500"></i>
                    </button>
                    <button type="button" onclick="socialLogin('facebook')" 
                            class="social-btn w-full inline-flex justify-center py-3 px-4 border border-gray-300 rounded-lg shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <i class="fab fa-facebook text-blue-600"></i>
                    </button>
                    <button type="button" onclick="socialLogin('apple')" 
                            class="social-btn w-full inline-flex justify-center py-3 px-4 border border-gray-300 rounded-lg shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <i class="fab fa-apple text-gray-900"></i>
                    </button>
                </div>

                <!-- Sara AI Help -->
                <div class="mt-6 text-center">
                    <button type="button" onclick="openSaraChat()" 
                            class="text-blue-600 hover:text-blue-500 text-sm font-medium">
                        <i class="fas fa-robot mr-1"></i>Need help? Ask Sara
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(fieldId) {
            const passwordInput = document.getElementById(fieldId);
            const iconId = fieldId === 'password' ? 'passwordIcon' : 'confirmPasswordIcon';
            const passwordIcon = document.getElementById(iconId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                passwordIcon.className = 'fas fa-eye';
            }
        }

        function checkPasswordStrength() {
            const password = document.getElementById('password').value;
            const strengthBar = document.getElementById('passwordStrength');
            const strengthText = document.getElementById('passwordStrengthText');
            
            let strength = 0;
            let text = 'Weak';
            let color = '#ef4444';
            
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            switch (strength) {
                case 0:
                case 1:
                    text = 'Very weak';
                    color = '#ef4444';
                    break;
                case 2:
                    text = 'Weak';
                    color = '#f97316';
                    break;
                case 3:
                    text = 'Fair';
                    color = '#eab308';
                    break;
                case 4:
                    text = 'Good';
                    color = '#22c55e';
                    break;
                case 5:
                    text = 'Strong';
                    color = '#16a34a';
                    break;
            }
            
            strengthBar.style.width = (strength * 20) + '%';
            strengthBar.style.backgroundColor = color;
            strengthText.textContent = text;
            strengthText.style.color = color;
        }

        function socialLogin(provider) {
            window.location.href = `/auth/${provider}`;
        }

        function openSaraChat() {
            window.location.href = '/sara';
        }

        // Auto-focus name field
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('name').focus();
        });
    </script>
</body>
</html>

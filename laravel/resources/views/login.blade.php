@extends('layout.frontend')
@push('title', 'Login')
@section('css')
<style>
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fadeIn {
        animation: fadeIn 0.3s ease-out;
    }
</style>
@endsection

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-cyan-50 to-slate-50 flex items-center justify-center p-4 relative overflow-hidden">
        <!-- Animated background elements -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-blue-200/30 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-cyan-200/30 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
            <div class="absolute top-1/2 left-1/2 w-96 h-96 bg-blue-100/40 rounded-full blur-3xl animate-pulse" style="animation-delay: 2s;"></div>
        </div>

        <!-- Grid pattern overlay -->
        <div class="absolute inset-0 bg-[linear-gradient(rgba(59,130,246,0.08)_1px,transparent_1px),linear-gradient(90deg,rgba(59,130,246,0.08)_1px,transparent_1px)] bg-[size:50px_50px]"></div>

        <!-- Login card -->
        <div class="relative z-10 w-full max-w-md">
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-2xl border border-white/60 overflow-hidden">
                <!-- Header with icon -->
                <div class="text-center pt-8 pb-6 px-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-br from-blue-500 to-cyan-500 mb-4 shadow-lg shadow-blue-500/50">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                            <path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-slate-900 mb-2" id="formTitle">
                        Welcome Back
                    </h1>
                    <p class="text-slate-600 text-sm" id="formSubtitle">
                        Enter your credentials to continue
                    </p>
                </div>

                <!-- Success/Error Messages -->
                @if (session('success'))
                    <div class="mx-8 mb-4 p-4 bg-green-50 border border-green-200 rounded-lg animate-fadeIn">
                        <div class="flex items-center space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-600">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                            <p class="text-sm text-green-800 font-medium">{{ session('success') }}</p>
                        </div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mx-8 mb-4 p-4 bg-red-50 border border-red-200 rounded-lg animate-fadeIn">
                        <div class="flex items-start space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-red-600 mt-0.5 flex-shrink-0">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" x2="12" y1="8" y2="12"></line>
                                <line x1="12" x2="12.01" y1="16" y2="16"></line>
                            </svg>
                            <div class="flex-1">
                                @foreach ($errors->all() as $error)
                                    <p class="text-sm text-red-800 font-medium">{{ $error }}</p>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Form -->
                <form id="authForm" action="{{ route('loginaction') }}" method="POST" class="px-8 pb-8 space-y-5">
                    @csrf
                    <!-- Name field (hidden by default for login) -->
                    <div id="nameField" class="space-y-2 hidden">
                        <label class="text-sm font-medium text-slate-700 block">Full Name</label>
                        <div class="relative">
                            <input
                                type="text"
                                id="name"
                                name="name"
                                class="w-full px-4 py-3 bg-white/60 border border-slate-200 rounded-lg text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-all duration-200"
                                placeholder="John Doe"
                                value="{{ old('name') }}"
                            />
                        </div>
                    </div>

                    <!-- Username field (hidden by default for login) -->
                    <div id="usernameField" class="space-y-2 hidden">
                        <label class="text-sm font-medium text-slate-700 block">Username</label>
                        <div class="relative">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            <input
                                type="text"
                                id="username"
                                name="username"
                                class="w-full pl-11 pr-4 py-3 bg-white/60 border border-slate-200 rounded-lg text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-all duration-200 @error('username') border-red-400 @enderror"
                                placeholder="johndoe"
                                value="{{ old('username') }}"
                            />
                        </div>
                    </div>

                    <!-- Email field -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700 block">Email Address</label>
                        <div class="relative">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                                <rect width="20" height="16" x="2" y="4" rx="2"/>
                                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                            </svg>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="w-full pl-11 pr-4 py-3 bg-white/60 border border-slate-200 rounded-lg text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-all duration-200 @error('email') border-red-400 @enderror"
                                placeholder="you@example.com"
                                value="{{ old('email') }}"
                                required
                            />
                        </div>
                    </div>

                    <!-- Password field -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700 block">Password</label>
                        <div class="relative">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                                <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="w-full pl-11 pr-12 py-3 bg-white/60 border border-slate-200 rounded-lg text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-all duration-200 @error('password') border-red-400 @enderror"
                                placeholder="••••••••"
                                required
                            />
                            <button
                                type="button"
                                id="togglePassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors"
                            >
                                <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg id="eyeOffIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="hidden">
                                    <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
                                    <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                                    <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                                    <line x1="2" x2="22" y1="2" y2="22"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Confirm Password field (hidden by default for login) -->
                    <div id="confirmPasswordField" class="space-y-2 hidden">
                        <label class="text-sm font-medium text-slate-700 block">Confirm Password</label>
                        <div class="relative">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                                <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                class="w-full pl-11 pr-12 py-3 bg-white/60 border border-slate-200 rounded-lg text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-all duration-200 @error('password_confirmation') border-red-400 @enderror"
                                placeholder="••••••••"
                            />
                            <button
                                type="button"
                                id="toggleConfirmPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors"
                            >
                                <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg class="eye-off-icon hidden" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
                                    <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                                    <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                                    <line x1="2" x2="22" y1="2" y2="22"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Remember me & Forgot password (shown only in login mode) -->
                    <div id="loginOptions" class="flex items-center justify-between text-sm">
                        <label class="flex items-center text-slate-600 cursor-pointer hover:text-slate-800 transition-colors">
                            <input type="checkbox" name="remember" class="mr-2 rounded bg-white border-slate-300" />
                            Remember me
                        </label>
                        <a href="{{ route('password.request') }}" class="text-blue-600 hover:text-blue-700 transition-colors font-medium">
                            Forgot password?
                        </a>
                    </div>

                    <!-- Submit button -->
                    <button
                        type="submit"
                        class="w-full py-3 px-4 bg-gradient-to-r from-blue-500 to-cyan-500 text-white font-medium rounded-lg shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200"
                        id="submitButton"
                    >
                        Sign In
                    </button>
                </form>

                <!-- Footer -->
                <div class="px-8 pb-8 text-center">
                    <p class="text-slate-600 text-sm">
                        <span id="toggleText">Don't have an account? </span>
                        <button
                            id="toggleMode"
                            class="text-blue-600 hover:text-blue-700 font-medium transition-colors"
                        >
                            <span id="toggleButton">Sign up</span>
                        </button>
                    </p>
                </div>
            </div>

            <!-- Decorative elements -->
            <div class="absolute -top-4 -right-4 w-24 h-24 bg-blue-300/40 rounded-full blur-2xl"></div>
            <div class="absolute -bottom-4 -left-4 w-24 h-24 bg-cyan-300/40 rounded-full blur-2xl"></div>
        </div>
    </div>

    <script>
        let isLogin = true;
        const togglePassword = document.getElementById('togglePassword');
        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('password_confirmation');
        const eyeIcon = document.getElementById('eyeIcon');
        const eyeOffIcon = document.getElementById('eyeOffIcon');
        const toggleMode = document.getElementById('toggleMode');
        const nameField = document.getElementById('nameField');
        const usernameField = document.getElementById('usernameField');
        const confirmPasswordField = document.getElementById('confirmPasswordField');
        const loginOptions = document.getElementById('loginOptions');
        const formTitle = document.getElementById('formTitle');
        const formSubtitle = document.getElementById('formSubtitle');
        const submitButton = document.getElementById('submitButton');
        const toggleText = document.getElementById('toggleText');
        const toggleButton = document.getElementById('toggleButton');
        const authForm = document.getElementById('authForm');

        // Toggle password visibility
        togglePassword.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            eyeIcon.classList.toggle('hidden');
            eyeOffIcon.classList.toggle('hidden');
        });

        // Toggle confirm password visibility
        toggleConfirmPassword.addEventListener('click', () => {
            const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPasswordInput.setAttribute('type', type);
            const eyeIcons = toggleConfirmPassword.querySelectorAll('svg');
            eyeIcons.forEach(icon => icon.classList.toggle('hidden'));
        });

        // Toggle between login and signup
        toggleMode.addEventListener('click', () => {
            isLogin = !isLogin;

            if (isLogin) {
                // Switch to login mode
                formTitle.textContent = 'Welcome Back';
                formSubtitle.textContent = 'Enter your credentials to continue';
                submitButton.textContent = 'Sign In';
                toggleText.textContent = "Don't have an account? ";
                toggleButton.textContent = 'Sign up';
                nameField.classList.add('hidden');
                nameField.classList.remove('animate-fadeIn');
                usernameField.classList.add('hidden');
                usernameField.classList.remove('animate-fadeIn');
                confirmPasswordField.classList.add('hidden');
                confirmPasswordField.classList.remove('animate-fadeIn');
                loginOptions.classList.remove('hidden');
                document.getElementById('name').removeAttribute('required');
                document.getElementById('username').removeAttribute('required');
                document.getElementById('password_confirmation').removeAttribute('required');
                // Update form action to login route
                authForm.setAttribute('action', '{{ route("login") }}');
            } else {
                // Switch to signup mode
                formTitle.textContent = 'Create Account';
                formSubtitle.textContent = 'Join us and start your journey';
                submitButton.textContent = 'Create Account';
                toggleText.textContent = 'Already have an account? ';
                toggleButton.textContent = 'Sign in';
                nameField.classList.remove('hidden');
                nameField.classList.add('animate-fadeIn');
                usernameField.classList.remove('hidden');
                usernameField.classList.add('animate-fadeIn');
                confirmPasswordField.classList.remove('hidden');
                confirmPasswordField.classList.add('animate-fadeIn');
                loginOptions.classList.add('hidden');
                document.getElementById('name').setAttribute('required', '');
                document.getElementById('username').setAttribute('required', '');
                document.getElementById('password_confirmation').setAttribute('required', '');
                // Update form action to register route
                authForm.setAttribute('action', '{{ route("register.submit") }}');
            }
        });
    </script>
@endsection

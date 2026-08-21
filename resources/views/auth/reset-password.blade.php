@extends('layout.frontend')
@push('title', 'Reset Password')

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

        <!-- Reset Password Card -->
        <div class="relative z-10 w-full max-w-md">
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-2xl border border-white/60 overflow-hidden">
                <!-- Header with icon -->
                <div class="text-center pt-8 pb-6 px-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-br from-blue-500 to-cyan-500 mb-4 shadow-lg shadow-blue-500/50">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                            <path d="M21 2l-2 2m-1-1l-3 3m5 0l-3-3m-3 3l-4 4a5 5 0 1 1-7.07-7.07l4-4a5 5 0 0 1 7.07 0l1 1"></path>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">
                        Set New Password
                    </h1>
                    <p class="text-slate-600 text-sm">
                        Create a strong and secure new password for your account.
                    </p>
                </div>

                @if (isset($errors) && $errors->any())
                    <div class="mx-8 mb-4 p-4 bg-red-50 border border-red-200 rounded-lg animate-fadeIn">
                        <div class="flex items-start space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-red-600 mt-0.5 flex-shrink-0">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                            <div class="text-sm text-red-800">
                                @foreach ($errors->all() as $error)
                                    <p class="font-medium">{{ $error }}</p>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Form -->
                <form action="{{ route('password.update') }}" method="POST" class="px-8 pb-8 space-y-4">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Email Address
                        </label>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            required
                            readonly
                            value="{{ old('email', $email) }}"
                            class="w-full px-4 py-2.5 bg-slate-100 border border-slate-300 rounded-lg text-slate-700 focus:outline-none cursor-not-allowed"
                        />
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">
                            New Password
                        </label>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            required
                            autofocus
                            minlength="6"
                            class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                            placeholder="At least 6 characters"
                        />
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Confirm New Password
                        </label>
                        <input
                            type="password"
                            name="password_confirmation"
                            id="password_confirmation"
                            required
                            minlength="6"
                            class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                            placeholder="Re-enter your new password"
                        />
                    </div>

                    <button
                        type="submit"
                        class="w-full py-3 px-4 bg-gradient-to-r from-blue-500 to-cyan-500 text-white font-medium rounded-lg shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200"
                    >
                        Update Password &amp; Sign In
                    </button>
                </form>

                <!-- Footer -->
                <div class="px-8 pb-8 text-center border-t border-slate-100 pt-6">
                    <p class="text-slate-600 text-sm">
                        <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-700 font-medium transition-colors">
                            ← Back to Sign in
                        </a>
                    </p>
                </div>
            </div>

            <!-- Decorative elements -->
            <div class="absolute -top-4 -right-4 w-24 h-24 bg-blue-300/40 rounded-full blur-2xl"></div>
            <div class="absolute -bottom-4 -left-4 w-24 h-24 bg-cyan-300/40 rounded-full blur-2xl"></div>
        </div>
    </div>
@endsection

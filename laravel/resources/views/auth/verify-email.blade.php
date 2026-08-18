@extends('layout.frontend')
@push('title', 'Verify Your Email')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-cyan-50 to-slate-50 flex items-center justify-center p-4">
    <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-2xl border border-white/60 overflow-hidden max-w-md w-full">
        <!-- Header -->
        <div class="text-center pt-8 pb-6 px-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-br from-blue-500 to-cyan-500 mb-4 shadow-lg shadow-blue-500/50">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                    <rect width="20" height="16" x="2" y="4" rx="2"/>
                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-slate-900 mb-2">Verify Your Email</h1>
            <p class="text-slate-600 text-sm">We've sent a verification link to your email address</p>
        </div>

        <!-- Success/Error Messages -->
        @if (session('success'))
            <div class="mx-8 mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
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
            <div class="mx-8 mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
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

        <!-- Content -->
        <div class="px-8 pb-8">
            <div class="bg-blue-50 rounded-lg p-6 mb-6">
                <p class="text-slate-700 text-sm mb-4">
                    Before continuing, please check your email for a verification link. If you didn't receive the email, you can request a new one below.
                </p>

                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="w-full py-3 px-4 bg-gradient-to-r from-blue-500 to-cyan-500 text-white font-medium rounded-lg shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200">
                        Resend Verification Email
                    </button>
                </form>
            </div>

            <div class="text-center">
                <a href="{{ route('panel.dashboard') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium transition-colors">
                    Go to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

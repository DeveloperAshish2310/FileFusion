<nav class="ff-glass-nav w-full">
    <div class="max-w-[1240px] mx-auto px-7 py-4 flex items-center justify-between gap-5">
        <a href="{{ url('/') }}" class="flex items-center gap-2.5 text-decoration-none shrink-0 group">
            <div class="w-8 h-8 rounded-[9px] flex items-center justify-center shrink-0" style="background: var(--ff-accent-grad);">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                </svg>
            </div>
            <span class="font-outfit text-xl font-bold tracking-tight text-[var(--ff-text)]">
                {{ env('APP_NAME', 'FileFusion') }}
            </span>
        </a>

        <!-- Desktop Navigation -->
        <div class="hidden md:flex items-center gap-8">
            <a href="{{ url('/') }}" class="text-[14.5px] font-medium transition-colors hover:text-[var(--ff-accent)] {{ \Request::routeIs('home') ? 'text-[var(--ff-accent)] font-semibold' : 'text-[var(--ff-text-secondary)]' }}">Home</a>
            <a href="{{ route('features') }}" class="text-[14.5px] font-medium transition-colors hover:text-[var(--ff-accent)] {{ \Request::routeIs('features') ? 'text-[var(--ff-accent)] font-semibold' : 'text-[var(--ff-text-secondary)]' }}">Features</a>
            <a href="{{ route('pricing') }}" class="text-[14.5px] font-medium transition-colors hover:text-[var(--ff-accent)] {{ \Request::routeIs('pricing') ? 'text-[var(--ff-accent)] font-semibold' : 'text-[var(--ff-text-secondary)]' }}">Pricing</a>
            <a href="{{ route('about') }}" class="text-[14.5px] font-medium transition-colors hover:text-[var(--ff-accent)] {{ \Request::routeIs('about') ? 'text-[var(--ff-accent)] font-semibold' : 'text-[var(--ff-text-secondary)]' }}">About</a>
            <a href="{{ route('contact') }}" class="text-[14.5px] font-medium transition-colors hover:text-[var(--ff-accent)] {{ \Request::routeIs('contact') ? 'text-[var(--ff-accent)] font-semibold' : 'text-[var(--ff-text-secondary)]' }}">Contact</a>
        </div>

        <div class="hidden md:flex items-center gap-3.5 shrink-0">
            @auth
                <a href="{{ route('panel.dashboard') }}" class="ff-btn-primary">
                    <i class="ri-dashboard-3-line mr-1.5"></i> Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" class="text-[14.5px] font-semibold text-[var(--ff-text)] hover:text-[var(--ff-accent)] transition-colors px-2 py-1">Log in</a>
                <a href="{{ route('register') }}" class="ff-btn-primary">Get Started</a>
            @endauth
        </div>

        <!-- Mobile Menu Trigger -->
        <button id="mobile-menu-button" type="button" class="md:hidden text-[var(--ff-text)] p-1.5 focus:outline-none" aria-label="Toggle Navigation">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
        </button>
    </div>

    <!-- Mobile Drawer Menu -->
    <div id="mobile-menu" class="hidden md:hidden px-5 pb-5 pt-2 border-t border-[var(--ff-border)] bg-[var(--ff-bg)]">
        <div class="flex flex-col gap-1">
            <a href="{{ url('/') }}" class="py-2.5 px-3 text-[15px] font-semibold rounded-lg text-[var(--ff-text)] hover:bg-[var(--ff-bg2)]">Home</a>
            <a href="{{ route('features') }}" class="py-2.5 px-3 text-[15px] font-semibold rounded-lg text-[var(--ff-text)] hover:bg-[var(--ff-bg2)]">Features</a>
            <a href="{{ route('pricing') }}" class="py-2.5 px-3 text-[15px] font-semibold rounded-lg text-[var(--ff-text)] hover:bg-[var(--ff-bg2)]">Pricing</a>
            <a href="{{ route('about') }}" class="py-2.5 px-3 text-[15px] font-semibold rounded-lg text-[var(--ff-text)] hover:bg-[var(--ff-bg2)]">About</a>
            <a href="{{ route('contact') }}" class="py-2.5 px-3 text-[15px] font-semibold rounded-lg text-[var(--ff-text)] hover:bg-[var(--ff-bg2)]">Contact</a>
            
            <div class="pt-2 mt-2 border-t border-[var(--ff-border)] flex flex-col gap-2">
                @auth
                    <a href="{{ route('panel.dashboard') }}" class="ff-btn-primary w-full text-center">
                        <i class="ri-dashboard-3-line mr-1.5"></i> Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="py-2.5 px-3 text-[15px] font-semibold text-center rounded-lg border border-[var(--ff-border)] text-[var(--ff-text)]">Log in</a>
                    <a href="{{ route('register') }}" class="ff-btn-primary w-full text-center">Get Started Free</a>
                @endauth
            </div>
        </div>
    </div>
</nav>


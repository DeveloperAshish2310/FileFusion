<footer class="pt-14 pb-7 bg-[var(--ff-bg)] border-t border-[var(--ff-border)]">
    <div class="max-w-[1240px] mx-auto px-7">
        <div class="grid grid-cols-1 md:grid-cols-[1.4fr_repeat(3,minmax(0,1fr))] gap-9 pb-9 border-b border-[var(--ff-border)]">
            <div>
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background: var(--ff-accent-grad);">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                        </svg>
                    </div>
                    <span class="font-outfit text-[17px] font-bold text-[var(--ff-text)]">{{ env('APP_NAME', 'FileFusion') }}</span>
                </div>
                <p class="text-[13.5px] text-[var(--ff-text-soft)] mt-3.5 max-w-[240px] leading-relaxed">
                    One home for your files, notes, and links.
                </p>
            </div>

            <div>
                <div class="text-[12.5px] font-bold tracking-wider uppercase text-[var(--ff-text-soft)] mb-3.5">Product</div>
                <div class="flex flex-col gap-2.5">
                    <a href="{{ route('features') }}" class="text-[14px] text-[var(--ff-text-secondary)] hover:text-[var(--ff-accent)] transition-colors text-decoration-none">Features</a>
                    <a href="{{ route('pricing') }}" class="text-[14px] text-[var(--ff-text-secondary)] hover:text-[var(--ff-accent)] transition-colors text-decoration-none">Pricing</a>
                    @auth
                        <a href="{{ route('panel.dashboard') }}" class="text-[14px] text-[var(--ff-text-secondary)] hover:text-[var(--ff-accent)] transition-colors text-decoration-none">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="text-[14px] text-[var(--ff-text-secondary)] hover:text-[var(--ff-accent)] transition-colors text-decoration-none">Log in</a>
                    @endauth
                </div>
            </div>

            <div>
                <div class="text-[12.5px] font-bold tracking-wider uppercase text-[var(--ff-text-soft)] mb-3.5">Company</div>
                <div class="flex flex-col gap-2.5">
                    <a href="{{ route('about') }}" class="text-[14px] text-[var(--ff-text-secondary)] hover:text-[var(--ff-accent)] transition-colors text-decoration-none">About</a>
                    <a href="{{ route('contact') }}" class="text-[14px] text-[var(--ff-text-secondary)] hover:text-[var(--ff-accent)] transition-colors text-decoration-none">Contact</a>
                </div>
            </div>

            <div>
                <div class="text-[12.5px] font-bold tracking-wider uppercase text-[var(--ff-text-soft)] mb-3.5">Legal</div>
                <div class="flex flex-col gap-2.5">
                    <a href="#" class="text-[14px] text-[var(--ff-text-secondary)] hover:text-[var(--ff-accent)] transition-colors text-decoration-none">Terms of Service</a>
                    <a href="#" class="text-[14px] text-[var(--ff-text-secondary)] hover:text-[var(--ff-accent)] transition-colors text-decoration-none">Privacy Policy</a>
                </div>
            </div>
        </div>

        <div class="flex flex-col md:flex-row items-center justify-between gap-4 pt-5">
            <div class="text-[13px] text-[var(--ff-text-soft)]">
                © {{ date('Y') }} FileFusion. All rights reserved.
            </div>
            <div class="flex gap-5">
                <a href="#" class="text-[13px] text-[var(--ff-text-soft)] hover:text-[var(--ff-accent)] transition-colors">Terms of Service</a>
                <a href="#" class="text-[13px] text-[var(--ff-text-soft)] hover:text-[var(--ff-accent)] transition-colors">Privacy Policy</a>
            </div>
        </div>
    </div>
</footer>


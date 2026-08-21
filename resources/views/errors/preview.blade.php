@extends('errors.layout')

@section('title', 'Error Pages Showcase')

@section('styles')
<style>
    .ff-preview-tabs {
        display: inline-flex;
        gap: 6px;
        padding: 5px;
        background: var(--ff-card-bg);
        border: 1px solid var(--ff-border);
        border-radius: 12px;
        margin-bottom: 24px;
        z-index: 10;
        box-shadow: 0 4px 14px rgba(0,0,0,0.15);
    }
    .ff-preview-tab {
        padding: 7px 18px;
        border-radius: 9px;
        border: none;
        cursor: pointer;
        font-family: 'Inter', sans-serif;
        font-size: 13px;
        font-weight: 700;
        background: transparent;
        color: var(--ff-text-2);
        transition: all 0.16s ease;
    }
    .ff-preview-tab:hover {
        color: var(--ff-text);
    }
    .ff-preview-tab.is-active {
        background: linear-gradient(135deg, var(--ff-accent), var(--ff-accent-2));
        color: #ffffff;
        box-shadow: 0 4px 12px var(--ff-accent-glow);
    }
    .ff-error-state {
        display: none;
        flex-direction: column;
        align-items: center;
        width: 100%;
    }
    .ff-error-state.is-active {
        display: flex;
    }
</style>
@endsection

@section('content')
    <!-- Interactive Status Code Selector Tabs -->
    <div class="ff-preview-tabs">
        <button type="button" class="ff-preview-tab is-active" data-code="404">404 Not Found</button>
        <button type="button" class="ff-preview-tab" data-code="401">401 Unauthorized</button>
        <button type="button" class="ff-preview-tab" data-code="403">403 Forbidden</button>
        <button type="button" class="ff-preview-tab" data-code="502">502 Bad Gateway</button>
        <button type="button" class="ff-preview-tab" data-code="500">500 Server Error</button>
    </div>

    <!-- ==================== 404 STATE ==================== -->
    <div class="ff-error-state is-active" id="state-404">
        <!-- Radar Scanning Animation -->
        <div style="position:relative; width:220px; height:220px; animation:ff-fade 0.6s ease both;">
            <div style="position:absolute; inset:0; border-radius:50%; background:var(--ff-radar-bg); border:1px solid var(--ff-radar-ring); box-shadow:inset 0 0 60px -20px var(--ff-accent-glow);"></div>
            <div style="position:absolute; inset:24px; border-radius:50%; border:1px solid var(--ff-radar-ring);"></div>
            <div style="position:absolute; inset:60px; border-radius:50%; border:1px solid var(--ff-radar-ring);"></div>
            <div style="position:absolute; inset:96px; border-radius:50%; border:1px solid var(--ff-radar-ring);"></div>
            <div style="position:absolute; left:50%; top:12px; bottom:12px; width:1px; background:var(--ff-radar-ring); transform:translateX(-50%);"></div>
            <div style="position:absolute; top:50%; left:12px; right:12px; height:1px; background:var(--ff-radar-ring); transform:translateY(-50%);"></div>
            
            <!-- Expanding Ping Ring -->
            <div style="position:absolute; inset:0; border-radius:50%; border:2px solid var(--ff-accent); animation:ff-ring 3s ease-out infinite;"></div>
            
            <!-- Conic Sweep -->
            <div style="position:absolute; inset:0; border-radius:50%; overflow:hidden;">
                <div style="position:absolute; left:50%; top:50%; width:50%; height:50%; transform-origin:top left; animation:ff-sweep 4s linear infinite; background:conic-gradient(from 0deg at top left, color-mix(in srgb, var(--ff-accent) 70%, transparent) 0deg, transparent 55deg);"></div>
                <div style="position:absolute; left:50%; top:50%; width:50%; height:2px; transform-origin:left center; animation:ff-sweep 4s linear infinite; background:linear-gradient(90deg, var(--ff-accent), transparent);"></div>
            </div>
            
            <!-- Blips -->
            <div style="position:absolute; left:66%; top:38%; width:9px; height:9px; border-radius:50%; background:var(--ff-accent); box-shadow:0 0 12px 2px var(--ff-accent-glow); animation:ff-blip 2.2s ease-in-out infinite;"></div>
            <div style="position:absolute; left:34%; top:64%; width:7px; height:7px; border-radius:50%; background:var(--ff-accent-2); animation:ff-blip 2.6s ease-in-out 0.6s infinite;"></div>
            
            <!-- Center Core -->
            <div style="position:absolute; left:50%; top:50%; transform:translate(-50%,-50%); width:16px; height:16px; border-radius:50%; background:linear-gradient(135deg, var(--ff-accent), var(--ff-accent-2)); box-shadow:0 0 16px 3px var(--ff-accent-glow);"></div>
        </div>

        <div class="ff-error-code">404</div>
        <div class="ff-error-tag">Signal Lost</div>
        <h1 class="ff-error-heading ff-stag" style="animation-delay:0.14s;">We can't locate that page</h1>
        <p class="ff-error-desc ff-stag" style="animation-delay:0.22s;">The file, link, or transfer you're scanning for isn't on our radar. It may have expired, moved, or never existed.</p>

        <div class="ff-error-actions ff-stag" style="animation-delay:0.3s;">
            <a href="{{ url('/') }}" class="ff-btn-p"><i data-lucide="home" class="w-4 h-4"></i> Back to home</a>
            <a href="{{ route('panel.filelist') }}" class="ff-btn-s"><i data-lucide="search" class="w-4 h-4"></i> Search files</a>
        </div>
    </div>

    <!-- ==================== 401 STATE ==================== -->
    <div class="ff-error-state" id="state-401">
        <div style="position:relative; width:200px; height:200px; display:flex; align-items:center; justify-content:center; animation:ff-fade 0.6s ease both;">
            <div style="position:absolute; inset:0; border-radius:50%; background:radial-gradient(circle, var(--ff-accent-glow) 0%, transparent 68%); animation:ff-pulse 4s ease-in-out infinite;"></div>
            <div style="position:absolute; width:200px; height:200px; border-radius:50%; border:2px dashed color-mix(in srgb, var(--ff-accent) 60%, transparent); animation:ff-spinCW 26s linear infinite;"></div>
            <div style="position:absolute; width:160px; height:160px; border-radius:50%; border:2px dashed var(--ff-border); animation:ff-spinCCW 20s linear infinite;"></div>
            <div style="position:relative; width:124px; height:124px; border-radius:50%; background:linear-gradient(135deg, var(--ff-accent), var(--ff-accent-2)); display:flex; align-items:center; justify-content:center; box-shadow:0 24px 50px -16px var(--ff-accent-glow), inset 0 3px 10px rgba(255,255,255,0.25);">
                <svg width="54" height="54" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="4" y="10.5" width="16" height="10" rx="2.5"></rect>
                    <path d="M8 10.5V7a4 4 0 0 1 8 0v3.5"></path>
                    <circle cx="12" cy="15" r="1.6"></circle>
                    <path d="M12 15.8v2.2"></path>
                </svg>
            </div>
        </div>

        <div class="ff-error-code">401</div>
        <div class="ff-error-tag">Error 401 &bull; Locked</div>
        <h1 class="ff-error-heading ff-stag" style="animation-delay:0.1s;">Authentication required</h1>
        <p class="ff-error-desc ff-stag" style="animation-delay:0.18s;">Your session has expired or you're not signed in. Please sign in to verify your identity and unlock this credential or resource.</p>

        <!-- Keycard Swipe Bar -->
        <div class="ff-stag" style="animation-delay:0.26s; margin-top:24px; width:240px; height:50px; border-radius:12px; background:var(--ff-card-bg); border:1px solid var(--ff-border); display:flex; align-items:center; padding:0 8px; overflow:hidden; position:relative;">
            <div style="width:52px; height:34px; border-radius:6px; background:linear-gradient(135deg, var(--ff-accent), var(--ff-accent-2)); display:flex; align-items:center; justify-content:flex-end; padding-right:6px; animation:ff-swipe 3s ease-in-out infinite; box-shadow:0 4px 12px var(--ff-accent-glow);">
                <div style="width:7px; height:20px; border-radius:2px; background:rgba(255,255,255,0.6);"></div>
            </div>
            <div style="position:absolute; right:16px; font-family:'JetBrains Mono',monospace; font-size:11px; font-weight:700; color:var(--ff-text-soft);">swipe to unlock</div>
        </div>

        <div class="ff-error-actions ff-stag" style="animation-delay:0.34s;">
            <a href="{{ route('login') }}" class="ff-btn-p"><i data-lucide="log-in" class="w-4 h-4"></i> Sign in</a>
            <a href="javascript:history.back()" class="ff-btn-s"><i data-lucide="arrow-left" class="w-4 h-4"></i> Go back</a>
        </div>
    </div>

    <!-- ==================== 403 STATE ==================== -->
    <div class="ff-error-state" id="state-403">
        <div style="display:flex; flex-direction:column; align-items:center; animation:ff-fade 0.6s ease both; width:100%; max-width:520px;">
            <div style="position:relative; width:100%; border-radius:16px; overflow:hidden; border:1px solid var(--ff-border); box-shadow:0 12px 30px -10px rgba(0,0,0,0.3);">
                <div style="height:116px; background:repeating-linear-gradient(-45deg, var(--ff-accent-2) 0 26px, var(--ff-hazard-dark) 26px 52px); display:flex; align-items:center; justify-content:center;">
                    <div style="animation:ff-stamp 0.7s cubic-bezier(0.34,1.56,0.64,1) both; padding:10px 24px; border:3.5px solid #ffffff; border-radius:10px; background:rgba(20,18,26,0.78); backdrop-filter:blur(4px); box-shadow:0 8px 24px rgba(0,0,0,0.5);">
                        <span style="font-family:'Outfit',sans-serif; font-size:28px; font-weight:800; letter-spacing:0.14em; color:#ffffff;">RESTRICTED</span>
                    </div>
                </div>
            </div>
            <div style="width:92px; height:92px; margin-top:-46px; border-radius:50%; background:var(--ff-card); border:1px solid var(--ff-border); display:flex; align-items:center; justify-content:center; position:relative; z-index:2; box-shadow:0 18px 40px -14px rgba(0,0,0,0.6);">
                <div style="width:68px; height:68px; border-radius:50%; background:linear-gradient(135deg, var(--ff-accent), var(--ff-accent-2)); display:flex; align-items:center; justify-content:center; box-shadow:inset 0 2px 8px rgba(255,255,255,0.25), 0 8px 20px var(--ff-accent-glow);">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 3l7 3v5c0 4.5-3 8-7 10-4-2-7-5.5-7-10V6z"></path>
                        <line x1="9" y1="12" x2="15" y2="12"></line>
                    </svg>
                </div>
            </div>
        </div>

        <div class="ff-error-code">403</div>
        <div class="ff-error-tag">Error 403 &bull; Forbidden</div>
        <h1 class="ff-error-heading ff-stag" style="animation-delay:0.12s;">Access forbidden</h1>
        <p class="ff-error-desc ff-stag" style="animation-delay:0.2s;">You don't have permission to open this resource or perform this action. Ask the owner for access or sign in with an authorized account.</p>

        <div class="ff-error-actions ff-stag" style="animation-delay:0.3s;">
            <a href="{{ route('contact') }}" class="ff-btn-p"><i data-lucide="message-square" class="w-4 h-4"></i> Request access</a>
            <a href="{{ url('/') }}" class="ff-btn-s"><i data-lucide="home" class="w-4 h-4"></i> Back to home</a>
        </div>
    </div>

    <!-- ==================== 502 STATE ==================== -->
    <div class="ff-error-state" id="state-502">
        <div style="display:flex; align-items:center; justify-content:center; animation:ff-fade 0.6s ease both;">
            <div style="width:80px; height:80px; border-radius:18px; background:var(--ff-node-bg); border:1px solid var(--ff-border); display:flex; align-items:center; justify-content:center; box-shadow:0 16px 36px -16px rgba(0,0,0,0.5);">
                <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="var(--ff-text-2)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="4" y="9" width="16" height="6" rx="1.5"></rect>
                    <rect x="4" y="15.5" width="16" height="5" rx="1.5" opacity="0.5"></rect>
                    <line x1="7.5" y1="12" x2="7.6" y2="12"></line>
                </svg>
            </div>
            <div style="position:relative; width:120px; display:flex; align-items:center; justify-content:center;">
                <svg width="120" height="40" viewBox="0 0 120 40" fill="none">
                    <path d="M2 20h44" stroke="var(--ff-accent)" stroke-width="3" stroke-linecap="round" stroke-dasharray="8 6" style="animation:ff-dash 1s linear infinite;"></path>
                    <path d="M74 20h44" stroke="var(--ff-border)" stroke-width="3" stroke-linecap="round" stroke-dasharray="8 6"></path>
                </svg>
                <div style="position:absolute; width:26px; height:26px; border-radius:50%; background:radial-gradient(circle, var(--ff-accent), var(--ff-accent-2)); display:flex; align-items:center; justify-content:center; animation:ff-spark 1.4s ease-in-out infinite; box-shadow:0 0 22px 4px var(--ff-accent-glow);">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="#ffffff"><path d="M13 2L4 14h6l-1 8 9-12h-6z"></path></svg>
                </div>
            </div>
            <div style="width:80px; height:80px; border-radius:18px; background:linear-gradient(135deg, var(--ff-accent), var(--ff-accent-2)); display:flex; align-items:center; justify-content:center; box-shadow:0 20px 44px -16px var(--ff-accent-glow); animation:ff-shake 3s ease-in-out infinite;">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17.5 19a4.5 4.5 0 0 0 .5-8.98A6 6 0 0 0 6.2 9.2 4 4 0 0 0 6.5 19"></path>
                    <line x1="12" y1="12.5" x2="12" y2="16"></line>
                    <line x1="12" y1="18.5" x2="12.01" y2="18.5"></line>
                </svg>
            </div>
        </div>

        <div style="margin-top:28px; width:360px; max-width:100%; border-radius:12px; overflow:hidden; border:1px solid var(--ff-border); background:var(--ff-terminal-bg); text-align:left; box-shadow:0 12px 28px -10px rgba(0,0,0,0.4);">
            <div style="display:flex; align-items:center; gap:7px; padding:9px 14px; background:var(--ff-terminal-bar); border-bottom:1px solid var(--ff-border);">
                <span style="width:9px; height:9px; border-radius:50%; background:#e0604f;"></span>
                <span style="width:9px; height:9px; border-radius:50%; background:#e0a24f;"></span>
                <span style="width:9px; height:9px; border-radius:50%; background:#5fb87e;"></span>
            </div>
            <div style="padding:14px 16px; font-family:'JetBrains Mono',monospace; font-size:12.5px; line-height:1.75;">
                <div style="color:var(--ff-text-soft);">$ GET /transfer/download</div>
                <div style="color:var(--ff-accent); font-weight:700;">502 Bad Gateway</div>
                <div style="color:var(--ff-text-2);">upstream did not respond<span style="color:var(--ff-accent); animation:ff-blip 1s infinite;">_</span></div>
            </div>
        </div>

        <div class="ff-error-code">502</div>
        <div class="ff-error-tag">Error 502 &bull; Bad Gateway</div>
        <h1 class="ff-error-heading ff-stag" style="animation-delay:0.14s;">Bad gateway</h1>
        <p class="ff-error-desc ff-stag" style="animation-delay:0.22s;">Our server couldn't reach the upstream processing service. This is almost always temporary — give it a moment and try again.</p>

        <div class="ff-error-actions ff-stag" style="animation-delay:0.3s;">
            <a href="javascript:location.reload()" class="ff-btn-p"><i data-lucide="refresh-cw" class="w-4 h-4"></i> Try again</a>
            <a href="{{ url('/') }}" class="ff-btn-s"><i data-lucide="home" class="w-4 h-4"></i> Back to home</a>
        </div>
    </div>

    <!-- ==================== 500 STATE ==================== -->
    <div class="ff-error-state" id="state-500">
        <div style="position:relative; width:180px; height:180px; display:flex; align-items:center; justify-content:center; animation:ff-fade 0.6s ease both;">
            <div style="position:absolute; inset:0; border-radius:50%; background:radial-gradient(circle, var(--ff-accent-glow) 0%, transparent 70%); animation:ff-pulse 3.5s ease-in-out infinite;"></div>
            <div style="position:absolute; width:180px; height:180px; border-radius:50%; border:2px dashed var(--ff-accent); animation:ff-spinCW 28s linear infinite;"></div>
            <div style="position:relative; width:110px; height:110px; border-radius:24px; background:linear-gradient(135deg, var(--ff-accent), var(--ff-accent-2)); display:flex; align-items:center; justify-content:center; box-shadow:0 24px 50px -16px var(--ff-accent-glow), inset 0 2px 8px rgba(255,255,255,0.25);">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path>
                    <line x1="12" y1="9" x2="12" y2="13"></line>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
            </div>
        </div>

        <div class="ff-error-code">500</div>
        <div class="ff-error-tag">Error 500 &bull; Server Exception</div>
        <h1 class="ff-error-heading ff-stag" style="animation-delay:0.12s;">Internal server error</h1>
        <p class="ff-error-desc ff-stag" style="animation-delay:0.2s;">Something unexpected occurred on our end while processing your request. Our automated telemetry has logged the issue for investigation.</p>

        <div class="ff-error-actions ff-stag" style="animation-delay:0.3s;">
            <a href="javascript:location.reload()" class="ff-btn-p"><i data-lucide="refresh-cw" class="w-4 h-4"></i> Reload page</a>
            <a href="{{ url('/') }}" class="ff-btn-s"><i data-lucide="home" class="w-4 h-4"></i> Back to home</a>
        </div>
    </div>

    <!-- Preview Tab Switching Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var tabs = document.querySelectorAll('.ff-preview-tab');
            var states = document.querySelectorAll('.ff-error-state');

            function showCode(code) {
                tabs.forEach(function(tab) {
                    tab.classList.toggle('is-active', tab.getAttribute('data-code') === code);
                });
                states.forEach(function(state) {
                    state.classList.toggle('is-active', state.id === 'state-' + code);
                });
                if (window.lucide) {
                    window.lucide.createIcons();
                }
            }

            tabs.forEach(function(tab) {
                tab.addEventListener('click', function() {
                    var code = this.getAttribute('data-code');
                    showCode(code);
                    try { history.replaceState(null, '', '{{ url("/errors/preview") }}/' + code); } catch (e) {}
                });
            });

            @if(isset($initialCode))
                showCode('{{ $initialCode }}');
            @endif
        });
    </script>
@endsection

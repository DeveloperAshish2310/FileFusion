@extends('layout.backend')

@push('title', 'Website Landing Page Editor')

@section('content')
<div class="ff-page">
    <!-- Header -->
    <div class="ff-admin-header">
        <div>
            <h1 class="ff-h1" style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#e0392e" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 20h9"/>
                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                </svg>
                Website Landing Page Editor
            </h1>
            <p class="ff-sub" style="margin: 0;">
                Customize copy, features, pricing, about story, and contact details live on your public website.
            </p>
        </div>
        <div class="ff-admin-actions" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <a href="{{ url('/') }}" target="_blank" class="ff-btn" style="flex: 1 1 auto; min-width: 140px; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                View Live Site
            </a>
            <form action="{{ route('panel.admin.landingPage.reset') }}" method="POST" onsubmit="return confirm('Reset all landing page content to factory defaults?');" style="flex: 1 1 auto; min-width: 140px; margin: 0; display: flex;">
                @csrf
                <button type="submit" class="ff-btn" style="width: 100%; background: rgba(239,68,68,0.12); color: #ef4444; border-color: rgba(239,68,68,0.3); display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                    Reset Defaults
                </button>
            </form>
        </div>
    </div>

    <!-- Section Navigation Tabs -->
    <div class="ff-admin-tabs" id="cmsTabList">
        <button type="button" class="cms-tab-btn active" data-target="tab-hero" style="padding: 10px 18px; font-size: 13.5px; font-weight: 700; border-radius: 8px; border: 1px solid transparent; cursor: pointer; white-space: nowrap; transition: all 0.2s;">
            Hero Section
        </button>
        <button type="button" class="cms-tab-btn" data-target="tab-features" style="padding: 10px 18px; font-size: 13.5px; font-weight: 700; border-radius: 8px; border: 1px solid transparent; cursor: pointer; white-space: nowrap; transition: all 0.2s;">
            Features
        </button>
        <button type="button" class="cms-tab-btn" data-target="tab-pricing" style="padding: 10px 18px; font-size: 13.5px; font-weight: 700; border-radius: 8px; border: 1px solid transparent; cursor: pointer; white-space: nowrap; transition: all 0.2s;">
            Pricing Plans
        </button>
        <button type="button" class="cms-tab-btn" data-target="tab-about" style="padding: 10px 18px; font-size: 13.5px; font-weight: 700; border-radius: 8px; border: 1px solid transparent; cursor: pointer; white-space: nowrap; transition: all 0.2s;">
            About & Founder
        </button>
        <button type="button" class="cms-tab-btn" data-target="tab-contact" style="padding: 10px 18px; font-size: 13.5px; font-weight: 700; border-radius: 8px; border: 1px solid transparent; cursor: pointer; white-space: nowrap; transition: all 0.2s;">
            Contact Info
        </button>
        <button type="button" class="cms-tab-btn" data-target="tab-cta" style="padding: 10px 18px; font-size: 13.5px; font-weight: 700; border-radius: 8px; border: 1px solid transparent; cursor: pointer; white-space: nowrap; transition: all 0.2s;">
            Call to Action Banner
        </button>
    </div>

    <!-- Main Editor Form -->
    <form action="{{ route('panel.admin.landingPage.update') }}" method="POST">
        @csrf

        <!-- HERO TAB SECTION -->
        <div id="tab-hero" class="cms-tab-panel">
            <div class="ff-card" style="padding: 24px; margin-bottom: 24px;">
                <h3 style="margin: 0 0 20px 0; font-size: 16px; font-weight: 700; color: var(--ff-text); border-bottom: 1px solid var(--ff-border); padding-bottom: 12px;">
                    Hero Header & Call to Action Copy
                </h3>

                <div style="display: grid; grid-template-columns: 1fr; gap: 18px; margin-bottom: 24px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); letter-spacing: 0.5px; margin-bottom: 6px;">Eyebrow Pill Badge Text</label>
                        <input type="text" name="hero_eyebrow" value="{{ $settings['hero_eyebrow'] ?? '' }}" class="ff-input" style="width: 100%;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); letter-spacing: 0.5px; margin-bottom: 6px;">Main Hero Headline</label>
                        <input type="text" name="hero_title" value="{{ $settings['hero_title'] ?? '' }}" class="ff-input" style="width: 100%; font-size: 16px; font-weight: 700;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); letter-spacing: 0.5px; margin-bottom: 6px;">Hero Subtitle / Description Paragraph</label>
                        <textarea name="hero_description" rows="3" class="ff-input" style="width: 100%; line-height: 1.5;">{{ $settings['hero_description'] ?? '' }}</textarea>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px;">
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); letter-spacing: 0.5px; margin-bottom: 6px;">Primary CTA Button Label</label>
                            <input type="text" name="hero_cta_primary" value="{{ $settings['hero_cta_primary'] ?? '' }}" class="ff-input" style="width: 100%;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); letter-spacing: 0.5px; margin-bottom: 6px;">Secondary Link Label</label>
                            <input type="text" name="hero_cta_secondary" value="{{ $settings['hero_cta_secondary'] ?? '' }}" class="ff-input" style="width: 100%;">
                        </div>
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); letter-spacing: 0.5px; margin-bottom: 6px;">Offer Guarantee / Storage Subtext</label>
                        <input type="text" name="hero_subtext" value="{{ $settings['hero_subtext'] ?? '' }}" class="ff-input" style="width: 100%;">
                    </div>
                </div>

                <h4 style="margin: 0 0 16px 0; font-size: 15px; font-weight: 700; color: var(--ff-text); border-top: 1px solid var(--ff-border); pt: 16px; padding-top: 16px;">
                    🖥️ Product Browser Mockup Preview Text
                </h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); letter-spacing: 0.5px; margin-bottom: 6px;">Address Bar URL</label>
                        <input type="text" name="hero_mockup_url" value="{{ $settings['hero_mockup_url'] ?? '' }}" class="ff-input" style="width: 100%;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); letter-spacing: 0.5px; margin-bottom: 6px;">Mockup Card Title</label>
                        <input type="text" name="hero_mockup_title" value="{{ $settings['hero_mockup_title'] ?? '' }}" class="ff-input" style="width: 100%;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); letter-spacing: 0.5px; margin-bottom: 6px;">Mockup Card Subtitle</label>
                        <input type="text" name="hero_mockup_sub" value="{{ $settings['hero_mockup_sub'] ?? '' }}" class="ff-input" style="width: 100%;">
                    </div>
                </div>
            </div>
        </div>

        <!-- FEATURES TAB SECTION -->
        <div id="tab-features" class="cms-tab-panel" style="display: none;">
            <div class="ff-card" style="padding: 24px; margin-bottom: 24px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; border-bottom: 1px solid var(--ff-border); padding-bottom: 12px;">
                    <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--ff-text);">
                        Features Section & Content Cards
                    </h3>
                    <button type="button" id="add-feature-btn" class="ff-btn" style="font-size: 13px;">
                        + Add Feature Card
                    </button>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; margin-bottom: 24px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); letter-spacing: 0.5px; margin-bottom: 6px;">Section Title</label>
                        <input type="text" name="features_title" value="{{ $settings['features_title'] ?? '' }}" class="ff-input" style="width: 100%;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); letter-spacing: 0.5px; margin-bottom: 6px;">Section Subtitle</label>
                        <input type="text" name="features_subtitle" value="{{ $settings['features_subtitle'] ?? '' }}" class="ff-input" style="width: 100%;">
                    </div>
                </div>

                @php
                    $featuresList = json_decode($settings['features_list'] ?? '[]', true) ?: [];
                @endphp

                <div id="features-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px;">
                    @foreach($featuresList as $idx => $feat)
                        <div class="feature-row" style="background: var(--ff-bg2); border: 1px solid var(--ff-border); border-radius: 12px; padding: 18px; position: relative;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                                <span style="font-size: 12px; font-weight: 700; color: var(--ff-text); background: var(--ff-surface); padding: 4px 10px; border-radius: 6px; border: 1px solid var(--ff-border);">
                                    Feature #<span class="feat-num">{{ $idx + 1 }}</span>
                                </span>
                                <button type="button" class="remove-feature-btn" style="background: none; border: none; color: var(--ff-muted); font-size: 12.5px; font-weight: 600; cursor: pointer; padding: 0;">
                                    Remove
                                </button>
                            </div>
                            <div style="margin-bottom: 12px;">
                                <label style="display: block; font-size: 11.5px; font-weight: 600; color: var(--ff-muted); margin-bottom: 4px;">Title</label>
                                <input type="text" name="features_list_titles[]" value="{{ $feat['title'] ?? '' }}" class="ff-input" style="width: 100%; height: 38px;" required>
                            </div>
                            <div style="margin-bottom: 12px;">
                                <label style="display: block; font-size: 11.5px; font-weight: 600; color: var(--ff-muted); margin-bottom: 4px;">Description</label>
                                <textarea name="features_list_descriptions[]" rows="2" class="ff-input" style="width: 100%; line-height: 1.4;">{{ $feat['description'] ?? '' }}</textarea>
                            </div>
                            <div>
                                <label style="display: block; font-size: 11.5px; font-weight: 600; color: var(--ff-muted); margin-bottom: 4px;">Icon Style</label>
                                <select name="features_list_icons[]" class="ff-select" style="width: 100%; height: 38px;">
                                    <option value="file" {{ ($feat['icon'] ?? '') == 'file' ? 'selected' : '' }}>File Storage</option>
                                    <option value="link" {{ ($feat['icon'] ?? '') == 'link' ? 'selected' : '' }}>Link Vault</option>
                                    <option value="tag" {{ ($feat['icon'] ?? '') == 'tag' ? 'selected' : '' }}>Custom Category</option>
                                    <option value="search" {{ ($feat['icon'] ?? '') == 'search' ? 'selected' : '' }}>Search</option>
                                    <option value="share" {{ ($feat['icon'] ?? '') == 'share' ? 'selected' : '' }}>Sharing</option>
                                    <option value="shield" {{ ($feat['icon'] ?? '') == 'shield' ? 'selected' : '' }}>Security Vault</option>
                                </select>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- PRICING TAB SECTION -->
        <div id="tab-pricing" class="cms-tab-panel" style="display: none;">
            <div class="ff-card" style="padding: 24px; margin-bottom: 24px;">
                <h3 style="margin: 0 0 20px 0; font-size: 16px; font-weight: 700; color: var(--ff-text); border-bottom: 1px solid var(--ff-border); padding-bottom: 12px;">
                    Pricing Plans & Subscription Tiers
                </h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; margin-bottom: 24px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); letter-spacing: 0.5px; margin-bottom: 6px;">Pricing Section Title</label>
                        <input type="text" name="pricing_title" value="{{ $settings['pricing_title'] ?? '' }}" class="ff-input" style="width: 100%;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); letter-spacing: 0.5px; margin-bottom: 6px;">Pricing Section Subtitle</label>
                        <input type="text" name="pricing_subtitle" value="{{ $settings['pricing_subtitle'] ?? '' }}" class="ff-input" style="width: 100%;">
                    </div>
                </div>

                @php
                    $plansList = json_decode($settings['pricing_plans'] ?? '[]', true) ?: [];
                @endphp

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                    @foreach($plansList as $idx => $plan)
                        <div style="background: var(--ff-bg2); border: 1px solid var(--ff-border); border-radius: 14px; padding: 20px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                                <h4 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--ff-text);">{{ $plan['name'] ?? 'Plan' }} Tier</h4>
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--ff-muted); cursor: pointer;">
                                    <input type="radio" name="pricing_plan_popular" value="{{ $idx }}" {{ ($plan['popular'] ?? false) ? 'checked' : '' }} style="accent-color: var(--ff-accent);">
                                    <span>Most Popular</span>
                                </label>
                            </div>
                            <div style="margin-bottom: 12px;">
                                <label style="display: block; font-size: 11.5px; font-weight: 600; color: var(--ff-muted); margin-bottom: 4px;">Plan Name</label>
                                <input type="text" name="pricing_plan_names[]" value="{{ $plan['name'] ?? '' }}" class="ff-input" style="width: 100%; height: 38px;" required>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                                <div>
                                    <label style="display: block; font-size: 11.5px; font-weight: 600; color: var(--ff-muted); margin-bottom: 4px;">Monthly Rate</label>
                                    <input type="text" name="pricing_plan_monthly[]" value="{{ $plan['price_monthly'] ?? '' }}" class="ff-input" style="width: 100%; height: 38px;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 11.5px; font-weight: 600; color: var(--ff-muted); margin-bottom: 4px;">Annual Rate (/mo)</label>
                                    <input type="text" name="pricing_plan_annual[]" value="{{ $plan['price_annual'] ?? '' }}" class="ff-input" style="width: 100%; height: 38px;">
                                </div>
                            </div>
                            <div style="margin-bottom: 12px;">
                                <label style="display: block; font-size: 11.5px; font-weight: 600; color: var(--ff-muted); margin-bottom: 4px;">Tagline</label>
                                <input type="text" name="pricing_plan_taglines[]" value="{{ $plan['tagline'] ?? '' }}" class="ff-input" style="width: 100%; height: 38px;">
                            </div>
                            <div style="margin-bottom: 12px;">
                                <label style="display: block; font-size: 11.5px; font-weight: 600; color: var(--ff-muted); margin-bottom: 4px;">CTA Button Label</label>
                                <input type="text" name="pricing_plan_cta_labels[]" value="{{ $plan['cta_label'] ?? '' }}" class="ff-input" style="width: 100%; height: 38px;">
                            </div>
                            <div>
                                <label style="display: block; font-size: 11.5px; font-weight: 600; color: var(--ff-muted); margin-bottom: 4px;">Perks List (One per line)</label>
                                <textarea name="pricing_plan_perks[]" rows="4" class="ff-input" style="width: 100%; line-height: 1.4; font-size: 12.5px;">{{ implode("\n", $plan['perks'] ?? []) }}</textarea>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- ABOUT TAB SECTION -->
        <div id="tab-about" class="cms-tab-panel" style="display: none;">
            <div class="ff-card" style="padding: 24px; margin-bottom: 24px;">
                <h3 style="margin: 0 0 20px 0; font-size: 16px; font-weight: 700; color: var(--ff-text); border-bottom: 1px solid var(--ff-border); padding-bottom: 12px;">
                    About Story & Founder Profile
                </h3>
                <div style="display: grid; grid-template-columns: 1fr; gap: 18px; margin-bottom: 24px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); letter-spacing: 0.5px; margin-bottom: 6px;">About Section Title</label>
                        <input type="text" name="about_title" value="{{ $settings['about_title'] ?? '' }}" class="ff-input" style="width: 100%;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); letter-spacing: 0.5px; margin-bottom: 6px;">Why We Built FileFusion (Story Paragraph)</label>
                        <textarea name="about_description" rows="4" class="ff-input" style="width: 100%; line-height: 1.5;">{{ $settings['about_description'] ?? '' }}</textarea>
                    </div>
                </div>

                <h4 style="margin: 0 0 16px 0; font-size: 15px; font-weight: 700; color: var(--ff-text); border-top: 1px solid var(--ff-border); padding-top: 16px;">
                    Founder Profile Details
                </h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); letter-spacing: 0.5px; margin-bottom: 6px;">Founder Name</label>
                        <input type="text" name="founder_name" value="{{ $settings['founder_name'] ?? '' }}" class="ff-input" style="width: 100%;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); letter-spacing: 0.5px; margin-bottom: 6px;">Founder Title</label>
                        <input type="text" name="founder_title" value="{{ $settings['founder_title'] ?? '' }}" class="ff-input" style="width: 100%;">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr; gap: 16px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); letter-spacing: 0.5px; margin-bottom: 6px;">Founder Avatar Image URL</label>
                        <input type="text" name="founder_avatar" value="{{ $settings['founder_avatar'] ?? '' }}" class="ff-input" style="width: 100%;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); letter-spacing: 0.5px; margin-bottom: 6px;">Founder Bio</label>
                        <textarea name="founder_bio" rows="3" class="ff-input" style="width: 100%; line-height: 1.4;">{{ $settings['founder_bio'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- CONTACT TAB SECTION -->
        <div id="tab-contact" class="cms-tab-panel" style="display: none;">
            <div class="ff-card" style="padding: 24px; margin-bottom: 24px;">
                <h3 style="margin: 0 0 20px 0; font-size: 16px; font-weight: 700; color: var(--ff-text); border-bottom: 1px solid var(--ff-border); padding-bottom: 12px;">
                    Contact Section Details
                </h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); letter-spacing: 0.5px; margin-bottom: 6px;">Contact Section Title</label>
                        <input type="text" name="contact_title" value="{{ $settings['contact_title'] ?? '' }}" class="ff-input" style="width: 100%;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); letter-spacing: 0.5px; margin-bottom: 6px;">Contact Subtitle</label>
                        <input type="text" name="contact_subtitle" value="{{ $settings['contact_subtitle'] ?? '' }}" class="ff-input" style="width: 100%;">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); letter-spacing: 0.5px; margin-bottom: 6px;">Support Email</label>
                        <input type="email" name="contact_email" value="{{ $settings['contact_email'] ?? '' }}" class="ff-input" style="width: 100%;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); letter-spacing: 0.5px; margin-bottom: 6px;">Support Hours</label>
                        <input type="text" name="contact_hours" value="{{ $settings['contact_hours'] ?? '' }}" class="ff-input" style="width: 100%;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); letter-spacing: 0.5px; margin-bottom: 6px;">Location</label>
                        <input type="text" name="contact_location" value="{{ $settings['contact_location'] ?? '' }}" class="ff-input" style="width: 100%;">
                    </div>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); letter-spacing: 0.5px; margin-bottom: 6px;">Response Time Guarantee Notice</label>
                    <input type="text" name="contact_response_time" value="{{ $settings['contact_response_time'] ?? '' }}" class="ff-input" style="width: 100%;">
                </div>

                <h4 style="margin: 20px 0 16px 0; font-size: 15px; font-weight: 700; color: var(--ff-text); border-top: 1px solid var(--ff-border); padding-top: 16px;">
                    Social Media Links
                </h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); letter-spacing: 0.5px; margin-bottom: 6px;">Social Section Heading</label>
                        <input type="text" name="social_heading" value="{{ $settings['social_heading'] ?? 'Connect With Us' }}" class="ff-input" style="width: 100%;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); letter-spacing: 0.5px; margin-bottom: 6px;">GitHub URL</label>
                        <input type="text" name="social_github" value="{{ $settings['social_github'] ?? 'https://github.com/ashishkumar2310' }}" class="ff-input" style="width: 100%;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); letter-spacing: 0.5px; margin-bottom: 6px;">Twitter / X URL</label>
                        <input type="text" name="social_twitter" value="{{ $settings['social_twitter'] ?? '#' }}" class="ff-input" style="width: 100%;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); letter-spacing: 0.5px; margin-bottom: 6px;">LinkedIn URL</label>
                        <input type="text" name="social_linkedin" value="{{ $settings['social_linkedin'] ?? '#' }}" class="ff-input" style="width: 100%;">
                    </div>
                </div>
            </div>

        </div>

        <!-- CTA TAB SECTION -->
        <div id="tab-cta" class="cms-tab-panel" style="display: none;">
            <div class="ff-card" style="padding: 24px; margin-bottom: 24px;">
                <h3 style="margin: 0 0 20px 0; font-size: 16px; font-weight: 700; color: var(--ff-text); border-bottom: 1px solid var(--ff-border); padding-bottom: 12px;">
                    Bottom Call-to-Action Banner
                </h3>
                <div style="display: grid; grid-template-columns: 1fr; gap: 16px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); letter-spacing: 0.5px; margin-bottom: 6px;">CTA Banner Headline</label>
                        <input type="text" name="cta_title" value="{{ $settings['cta_title'] ?? '' }}" class="ff-input" style="width: 100%;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); letter-spacing: 0.5px; margin-bottom: 6px;">CTA Banner Subtitle</label>
                        <textarea name="cta_description" rows="2" class="ff-input" style="width: 100%; line-height: 1.4;">{{ $settings['cta_description'] ?? '' }}</textarea>
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); letter-spacing: 0.5px; margin-bottom: 6px;">CTA Button Label</label>
                        <input type="text" name="cta_button_text" value="{{ $settings['cta_button_text'] ?? '' }}" class="ff-input" style="width: 100%; max-width: 320px;">
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Action Bar (Static Container - Zero Overlapping) -->
        <div style="position: relative; margin-top: 28px; background: var(--ff-card); border: 1px solid var(--ff-border); border-radius: 12px; padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; box-shadow: 0 4px 16px rgba(0,0,0,0.1);">
            <span style="font-size: 13px; color: var(--ff-muted); display: flex; align-items: center; gap: 6px;">
                <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #10b981;"></span>
                Live Sync: Changes take immediate effect across public website pages.
            </span>
            <button type="submit" class="ff-btn is-primary" style="padding: 10px 24px; font-size: 14px; font-weight: 700; background: #e0392e; border-color: #e0392e; min-width: 220px; justify-content: center;">
                Save Landing Page Settings
            </button>
        </div>
    </form>
</div>

@endsection

@section('push-script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tab switching logic
        const tabBtns = document.querySelectorAll('.cms-tab-btn');
        const tabPanels = document.querySelectorAll('.cms-tab-panel');

        function updateTabStyles() {
            tabBtns.forEach(btn => {
                const isActive = btn.classList.contains('active');
                if (isActive) {
                    btn.style.background = '#e0392e';
                    btn.style.color = '#ffffff';
                    btn.style.borderColor = '#e0392e';
                } else {
                    btn.style.background = 'var(--ff-bg2)';
                    btn.style.color = 'var(--ff-text)';
                    btn.style.borderColor = 'var(--ff-border)';
                }
            });
        }

        updateTabStyles();

        tabBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                
                tabBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                updateTabStyles();

                tabPanels.forEach(panel => {
                    if (panel.id === targetId) {
                        panel.style.display = 'block';
                    } else {
                        panel.style.display = 'none';
                    }
                });
            });
        });

        // Dynamic Feature row adder & remover
        const addFeatureBtn = document.getElementById('add-feature-btn');
        const featuresContainer = document.getElementById('features-container');

        if (addFeatureBtn && featuresContainer) {
            addFeatureBtn.addEventListener('click', function() {
                const count = featuresContainer.querySelectorAll('.feature-row').length + 1;
                const wrapper = document.createElement('div');
                wrapper.className = 'feature-row';
                wrapper.style.cssText = 'background: var(--ff-bg2); border: 1px solid var(--ff-border); border-radius: 12px; padding: 18px; position: relative;';
                wrapper.innerHTML = `
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                        <span style="font-size: 12px; font-weight: 700; color: #6366f1; background: rgba(99,102,241,0.12); padding: 4px 10px; border-radius: 6px;">
                            Feature #<span class="feat-num">${count}</span>
                        </span>
                        <button type="button" class="remove-feature-btn" style="background: none; border: none; color: #ef4444; font-size: 12.5px; font-weight: 600; cursor: pointer; padding: 0;">
                            🗑️ Remove
                        </button>
                    </div>
                    <div style="margin-bottom: 12px;">
                        <label style="display: block; font-size: 11.5px; font-weight: 600; color: var(--ff-muted); margin-bottom: 4px;">Title</label>
                        <input type="text" name="features_list_titles[]" class="ff-input" style="width: 100%; height: 38px;" required>
                    </div>
                    <div style="margin-bottom: 12px;">
                        <label style="display: block; font-size: 11.5px; font-weight: 600; color: var(--ff-muted); margin-bottom: 4px;">Description</label>
                        <textarea name="features_list_descriptions[]" rows="2" class="ff-input" style="width: 100%; line-height: 1.4;"></textarea>
                    </div>
                    <div>
                        <label style="display: block; font-size: 11.5px; font-weight: 600; color: var(--ff-muted); margin-bottom: 4px;">Icon Style</label>
                        <select name="features_list_icons[]" class="ff-select" style="width: 100%; height: 38px;">
                            <option value="file">📄 File Storage</option>
                            <option value="link">🔗 Link Vault</option>
                            <option value="tag">🏷️ Custom Category</option>
                            <option value="search">🔍 Search</option>
                            <option value="share">📤 Sharing</option>
                            <option value="shield">🔒 Security Vault</option>
                        </select>
                    </div>
                `;
                featuresContainer.appendChild(wrapper);
            });

            document.addEventListener('click', function(e) {
                if (e.target && e.target.classList.contains('remove-feature-btn')) {
                    const row = e.target.closest('.feature-row');
                    if (row) {
                        row.remove();
                        featuresContainer.querySelectorAll('.feat-num').forEach((numSpan, idx) => {
                            numSpan.textContent = idx + 1;
                        });
                    }
                }
            });
        }
    });
</script>
@endsection

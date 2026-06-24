@extends('layouts.web')

@section('content')
      <!-- Hero Section -->
      <section class="flex flex-col lg:flex-row items-center justify-between w-full py-2xl gap-2xl fade-in-up">
        <div class="flex-1 flex flex-col items-start text-left max-w-2xl">
          <div class="inline-flex items-center gap-xs px-md py-xs rounded-full glass-panel mb-lg">
            <span class="material-symbols-outlined text-secondary text-[16px]">favorite</span>
            <span class="font-label-sm text-label-sm text-on-surface">Your Kind of Love</span>
          </div>
          <h1 class="font-display-lg-mobile lg:font-display-lg text-display-lg-mobile lg:text-[64px] leading-[1.1] text-on-surface mb-lg">
            Dating designed for <span class="neon-text-gradient block mt-2">Authenticity.</span>
          </h1>
          <p class="font-body-lg text-body-lg text-on-surface-variant mb-xl max-w-xl">
            Swipe globally, match locally. Experience deep compatibility matching, verified profiles, and unparalleled privacy controls designed to let you be your true self.
          </p>
          <div class="flex flex-col sm:flex-row gap-md w-full sm:w-auto">
            <button class="bg-on-surface text-background font-headline-md text-headline-md px-xl py-md rounded-full font-bold shadow-lg hover:bg-white active:scale-95 transform transition-all duration-300 flex items-center justify-center gap-2">
              <img src="https://upload.wikimedia.org/wikipedia/commons/3/31/Apple_logo_white.svg" alt="iOS" class="w-5 h-5 invert" />
              App Store
            </button>
            <button class="glass-panel text-on-surface font-headline-md text-headline-md px-xl py-md rounded-full font-semibold hover:bg-white/10 active:scale-95 transform transition-all duration-300 flex items-center justify-center gap-2">
              <span class="material-symbols-outlined">android</span>
              Google Play
            </button>
          </div>
        </div>

        <!-- Phone Mockup Grid -->
        <div class="flex-1 relative w-full h-[600px] hidden lg:block">
          <div class="absolute top-10 right-20 w-[300px] h-[600px] glass-panel-active rounded-[40px] border-4 border-white/20 overflow-hidden shadow-2xl transform rotate-6 hover:rotate-0 transition-all duration-500 z-20">
            <img class="w-full h-full object-cover" src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=600&q=80" alt="App UI" />
            <div class="absolute bottom-0 left-0 w-full p-lg bg-gradient-to-t from-background via-background/80 to-transparent">
              <h3 class="text-white text-2xl font-bold font-headline-lg">Sam, 26</h3>
              <p class="text-white/80 flex items-center gap-1 text-sm mt-1"><span class="material-symbols-outlined text-[16px]">location_on</span> New York, 5 miles away</p>
              <div class="flex gap-2 mt-4">
                <span class="bg-white/20 px-3 py-1 rounded-full text-xs text-white">Coffee</span>
                <span class="bg-white/20 px-3 py-1 rounded-full text-xs text-white">Travel</span>
                <span class="bg-white/20 px-3 py-1 rounded-full text-xs text-white">Art</span>
              </div>
            </div>
          </div>
          
          <div class="absolute top-40 right-60 w-[250px] h-[500px] glass-panel-active rounded-[36px] border-4 border-white/10 overflow-hidden shadow-2xl transform -rotate-12 opacity-80 z-10 blur-[2px]">
            <img class="w-full h-full object-cover" src="https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=500&q=80" alt="App UI Background" />
          </div>
        </div>
      </section>

      <!-- Features Grid -->
      <section id="features" class="w-full py-2xl fade-in-up delay-200">
        <div class="text-center mb-xl">
          <h2 class="font-headline-lg-mobile lg:font-headline-lg text-headline-lg-mobile lg:text-[40px] text-on-surface mb-sm">Connect with Intent</h2>
          <p class="font-body-md text-body-md text-on-surface-variant max-w-2xl mx-auto">Our features are meticulously crafted to help you find meaningful relationships without the noise.</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-lg">
          <!-- Feature: Rich Profiles -->
          <div class="glass-panel-active rounded-2xl p-xl flex flex-col items-start gap-md group hover:-translate-y-2 transition-transform duration-300">
            <div class="w-14 h-14 rounded-xl bg-secondary/20 flex items-center justify-center text-secondary mb-2 group-hover:neon-glow-rose transition-all duration-300">
              <span class="material-symbols-outlined text-[28px]">account_box</span>
            </div>
            <h3 class="font-headline-md text-[22px] font-bold text-on-surface">Rich Profiles & Prompts</h3>
            <p class="font-body-md text-on-surface-variant leading-relaxed">Go beyond just photos. Express your personality with deep lifestyle tags, demographic details, and engaging ice-breaker prompts.</p>
          </div>

          <!-- Feature: Verification -->
          <div class="glass-panel-active rounded-2xl p-xl flex flex-col items-start gap-md group hover:-translate-y-2 transition-transform duration-300">
            <div class="w-14 h-14 rounded-xl bg-primary/20 flex items-center justify-center text-primary mb-2">
              <span class="material-symbols-outlined text-[28px]">verified_user</span>
            </div>
            <h3 class="font-headline-md text-[22px] font-bold text-on-surface">Photo Verification</h3>
            <p class="font-body-md text-on-surface-variant leading-relaxed">No more catfishing. Our rigorous AI photo verification system ensures the person you match with is the person in the photos.</p>
          </div>

          <!-- Feature: Compatibility -->
          <div class="glass-panel-active rounded-2xl p-xl flex flex-col items-start gap-md group hover:-translate-y-2 transition-transform duration-300">
            <div class="w-14 h-14 rounded-xl bg-tertiary/20 flex items-center justify-center text-tertiary mb-2">
              <span class="material-symbols-outlined text-[28px]">psychology</span>
            </div>
            <h3 class="font-headline-md text-[22px] font-bold text-on-surface">Smart Compatibility</h3>
            <p class="font-body-md text-on-surface-variant leading-relaxed">We calculate an instant compatibility match percentage based on your shared interests, lifestyle choices, and relationship goals.</p>
          </div>
        </div>
      </section>

      <!-- Premium Banner -->
      <section id="premium" class="w-full py-2xl fade-in-up delay-300">
        <div class="relative w-full rounded-3xl overflow-hidden glass-panel-active border-2 border-tertiary/30">
          <div class="absolute inset-0 bg-gradient-to-br from-[#885500]/20 via-background to-background"></div>
          
          <div class="relative p-xl lg:p-[60px] flex flex-col lg:flex-row items-center gap-xl lg:gap-[80px]">
            <div class="flex-1">
              <div class="inline-flex items-center gap-xs px-md py-xs rounded-full bg-tertiary/20 border border-tertiary/50 mb-lg">
                <span class="material-symbols-outlined text-tertiary text-[16px]">crown</span>
                <span class="font-label-sm text-tertiary font-bold tracking-widest uppercase">IndieDate Premium</span>
              </div>
              <h2 class="font-display-lg text-[36px] lg:text-[48px] leading-tight text-on-surface mb-lg">
                Unlock the ultimate dating experience.
              </h2>
              <ul class="space-y-4 mb-xl">
                <li class="flex items-center gap-3 text-on-surface-variant text-lg">
                  <span class="material-symbols-outlined text-tertiary">check_circle</span>
                  <strong>See Who Likes You</strong> – Match instantly without the guessing game.
                </li>
                <li class="flex items-center gap-3 text-on-surface-variant text-lg">
                  <span class="material-symbols-outlined text-tertiary">public</span>
                  <strong>Global Travel Mode</strong> – Change your location and swipe anywhere on Earth.
                </li>
                <li class="flex items-center gap-3 text-on-surface-variant text-lg">
                  <span class="material-symbols-outlined text-tertiary">bolt</span>
                  <strong>Super Likes & Profile Boosts</strong> – Be seen first and stand out from the crowd.
                </li>
                <li class="flex items-center gap-3 text-on-surface-variant text-lg">
                  <span class="material-symbols-outlined text-tertiary">all_inclusive</span>
                  <strong>Unlimited Swiping & Rewinds</strong> – Never miss a connection.
                </li>
              </ul>
              <button class="bg-tertiary text-[#2a1700] font-headline-md text-headline-md px-xl py-4 rounded-full font-bold shadow-[0_0_20px_rgba(255,185,95,0.4)] hover:bg-white active:scale-95 transform transition-all duration-300">
                Upgrade to Premium
              </button>
            </div>
            
            <div class="flex-1 w-full max-w-[400px]">
              <div class="glass-panel p-lg rounded-2xl border border-tertiary/20 relative">
                <div class="absolute -top-4 -right-4 w-12 h-12 bg-tertiary rounded-full flex items-center justify-center shadow-lg shadow-tertiary/50">
                  <span class="material-symbols-outlined text-[#2a1700]">star</span>
                </div>
                <div class="flex items-center gap-4 border-b border-white/10 pb-4 mb-4">
                  <img src="https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=100&q=80" class="w-16 h-16 rounded-full object-cover border-2 border-tertiary" />
                  <div>
                    <h4 class="text-white font-bold text-lg">Jordan liked you!</h4>
                    <p class="text-tertiary text-sm">Match now?</p>
                  </div>
                </div>
                <div class="flex gap-4">
                  <button class="flex-1 bg-white/10 text-white py-3 rounded-xl font-bold">Pass</button>
                  <button class="flex-1 bg-tertiary text-[#2a1700] py-3 rounded-xl font-bold">Match</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Privacy Section -->
      <section id="privacy" class="w-full py-2xl fade-in-up delay-400">
        <div class="flex flex-col-reverse lg:flex-row items-center gap-xl lg:gap-[80px]">
          <div class="flex-1 w-full max-w-[500px]">
             <div class="glass-panel p-xl rounded-[32px] border border-white/10 flex flex-col items-center justify-center text-center">
                <div class="w-24 h-24 bg-surface rounded-full flex items-center justify-center border-4 border-primary/30 mb-6 shadow-xl shadow-primary/20">
                  <span class="text-3xl font-display-lg text-primary">J.D.</span>
                </div>
                <h4 class="text-white font-bold text-2xl mb-2">Mask Name Active</h4>
                <p class="text-on-surface-variant">Only your initials are shown to others until you match.</p>
             </div>
          </div>
          
          <div class="flex-1 flex flex-col items-start">
            <h2 class="font-display-lg text-[40px] leading-tight text-on-surface mb-md">
              Your Privacy, <br /><span class="text-primary">Your Control.</span>
            </h2>
            <p class="font-body-lg text-on-surface-variant mb-lg leading-relaxed">
              We believe in safe, comfortable dating. With the new <strong>Mask Name</strong> feature, you can choose to show only your initials while swiping. Your full name is only revealed to the people you mutually match and connect with.
            </p>
            <div class="flex flex-col gap-4 w-full">
              <div class="flex items-center gap-4 glass-panel px-lg py-4 rounded-xl">
                <span class="material-symbols-outlined text-primary text-[24px]">visibility_off</span>
                <span class="text-on-surface font-semibold">Incognito browsing mode</span>
              </div>
              <div class="flex items-center gap-4 glass-panel px-lg py-4 rounded-xl">
                <span class="material-symbols-outlined text-primary text-[24px]">do_not_disturb_on</span>
                <span class="text-on-surface font-semibold">Block contacts instantly</span>
              </div>
            </div>
          </div>
        </div>
      </section>
@endsection
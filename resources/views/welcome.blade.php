@extends('layouts.web')

@section('content')
      <!-- Hero Section -->
      <section class="flex flex-col lg:flex-row items-center justify-between w-full py-2xl gap-2xl reveal">
        <div class="flex-1 flex flex-col items-start text-left max-w-2xl relative">
          <!-- Decorative floating elements -->
          <div class="absolute -top-10 -left-10 w-20 h-20 bg-secondary/20 rounded-full blur-2xl animate-pulse"></div>
          <div class="absolute top-40 -right-10 w-32 h-32 bg-primary/20 rounded-full blur-3xl animate-pulse delay-700"></div>
          <div class="absolute -bottom-10 left-20 w-24 h-24 bg-tertiary/20 rounded-full blur-2xl animate-pulse delay-300"></div>

          <div class="inline-flex items-center gap-xs px-md py-xs rounded-full glass-panel mb-lg relative z-10 border border-white/20 shadow-[0_0_15px_rgba(255,255,255,0.1)]">
            <span class="material-symbols-outlined text-secondary text-[16px] animate-bounce">favorite</span>
            <span class="font-label-sm text-label-sm text-white font-bold tracking-wide uppercase">Your Kind of Love</span>
          </div>
          <h1 class="font-display-lg-mobile lg:font-display-lg text-display-lg-mobile lg:text-[72px] leading-[1.05] text-white mb-lg relative z-10 drop-shadow-2xl">
            Dating designed for <span class="neon-text-gradient block mt-2 drop-shadow-[0_0_25px_rgba(255,178,185,0.6)]">Authenticity.</span>
          </h1>
          <p class="font-body-lg text-body-lg text-on-surface-variant mb-xl max-w-xl relative z-10 text-lg leading-relaxed">
            Swipe globally, match locally. Experience deep compatibility matching, verified profiles, and unparalleled privacy controls designed to let you be your true self.
          </p>
          <div class="flex flex-col sm:flex-row gap-md w-full sm:w-auto relative z-10">
            <button class="group relative overflow-hidden bg-white text-background font-headline-md text-headline-md px-8 py-4 rounded-full font-bold shadow-[0_0_20px_rgba(255,255,255,0.3)] hover:shadow-[0_0_30px_rgba(255,255,255,0.5)] active:scale-95 transform transition-all duration-300 flex items-center justify-center gap-3">
              <span class="absolute inset-0 bg-gradient-to-r from-white to-gray-200 opacity-0 group-hover:opacity-100 transition-opacity"></span>
              <img src="https://upload.wikimedia.org/wikipedia/commons/3/31/Apple_logo_white.svg" alt="iOS" class="w-6 h-6 invert relative z-10" />
              <span class="relative z-10">App Store</span>
            </button>
            <button class="glass-panel-active text-white font-headline-md text-headline-md px-8 py-4 rounded-full font-semibold hover:bg-white/20 active:scale-95 transform transition-all duration-300 flex items-center justify-center gap-3 border border-white/30">
              <span class="material-symbols-outlined text-[24px]">android</span>
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
      <section id="features" class="w-full py-2xl reveal delay-200">
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



      <!-- Privacy Section -->
      <section id="privacy" class="w-full py-2xl reveal delay-400">
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
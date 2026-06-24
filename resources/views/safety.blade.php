@extends('layouts.web')

@section('title', 'Safety Tips | IndieDate')

@section('content')
<section class="w-full max-w-4xl mx-auto py-12 reveal">
    <div class="glass-panel-active rounded-[32px] p-xl lg:p-[60px] border border-white/10">
        <div class="inline-flex items-center gap-xs px-md py-xs rounded-full bg-primary/20 border border-primary/50 mb-lg">
            <span class="material-symbols-outlined text-primary text-[16px]">health_and_safety</span>
            <span class="font-label-sm text-primary font-bold tracking-widest uppercase">Trust & Safety</span>
        </div>
        <h1 class="font-display-lg text-4xl lg:text-5xl text-on-surface mb-6">Dating Safely on IndieDate</h1>
        
        <div class="prose prose-invert prose-lg max-w-none text-on-surface-variant font-body-md leading-relaxed space-y-6">
            <p>Meeting new people is exciting, but your safety should always come first. Whether you are chatting in the app or meeting up in person, keep these core guidelines in mind.</p>
            
            <h3 class="text-xl text-on-surface font-headline-md mt-6 mb-2">1. Keep Conversations on the App</h3>
            <p>Don't be in a rush to give out your personal phone number, WhatsApp, or email. Get to know people safely through IndieDate's secure messaging system first.</p>

            <h3 class="text-xl text-on-surface font-headline-md mt-6 mb-2">2. Never Send Money or Financial Information</h3>
            <p>Never send money, wire funds, or share your bank account details with anyone you meet online, even if they claim it's an emergency.</p>

            <h3 class="text-xl text-on-surface font-headline-md mt-6 mb-2">3. Meet in Public and Stay in Public</h3>
            <p>For your first few dates, meet in a populated, public place like a coffee shop, restaurant, or museum. Never agree to meet at someone's home, your home, or an isolated location on an early date.</p>

            <h3 class="text-xl text-on-surface font-headline-md mt-6 mb-2">4. Tell a Friend or Family Member</h3>
            <p>Always let someone know who you are meeting, where you are going, and when you expect to return. Make sure your phone is fully charged.</p>

            <h3 class="text-xl text-on-surface font-headline-md mt-6 mb-2">5. Trust Your Instincts</h3>
            <p>If something feels off, trust your gut. You can block and report any user instantly within the app. Our Trust & Safety team reviews all reports seriously.</p>
        </div>
    </div>
</section>
@endsection

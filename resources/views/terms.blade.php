@extends('layouts.web')

@section('title', 'Terms of Service | IndieDate')

@section('content')
<section class="w-full max-w-4xl mx-auto py-12 reveal">
    <div class="glass-panel-active rounded-[32px] p-xl lg:p-[60px] border border-white/10">
        <h1 class="font-display-lg text-4xl lg:text-5xl text-on-surface mb-6">Terms of Service</h1>
        <p class="text-on-surface-variant font-label-md mb-8">Last Updated: {{ date('F j, Y') }}</p>
        
        <div class="prose prose-invert prose-lg max-w-none text-on-surface-variant font-body-md leading-relaxed space-y-6">
            <h3 class="text-xl text-on-surface font-headline-md mt-6 mb-2">1. Acceptance of Terms</h3>
            <p>By accessing or using the IndieDate application or website, you agree to be bound by these Terms of Service. If you disagree with any part of the terms, you may not access the service.</p>
            
            <h3 class="text-xl text-on-surface font-headline-md mt-6 mb-2">2. Eligibility</h3>
            <p>You must be at least 18 years old to create an account on IndieDate. By creating an account, you represent and warrant that you meet this age requirement.</p>

            <h3 class="text-xl text-on-surface font-headline-md mt-6 mb-2">3. User Conduct</h3>
            <p>You agree not to use the service to post explicit content, harass other users, or engage in fraudulent activities. We reserve the right to ban or suspend any account that violates these guidelines.</p>

            <h3 class="text-xl text-on-surface font-headline-md mt-6 mb-2">4. Premium Subscriptions</h3>
            <p>IndieDate offers premium subscription tiers. Subscriptions automatically renew unless canceled at least 24 hours before the end of the current period through your respective App Store settings.</p>

            <h3 class="text-xl text-on-surface font-headline-md mt-6 mb-2">5. Modifications</h3>
            <p>We reserve the right to modify these terms at any time. We will notify users of any significant changes via email or in-app notification.</p>
        </div>
    </div>
</section>
@endsection

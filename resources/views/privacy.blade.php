@extends('layouts.web')

@section('title', 'Privacy Policy | IndieDate')

@section('content')
<section class="w-full max-w-4xl mx-auto py-12 fade-in-up">
    <div class="glass-panel-active rounded-[32px] p-xl lg:p-[60px] border border-white/10">
        <h1 class="font-display-lg text-4xl lg:text-5xl text-on-surface mb-6">Privacy Policy</h1>
        <p class="text-on-surface-variant font-label-md mb-8">Last Updated: {{ date('F j, Y') }}</p>
        
        <div class="prose prose-invert prose-lg max-w-none text-on-surface-variant font-body-md leading-relaxed space-y-6">
            <h3 class="text-xl text-on-surface font-headline-md mt-6 mb-2">1. Introduction</h3>
            <p>At IndieDate, your privacy is our priority. We are committed to protecting your personal data and ensuring you have control over your information.</p>
            
            <h3 class="text-xl text-on-surface font-headline-md mt-6 mb-2">2. Data We Collect</h3>
            <p>We collect information you provide directly to us when creating a profile, including photos, demographics, interests, and location data (if permitted). We use this data strictly for matchmaking purposes.</p>

            <h3 class="text-xl text-on-surface font-headline-md mt-6 mb-2">3. Mask Name & Privacy Controls</h3>
            <p>We offer advanced privacy features such as <strong>Mask Name</strong>, which allows you to hide your full name and display only your initials to users you haven't matched with. You can toggle this feature at any time in your app settings.</p>

            <h3 class="text-xl text-on-surface font-headline-md mt-6 mb-2">4. Data Sharing</h3>
            <p>We do not sell your personal data to third parties. Data is only shared with trusted service providers who assist us in operating the platform securely.</p>

            <h3 class="text-xl text-on-surface font-headline-md mt-6 mb-2">5. Contact Us</h3>
            <p>If you have any questions about this Privacy Policy, please contact us at <a href="mailto:indiedate.in@gmail.com" class="text-primary hover:underline">indiedate.in@gmail.com</a>.</p>
        </div>
    </div>
</section>
@endsection

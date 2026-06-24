@extends('layouts.web')

@section('title', 'About Us | IndieDate')

@section('content')
<section class="w-full max-w-4xl mx-auto py-12 fade-in-up">
    <div class="glass-panel-active rounded-[32px] p-xl lg:p-[60px] border border-white/10">
        <h1 class="font-display-lg text-4xl lg:text-5xl text-on-surface mb-6 text-center">About <span class="neon-text-gradient">IndieDate</span></h1>
        
        <div class="prose prose-invert prose-lg max-w-none text-on-surface-variant font-body-lg leading-relaxed space-y-6">
            <p>
                Welcome to IndieDate, the dating platform built for authenticity, safety, and deep compatibility. We believe that finding your kind of love shouldn't feel like a full-time job. It should be exciting, secure, and uniquely tailored to your personality.
            </p>
            <h3 class="text-2xl text-on-surface font-headline-md mt-8 mb-4">Our Mission</h3>
            <p>
                Our mission is simple: to create meaningful connections in a digital world. We prioritize rich profiles, verified users, and smart algorithms that look beyond just photos. 
            </p>
            <h3 class="text-2xl text-on-surface font-headline-md mt-8 mb-4">Built for You</h3>
            <p>
                With features like <strong>Global Travel Mode</strong>, <strong>Mask Name</strong> privacy controls, and <strong>Compatibility Match Scoring</strong>, IndieDate puts you in the driver's seat of your dating life.
            </p>
            <p class="text-center mt-12">
                <a href="{{ url('/') }}" class="inline-block bg-primary text-on-primary font-bold px-8 py-3 rounded-full hover:bg-white transition-colors duration-300">Start Your Journey</a>
            </p>
        </div>
    </div>
</section>
@endsection

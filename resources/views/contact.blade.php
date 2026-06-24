@extends('layouts.web')

@section('title', 'Contact Us | IndieDate')

@section('content')
<section class="w-full max-w-4xl mx-auto py-12 fade-in-up">
    <div class="glass-panel-active rounded-[32px] p-xl lg:p-[60px] border border-white/10 flex flex-col md:flex-row gap-12">
        <div class="flex-1">
            <h1 class="font-display-lg text-4xl lg:text-5xl text-on-surface mb-6">Contact Us</h1>
            <p class="text-on-surface-variant font-body-md leading-relaxed mb-8">
                Have a question, need support, or want to share feedback? We'd love to hear from you. Fill out the form or email us directly.
            </p>
            <div class="flex items-center gap-4 mb-4 text-on-surface">
                <span class="material-symbols-outlined text-primary text-[24px]">email</span>
                <span class="font-bold">indiedate.in@gmail.com</span>
            </div>
            <div class="flex items-center gap-4 text-on-surface">
                <span class="material-symbols-outlined text-primary text-[24px]">headset_mic</span>
                <span>We typically respond within 24-48 hours.</span>
            </div>
        </div>
        
        <div class="flex-1">
            <form class="flex flex-col gap-4 bg-background/30 p-6 rounded-2xl border border-white/5" onsubmit="event.preventDefault(); alert('Message sent successfully! (This is a demo frontend form)');">
                <div>
                    <label class="block text-on-surface-variant font-label-md mb-2">Name</label>
                    <input type="text" class="w-full bg-surface-container-highest border border-outline-variant rounded-lg px-4 py-3 text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors" placeholder="Your Name" required />
                </div>
                <div>
                    <label class="block text-on-surface-variant font-label-md mb-2">Email</label>
                    <input type="email" class="w-full bg-surface-container-highest border border-outline-variant rounded-lg px-4 py-3 text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors" placeholder="you@example.com" required />
                </div>
                <div>
                    <label class="block text-on-surface-variant font-label-md mb-2">Message</label>
                    <textarea rows="4" class="w-full bg-surface-container-highest border border-outline-variant rounded-lg px-4 py-3 text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors" placeholder="How can we help?" required></textarea>
                </div>
                <button type="submit" class="w-full bg-primary text-on-primary font-bold px-8 py-3 rounded-full hover:bg-white transition-colors duration-300 mt-2">
                    Send Message
                </button>
            </form>
        </div>
    </div>
</section>
@endsection

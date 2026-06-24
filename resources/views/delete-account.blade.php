@extends('layouts.web')

@section('title', 'Delete Account | IndieDate')

@section('content')
<section class="w-full max-w-4xl mx-auto py-12 reveal">
    <div class="glass-panel-active rounded-[32px] p-xl lg:p-[60px] border border-error/20 flex flex-col md:flex-row gap-12">
        <div class="flex-1">
            <div class="inline-flex items-center gap-xs px-md py-xs rounded-full bg-error/20 border border-error/50 mb-lg">
                <span class="material-symbols-outlined text-error text-[16px]">warning</span>
                <span class="font-label-sm text-error font-bold tracking-widest uppercase">Data Privacy</span>
            </div>
            <h1 class="font-display-lg text-4xl lg:text-5xl text-on-surface mb-6">Request Account Deletion</h1>
            <p class="text-on-surface-variant font-body-md leading-relaxed mb-8">
                If you wish to permanently delete your IndieDate account and all associated data, please fill out this form. We will process your request within 72 hours.
            </p>
            <div class="glass-panel p-4 rounded-xl border border-white/5 mb-4">
                <p class="text-sm text-on-surface-variant">
                    <strong class="text-on-surface">Please note:</strong> Deleting your account will permanently remove your profile, photos, matches, and messages. This action cannot be undone.
                </p>
            </div>
            <div class="flex items-center gap-4 mt-8 text-on-surface">
                <span class="material-symbols-outlined text-primary text-[24px]">email</span>
                <span class="font-bold">indiedate.in@gmail.com</span>
            </div>
        </div>
        
        <div class="flex-1">
            <form class="flex flex-col gap-4 bg-background/30 p-6 rounded-2xl border border-white/5" onsubmit="event.preventDefault(); alert('Deletion request submitted successfully. We will contact you shortly.');">
                <div>
                    <label class="block text-on-surface-variant font-label-md mb-2">Registered Email Address</label>
                    <input type="email" class="w-full bg-surface-container-highest border border-outline-variant rounded-lg px-4 py-3 text-on-surface focus:outline-none focus:border-error focus:ring-1 focus:ring-error transition-colors" placeholder="Email associated with your account" required />
                </div>
                <div>
                    <label class="block text-on-surface-variant font-label-md mb-2">Phone Number (Optional)</label>
                    <input type="tel" class="w-full bg-surface-container-highest border border-outline-variant rounded-lg px-4 py-3 text-on-surface focus:outline-none focus:border-error focus:ring-1 focus:ring-error transition-colors" placeholder="+1 (555) 000-0000" />
                </div>
                <div>
                    <label class="block text-on-surface-variant font-label-md mb-2">Reason for leaving</label>
                    <textarea rows="3" class="w-full bg-surface-container-highest border border-outline-variant rounded-lg px-4 py-3 text-on-surface focus:outline-none focus:border-error focus:ring-1 focus:ring-error transition-colors" placeholder="Optional feedback..." ></textarea>
                </div>
                <div class="flex items-start gap-2 mt-2">
                    <input type="checkbox" id="confirm" class="mt-1" required />
                    <label for="confirm" class="text-xs text-on-surface-variant">I understand that this will permanently delete my account and all associated data.</label>
                </div>
                <button type="submit" class="w-full bg-error text-on-error-container font-bold px-8 py-3 rounded-full hover:opacity-90 transition-opacity duration-300 mt-4 shadow-lg shadow-error/20">
                    Submit Deletion Request
                </button>
            </form>
        </div>
    </div>
</section>
@endsection

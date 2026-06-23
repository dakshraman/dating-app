<x-mail::message>
# Password Reset

You requested a password reset. Use the code below to reset your password:

<x-mail::panel>
**{{ $otp }}**
</x-mail::panel>

This code will expire in **10 minutes**.

If you didn't request this, you can safely ignore this email.

Stay awesome,<br>
**The {{ config('app.name') }} Team**
</x-mail::message>

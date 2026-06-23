<x-mail::message>
# Welcome to IndieDate, {{ $name }}! 🎉

We are thrilled to have you here. IndieDate is the fresh, dynamic way to discover amazing people and build genuine connections.

Your profile is now active! Here are a few quick tips to get started:

- **Complete your profile**: Add your best photos and fill out your bio to stand out.
- **Set your preferences**: Let us know exactly who you're looking for.
- **Start exploring**: Swipe, match, and start meaningful conversations.

<x-mail::button :url="config('app.url')">
Explore Matches Now
</x-mail::button>

If you need any help, our support team is always here for you.

Stay awesome,<br>
**The {{ config('app.name') }} Team**
</x-mail::message>

# React Native App — Backend Integration Guide

## API Base URL

```
http://localhost/api
```

For real devices, replace `localhost` with your machine's local IP (e.g. `http://192.168.x.x:8000/api`).

## Auth (Sanctum Token)

All authenticated endpoints require a Bearer token header:

```
Authorization: Bearer <token>
```

**Endpoints:**

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| POST | `/register` | No | Create account |
| POST | `/login` | No | Login |
| POST | `/auth/google/mobile` | No | Google OAuth (mobile) |
| POST | `/logout` | Yes | Revoke token |
| GET | `/user` | Yes | Get authenticated user |

### Register
```json
// POST /api/register
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "1234567890",
  "gender": "male",
  "birth_date": "1995-01-01"
}
// Response 201
{
  "user": { "id": 1, "name": "...", "email": "..." },
  "token": "1|abc123..."
}
```

### Login
```json
// POST /api/login
{ "email": "john@example.com", "password": "password123" }
// Response 200
{ "user": { ... }, "token": "1|abc123..." }
```

### Google OAuth (Mobile)
Pass the ID token from Google Sign-In on the client:
```json
// POST /api/auth/google/mobile
{
  "id_token": "...",
  "email": "user@gmail.com",
  "name": "John",
  "avatar": "https://..."
}
// Response 200 — same shape as login
```

### Get Current User (from token)
```json
// GET /api/user
// Response 200
{
  "id": 1,
  "name": "John",
  "email": "john@example.com",
  "bio": "...",
  "gender": "male",
  "birth_date": "1995-01-01",
  "location": "New York",
  "latitude": 40.7128,
  "longitude": -74.0060,
  "profile_photo": "https://...",
  "is_verified": false,
  "is_banned": false,
  "verification_photo": null,
  "last_seen_at": "2026-06-13T...",
  "phone": "1234567890",
  "fcm_tokens": ["token1", "token2"],
  "photos": [ ... ],
  "preferences": { "gender_preference": "female", "min_age": 20, "max_age": 40, "max_distance": 100 },
  "interests": [ { "id": 1, "name": "music" } ],
  "prompts": [ { "id": 1, "prompt": "My ideal weekend", "answer": "Hiking and coffee", "order": 0 } ]
}
```

## Profile

| Method | Path | Description |
|--------|------|-------------|
| PUT | `/api/profile` | Update name, bio, gender, birth_date, location, lat/lng, profile_photo |
| PUT | `/api/profile/preferences` | Update dating preferences |
| POST | `/api/profile/photos` | Add a photo (URL string) |
| DELETE | `/api/profile/photos/{id}` | Remove a photo |
| PUT | `/api/profile/interests` | Replace interests (array of strings) |
| PUT | `/api/profile/prompts` | Replace prompts (max 3) |
| GET | `/api/profile/visitors` | Who viewed your profile |
| GET | `/api/profiles/{id}` | View another user's profile |
| DELETE | `/api/account` | Delete your account permanently |

### Update Profile
```json
// PUT /api/profile
{
  "name": "Updated Name",
  "bio": "New bio here",
  "location": "New York",
  "latitude": 40.7128,
  "longitude": -74.0060,
  "profile_photo": "https://example.com/photo.jpg"
}
// Response 200 — returns full user object (with photos, preferences, interests, prompts)
```

### Preferences
```json
// PUT /api/profile/preferences
{
  "gender_preference": "female",
  "min_age": 20,
  "max_age": 40,
  "max_distance": 100
}
```

### Add Photo
```json
// POST /api/profile/photos
{ "photo_url": "https://...", "is_primary": true }
// Response 201
```

### Update Interests
```json
// PUT /api/profile/interests
{ "interests": ["music", "travel", "photography"] }
```

### Update Prompts
```json
// PUT /api/profile/prompts
{
  "prompts": [
    { "prompt": "My ideal weekend", "answer": "Hiking and coffee" },
    { "prompt": "Two truths and a lie", "answer": "I speak 3 languages" },
    { "prompt": "Best travel story", "answer": "Got lost in Tokyo" }
  ]
}
// Max 3 prompts
```

### Profile Visitors
```json
// GET /api/profile/visitors
// Response 200 — array
[
  { "id": 2, "name": "Jane", "profile_photo": "https://...", "bio": "...", "age": 28, "visited_at": "2026-06-13T..." }
]
```

### View Profile
```json
// GET /api/profiles/{id}
// Response 200
{
  "id": 2,
  "name": "Jane",
  "age": 28,
  "bio": "...",
  "location": "Brooklyn",
  "profile_photo": "https://...",
  "is_verified": false,
  "photos": [ ... ],
  "interests": [ ... ],
  "prompts": [ ... ],
  "compatibility": 50
}
```

### Delete Account
```json
// DELETE /api/account
// Response 200 — user and all data permanently deleted
```

## Discover Feed

```json
// GET /api/discover?cursor=eyJpZCI6MTB9
// Response 200
{
  "data": [
    {
      "id": 2,
      "name": "Jane",
      "age": 28,
      "bio": "...",
      "location": "Brooklyn",
      "profile_photo": "https://...",
      "is_verified": false,
      "photos": [ ... ],
      "interests": [ ... ],
      "prompts": [ ... ],
      "compatibility": 50
    }
  ],
  "next_cursor": "eyJpZCI6MTB9",
  "has_more": true
}
```

Cursor pagination — pass `next_cursor` value as `?cursor=` param for next page. 20 profiles per page. Boosted profiles appear first.

## Swipe & Match

| Method | Path | Description |
|--------|------|-------------|
| POST | `/api/swipe` | Swipe like/nope/super like |
| POST | `/api/swipe/undo` | Undo last swipe |
| GET | `/api/matches` | Get your matches |
| DELETE | `/api/matches/{id}` | Unmatch |
| GET | `/api/likes-received` | Who liked you |
| GET | `/api/likes/me` | Alias for likes-received |
| GET | `/api/swipe/remaining` | Swipe status |

### Swipe
```json
// POST /api/swipe
{ "swiped_id": 2, "direction": "like" }
// or super like:
{ "swiped_id": 2, "direction": "like", "is_super_like": true }

// Response 200 — no match
{ "swipe": { "id": 1, "direction": "like", "is_super_like": false }, "matched": false, "match": null }

// Response 200 — mutual match!
{ "swipe": { ... }, "matched": true, "match": { "id": 1, "user1_id": 1, "user2_id": 2, "matched_at": "..." } }
```

### Undo Last Swipe
```json
// POST /api/swipe/undo
// Response 200 — swipe deleted, usage count restored
```

### Matches
```json
// GET /api/matches
// Response 200
[
  {
    "id": 1,
    "matched_at": "2026-06-13T...",
    "user": { "id": 2, "name": "Jane", "profile_photo": "https://..." },
    "conversation_id": 1
  }
]
```

### Likes Received
```json
// GET /api/likes-received
// Response 200
[
  {
    "id": 2,
    "name": "Jane",
    "profile_photo": "https://...",
    "bio": "...",
    "age": 28,
    "is_super_like": false,
    "swiped_at": "2026-06-13T..."
  }
]
```

### Swipe Status
```json
// GET /api/swipe/remaining
// Response 200
{ "remaining_swipes": 999999999, "remaining_super_likes": 999999999, "is_premium": false }
```

All features are free. Boost activation requires a subscription.

## Chat / Messaging

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/conversations` | List conversations |
| GET | `/api/conversations/{id}/messages` | Get messages (paginated) |
| POST | `/api/conversations/{id}/messages` | Send a message |
| POST | `/api/conversations/{id}/read` | Mark messages as read |
| POST | `/api/conversations/{id}/typing` | Send typing indicator |
| POST | `/api/chat/upload` | Upload image/voice file |
| POST | `/api/conversations/{id}/messages/{msg}/react` | React to message |
| DELETE | `/api/conversations/{id}/messages/{msg}` | Delete a message |
| POST | `/api/user/block` | Block a user |
| POST | `/api/user/report` | Report a user |
| POST | `/api/user/last-seen` | Update last seen timestamp |

### Conversations
```json
// GET /api/conversations
// Response 200
[
  {
    "id": 1,
    "match_id": 1,
    "user": { "id": 2, "name": "Jane", "profile_photo": "https://...", "last_seen_at": "...", "is_online": true },
    "last_message": { "id": 5, "content": "Hey!", "type": "text", "created_at": "...", "sender_id": 2, "status": "sent" },
    "unread_count": 2,
    "last_message_at": "2026-06-13T..."
  }
]
```

### Send Message
```json
// POST /api/conversations/1/messages
{ "content": "Hello!", "type": "text", "reply_to_id": null, "metadata": {} }
// Response 201
{
  "id": 6,
  "conversation_id": 1,
  "sender_id": 1,
  "content": "Hello!",
  "type": "text",
  "status": "sent",
  "metadata": null,
  "read_at": null,
  "created_at": "...",
  "reply_to": null,
  "sender": { "id": 1, "name": "John", "profile_photo": "https://..." }
}
```

### Get Messages
```json
// GET /api/conversations/1/messages?before=50&limit=50
// Response 200 — array (not wrapped in data)
[
  {
    "id": 1,
    "conversation_id": 1,
    "sender_id": 2,
    "content": "Hey there!",
    "type": "text",
    "status": "read",
    "metadata": null,
    "read_at": "2026-06-13T...",
    "created_at": "2026-06-13T...",
    "reply_to": null,
    "sender": { "id": 2, "name": "Jane", "profile_photo": "https://..." }
  }
]
```

### Mark as Read
```json
// POST /api/conversations/1/read
// Response 200
{ "message": "Marked as read", "last_read_message_id": 5 }
```

### Typing Indicator
```json
// POST /api/conversations/1/typing
// Response 200 — broadcasts to WebSocket channel
```

### Upload Media
```json
// POST /api/chat/upload (multipart/form-data)
// file: (binary), type: "image" | "voice" | "video"
// Response 201
{ "url": "http://localhost/storage/chat-media/abc123.jpg", "type": "image" }
```

### Block User
```json
// POST /api/user/block
{ "user_id": 2 }
```

### Report User
```json
// POST /api/user/report
{ "user_id": 2, "reason": "Inappropriate behavior" }
```

### Last Seen
```json
// POST /api/user/last-seen
// Response 200
```

### React to Message
```json
// POST /api/conversations/1/messages/5/react
{ "metadata": { "emoji": "❤️" } }
```

### Delete Message
```json
// DELETE /api/conversations/1/messages/5
```

## Real-Time Chat (Laravel Reverb)

Reverb uses the **Pusher protocol**, so use a Pusher-compatible client on React Native.

### Client Setup

```bash
npm install pusher-js
```

### Configuration

```javascript
import Pusher from 'pusher-js/react-native';

const pusher = new Pusher('2gjhksblzm5x0iml1gsj', {
  cluster: '',           // leave empty — using custom host
  wsHost: 'localhost',   // your backend IP in production
  wsPort: 8080,
  wssPort: 8080,         // same as wsPort unless TLS is set up
  forceTLS: false,
  enabledTransports: ['ws', 'wss'],
  disableStats: true,
});
```

### Channel Subscription

Each user subscribes to their **private** channel:

```javascript
const channel = pusher.subscribe(`private-user.${userId}`);

// New message
channel.bind('App\\Events\\MessageSent', (data) => {
  console.log('New message:', data.message);
});

// Message read receipt
channel.bind('App\\Events\\MessageRead', (data) => {
  console.log('Read receipt:', data);
});

// Typing indicator
channel.bind('App\\Events\\TypingIndicator', (data) => {
  console.log(`${data.userName} is typing`);
});

// New match
channel.bind('App\\Events\\NewMatch', (data) => {
  console.log('New match:', data.match);
});

// Message delivered
channel.bind('App\\Events\\MessageDelivered', (data) => {
  console.log('Message delivered:', data);
});
```

> **Authentication**: Private channels require a POST to `/broadcasting/auth` with the Sanctum Bearer token. The `pusher-js` library handles this automatically when you configure the `authEndpoint`:

```javascript
const pusher = new Pusher('2gjhksblzm5x0iml1gsj', {
  authEndpoint: 'http://localhost/api/broadcasting/auth',
  auth: {
    headers: { Authorization: `Bearer ${token}` },
  },
  // ... other options
});
```

## Push Notifications (FCM)

### Register Device Token
```json
// PUT /api/user/fcm-token
{ "fcm_token": "firebase-device-token-here" }
```

### Remove Token
```
// DELETE /api/user/fcm-token/{token}
```

The backend sends FCM notifications for new messages when the recipient is offline. The `fcm_tokens` field on the user model stores an array of tokens.

## Subscriptions & Boosts

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/subscription/plans` | Available plans |
| GET | `/api/subscription/status` | Current subscription |
| POST | `/api/subscription/purchase` | Purchase a plan |
| POST | `/api/profile/boost` | Activate profile boost (requires subscription) |
| POST | `/api/profile/verification-photo` | Upload verification photo |

### Plans
```json
// GET /api/subscription/plans
// Response 200
[
  {
    "id": 1,
    "name": "Premium",
    "slug": "premium",
    "description": null,
    "price": 19.99,
    "duration_days": 30,
    "features": null
  }
]
```

### Purchase
```json
// POST /api/subscription/purchase
{ "plan_id": 1 }
// Response 200
{ "message": "Subscription activated", "subscription": { ... } }
```

### Activate Boost (requires subscription)
```json
// POST /api/profile/boost
// Response 200
{ "message": "Boost activated", "boost": { "is_active": true, "expires_at": "..." } }
// Response 403 without active subscription
{ "message": "Subscription required to boost your profile." }
```

### Upload Verification Photo
```json
// POST /api/profile/verification-photo
{ "photo": "https://..." }
```

## Error Handling

All endpoints return consistent errors:

```json
// Validation error (422)
{ "errors": { "email": ["The email field is required."] } }

// Auth error (401)
{ "message": "Invalid credentials" }

// Not found (404)
{ "message": "No query results for model [User] 1" }

// Banned user (403) — applies to ALL API routes
{ "message": "Your account has been banned.", "reason": "Spam" }
```

### HTTP Status Codes Used

| Code | Meaning |
|------|---------|
| 200 | Success |
| 201 | Created (register, send message, upload) |
| 401 | Unauthenticated |
| 403 | Forbidden (banned, boost requires sub) |
| 404 | Not found |
| 422 | Validation error |
| 429 | Rate limited |

## CORS

The backend is configured to accept requests from any origin (`*`). No additional CORS setup needed on the mobile client.

## Running the Backend Locally

```bash
cd backend
cp .env.example .env      # configure your DB
php artisan migrate
php artisan db:seed --class=SubscriptionPlanSeeder
php artisan serve          # API at http://localhost:8000
php artisan reverb:start   # WebSocket at port 8080
```

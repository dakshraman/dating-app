<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\Interest;
use App\Models\Message;
use App\Models\Swipe;
use App\Models\User;
use App\Models\UserMatch;
use App\Models\UserPhoto;
use App\Models\UserPreference;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $interests = collect(['Travel', 'Music', 'Cooking', 'Fitness', 'Photography', 'Reading', 'Gaming', 'Art', 'Dancing', 'Hiking', 'Movies', 'Yoga'])
            ->map(fn ($name) => Interest::firstOrCreate(['name' => $name]));

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('123456'),
            'gender' => 'male',
            'birth_date' => '1990-01-01',
            'bio' => 'Platform administrator',
            'location' => 'San Francisco, CA',
            'profile_photo' => 'https://i.pravatar.cc/400?u=admin',
            'is_verified' => true,
            'is_active' => true,
        ]);

        $users = collect([
            ['name' => 'Sarah Johnson', 'email' => 'sarah@test.com', 'gender' => 'female', 'birth_date' => '1998-03-15', 'bio' => 'Love traveling and trying new foods! 🌍', 'location' => 'Los Angeles, CA', 'photo' => 'https://i.pravatar.cc/400?u=sarah'],
            ['name' => 'Mike Chen', 'email' => 'mike@test.com', 'gender' => 'male', 'birth_date' => '1996-07-22', 'bio' => 'Photographer & coffee enthusiast 📸', 'location' => 'New York, NY', 'photo' => 'https://i.pravatar.cc/400?u=mike'],
            ['name' => 'Emily Davis', 'email' => 'emily@test.com', 'gender' => 'female', 'birth_date' => '1999-11-08', 'bio' => 'Yoga instructor. Finding my zen 🧘‍♀️', 'location' => 'Austin, TX', 'photo' => 'https://i.pravatar.cc/400?u=emily'],
            ['name' => 'James Wilson', 'email' => 'james@test.com', 'gender' => 'male', 'birth_date' => '1995-05-30', 'bio' => 'Guitarist and outdoor enthusiast 🎸', 'location' => 'Seattle, WA', 'photo' => 'https://i.pravatar.cc/400?u=james'],
            ['name' => 'Sophia Martinez', 'email' => 'sophia@test.com', 'gender' => 'female', 'birth_date' => '1997-09-14', 'bio' => 'Book lover & aspiring chef 📚', 'location' => 'Miami, FL', 'photo' => 'https://i.pravatar.cc/400?u=sophia'],
            ['name' => 'Alex Thompson', 'email' => 'alex@test.com', 'gender' => 'male', 'birth_date' => '1994-12-03', 'bio' => 'Software dev by day, gamer by night 🎮', 'location' => 'San Francisco, CA', 'photo' => 'https://i.pravatar.cc/400?u=alex'],
            ['name' => 'Olivia Brown', 'email' => 'olivia@test.com', 'gender' => 'female', 'birth_date' => '2000-01-20', 'bio' => 'Dance enthusiast & dog mom 🐕', 'location' => 'Chicago, IL', 'photo' => 'https://i.pravatar.cc/400?u=olivia'],
            ['name' => 'Ryan Garcia', 'email' => 'ryan@test.com', 'gender' => 'male', 'birth_date' => '1993-08-17', 'bio' => 'Fitness trainer. Let\'s work out! 💪', 'location' => 'Denver, CO', 'photo' => 'https://i.pravatar.cc/400?u=ryan'],
        ]);

        $createdUsers = $users->map(fn ($data) => User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt('password'),
            'gender' => $data['gender'],
            'birth_date' => $data['birth_date'],
            'bio' => $data['bio'],
            'location' => $data['location'],
            'profile_photo' => $data['photo'],
            'is_verified' => fake()->boolean(70),
            'is_active' => true,
        ]));

        foreach ($createdUsers as $user) {
            $userInterests = $interests->random(rand(3, 6));
            $user->interests()->attach($userInterests->pluck('id'));

            UserPhoto::create([
                'user_id' => $user->id,
                'photo_url' => $user->profile_photo,
                'is_primary' => true,
                'order' => 0,
            ]);

            UserPreference::create([
                'user_id' => $user->id,
                'gender_preference' => $user->gender === 'male' ? 'female' : 'male',
                'min_age' => 20,
                'max_age' => 40,
                'max_distance' => 100,
            ]);
        }

        $allUsers = $createdUsers->push($admin);
        $femaleUsers = $allUsers->where('gender', 'female');
        $maleUsers = $allUsers->where('gender', 'male');

        $matches = collect();
        $matchedPairs = [];

        foreach ($maleUsers as $male) {
            $femalePool = $femaleUsers->where('id', '!=', $male->id);
            $matchCount = rand(1, min(3, $femalePool->count()));

            foreach ($femalePool->random($matchCount) as $female) {
                $pair = [$male->id, $female->id];
                sort($pair);
                $key = implode('-', $pair);

                if (in_array($key, $matchedPairs)) {
                    continue;
                }
                $matchedPairs[] = $key;

                Swipe::create(['swiper_id' => $male->id, 'swiped_id' => $female->id, 'direction' => 'like']);
                Swipe::create(['swiper_id' => $female->id, 'swiped_id' => $male->id, 'direction' => 'like']);

                $match = UserMatch::create([
                    'user1_id' => $pair[0],
                    'user2_id' => $pair[1],
                    'matched_at' => now()->subDays(rand(1, 14)),
                ]);
                $matches->push($match);
            }
        }

        $sampleMessages = [
            'Hey! How are you doing?',
            'I\'m great, thanks for asking!',
            'Love your profile picture 😊',
            'Thanks! Yours is pretty cool too',
            'Would you like to grab coffee sometime?',
            'I\'d love that! How about this weekend?',
            'Sounds perfect! Let\'s meet at Starbucks',
            'Can\'t wait! See you there 👋',
            'What kind of music do you like?',
            'I\'m into indie rock and jazz',
            'No way! Me too! What\'s your favorite band?',
            'That\'s awesome! We should jam together',
            'You have such a great smile',
            'Stop it, you\'re making me blush 😊',
            'I really enjoyed talking to you',
            'Me too! Let\'s do this again soon',
        ];

        foreach ($matches as $match) {
            $conv = Conversation::create([
                'match_id' => $match->id,
                'user1_id' => $match->user1_id,
                'user2_id' => $match->user2_id,
                'last_message_at' => now()->subHours(rand(1, 72)),
            ]);

            $msgCount = rand(3, 8);
            $senderId = $match->user1_id;
            $lastMsg = null;

            for ($i = 0; $i < $msgCount; $i++) {
                $createdAt = now()->subHours(rand(1, 72))->addMinutes($i * rand(1, 60));
                $lastMsg = Message::create([
                    'conversation_id' => $conv->id,
                    'sender_id' => $senderId,
                    'content' => $sampleMessages[array_rand($sampleMessages)],
                    'type' => 'text',
                    'status' => 'read',
                    'read_at' => $createdAt->addMinutes(rand(1, 30)),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
                $senderId = $senderId === $match->user1_id ? $match->user2_id : $match->user1_id;
            }

            if ($lastMsg) {
                $conv->update(['last_message_at' => $lastMsg->created_at]);
            }
        }
    }
}

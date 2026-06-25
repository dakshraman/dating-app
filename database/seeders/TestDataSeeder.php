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

        $faker = \Faker\Factory::create();
        $users = collect();
        for ($i = 0; $i < 50; $i++) {
            $gender = $faker->randomElement(['male', 'female']);
            $users->push([
                'name' => $faker->name($gender === 'male' ? 'male' : 'female'),
                'email' => $faker->unique()->safeEmail(),
                'gender' => $gender,
                'birth_date' => $faker->dateTimeBetween('-40 years', '-20 years')->format('Y-m-d'),
                'bio' => $faker->text(100),
                'location' => $faker->city . ', ' . $faker->stateAbbr,
                'photo' => 'https://i.pravatar.cc/400?u=' . $faker->uuid,
            ]);
        }

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

<?php

namespace App\Console\Commands;

use App\Events\MessageSent;
use App\Models\Message;
use Illuminate\Console\Command;

class ReverbTest extends Command
{
    protected $signature = 'reverb:test {message_id?}';

    protected $description = 'Test Reverb broadcast delivery';

    public function handle(): void
    {
        $messageId = $this->argument('message_id');
        $message = $messageId ? Message::find($messageId) : Message::latest()->first();

        if (! $message) {
            $this->error('No message found');

            return;
        }

        $this->info("Broadcasting MessageSent for message #{$message->id}...");
        $this->info("Conversation: {$message->conversation_id}");
        $this->info("Sender: {$message->sender_id}");

        try {
            broadcast(new MessageSent($message));
            $this->info('Broadcast dispatched successfully');
        } catch (\Throwable $e) {
            $this->error('Broadcast failed: '.$e->getMessage());
        }
    }
}

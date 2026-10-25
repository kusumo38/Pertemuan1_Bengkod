<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QueueUpdated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $poliId,
        public int $currentQueue = 0 // Tambahkan = 0 agar argumen kedua opsional
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel("poliklinik.queue.{$this->poliId}")];
    }

    public function broadcastAs(): string
    {
        return 'queue.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'poliId' => $this->poliId,
            'currentQueue' => $this->currentQueue,
        ];
    }
}
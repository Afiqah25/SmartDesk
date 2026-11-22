<?php

namespace App\Jobs;

use App\Contracts\TicketAiService;
use App\Models\Ticket;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ClassifyTicket implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public int $ticketId)
    {
    }

    public function handle(TicketAiService $ai): void
    {
        $ticket = Ticket::findOrFail($this->ticketId);

        if ($ticket->ai_status === 'done') {
            return;
        }

        $result = $ai->classify($ticket);

        
        $ticket->refresh();

        $data = [
            'sentiment' => $result['sentiment'],
            'summary' => $result['summary'],
            'language' => $result['language'],
            'ai_status' => 'done',
        ];

        if ($ticket->category_source !== 'agent') {
            $data['category'] = $result['category'];
        }

        if ($ticket->priority_source !== 'agent') {
            $data['priority'] = $result['priority'];
        }

        $ticket->update($data);

    }

    public function failed(?\Throwable $exception): void
    {
        Ticket::whereKey($this->ticketId)
            ->update(['ai_status' => 'failed']);
    }
}

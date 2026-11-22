<?php

namespace App\Services;

use App\Contracts\TicketAiService;
use App\Models\Ticket;

class FakeTicketAiService implements TicketAiService
{
    public function classify(Ticket $ticket): array
    {
        $text = strtolower(
            $ticket->subject . ' ' . $ticket->message
        );

        $category = match (true) {
            str_contains($text, 'login'),
            str_contains($text, 'password') => 'account',

            str_contains($text, 'payment'),
            str_contains($text, 'refund') => 'billing',

            str_contains($text, 'error'),
            str_contains($text, 'bug') => 'technical',

            str_contains($text, 'feature') => 'feature_request',

            default => 'other',
        };

        return [
            'category' => $category,
            'priority' => 'medium',
            'sentiment' => 'neutral',
            'summary' => mb_substr($ticket->message, 0, 200),
            'language' => 'en',
        ];
    }

    public function draftReply(Ticket $ticket): string
    {
        return "Thank you for contacting SmartDesk. "
            . "We are reviewing your ticket "
            . $ticket->reference . ".";
    }
}

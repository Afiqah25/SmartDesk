<?php

namespace App\Contracts;

use App\Models\Ticket;

interface TicketAiService
{
    public function classify(Ticket $ticket): array;

    public function draftReply(Ticket $ticket): string;
}

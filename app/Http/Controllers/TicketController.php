<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketRequest;
use App\Models\Ticket;
use App\Jobs\ClassifyTicket;

class TicketController extends Controller
{
    public function create()
    {
        return view('tickets.create');
    }

    
    public function store(StoreTicketRequest $request)
    {
        $ticket = Ticket::create($request->validated());

        ClassifyTicket::dispatch($ticket->id);

        return redirect()->route('tickets.success', $ticket);
    }


    public function success(Ticket $ticket)
    {
        return view('tickets.success', compact('ticket'));
    }
}

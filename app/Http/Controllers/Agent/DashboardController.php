<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use App\Contracts\TicketAiService;

class DashboardController extends Controller
{
    
    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => 'nullable|in:new,open,resolved',
            'category' => 'nullable|in:billing,technical,account,feature_request,other',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'search' => 'nullable|string|max:100',
        ]);

        $query = Ticket::query();

        foreach (['status', 'category', 'priority'] as $field) {
            if (!empty($filters[$field])) {
                $query->where($field, $filters[$field]);
            }
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $tickets = $query->latest()
            ->paginate(15)
            ->withQueryString();

        
        $stats = [
            'total' => Ticket::count(),

            'new' => Ticket::where('status', 'new')->count(),

            'open' => Ticket::where('status', 'open')->count(),

            'resolved' => Ticket::where('status', 'resolved')->count(),

            'categories' => Ticket::selectRaw(
                'category, COUNT(*) as total'
            )->groupBy('category')->pluck('total', 'category'),

            'reclassified' => Ticket::where(
                'category_source', 'agent'
            )->count(),
        ];

        $stats['reclassified_percent'] = $stats['total'] > 0
            ? round($stats['reclassified'] / $stats['total'] * 100, 1)
            : 0;

        return view('agent.dashboard', compact('tickets', 'stats'));

    }



    public function show(Ticket $ticket)
    {
        return view('agent.show', compact('ticket'));
    }


    public function update(Request $request, Ticket $ticket)
    {
        $data = $request->validate([
            'category' => [
                'required',
                'in:billing,technical,account,feature_request,other'
            ],
            'priority' => [
                'required',
                'in:low,medium,high,urgent'
            ],
            'status' => [
                'required',
                'in:new,open,resolved'
            ],
        ]);

        if ($ticket->category !== $data['category']) {
            $data['category_source'] = 'agent';
        }

        if ($ticket->priority !== $data['priority']) {
            $data['priority_source'] = 'agent';
        }

        $ticket->update($data);

        return back()->with('success', 'Ticket updated successfully!');
    }


    public function generateReply(Ticket $ticket, TicketAiService $ai)
    {
        $reply = $ai->draftReply($ticket);

        return back()->withInput([
            'reply' => $reply,
        ]);
    }


    public function saveReply(Request $request, Ticket $ticket)
    {
        $data = $request->validate([
            'reply' => ['required', 'string', 'max:5000'],
        ]);

        $ticket->update($data);

        return back()->with('success', 'Reply saved successfully!');
    }




}


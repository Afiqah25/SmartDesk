
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SmartDesk | Agent Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/smartdesk.css') }}">
</head>
<body>

<div class="app">

    <aside class="sidebar">
        <h2>SmartDesk</h2>

        <a href="{{ route('agent.dashboard') }}">
            Dashboard
        </a>

        <a href="{{ route('tickets.create') }}" target="_blank">
            New Ticket Form
        </a>

        <hr>

        <p>Logged in as:</p>
        <strong>{{ auth()->user()->name }}</strong>

        <form method="POST" action="{{ route('logout') }}"
              style="margin-top:20px">
            @csrf
            <button type="submit">Logout</button>
        </form>
    </aside>

    <main class="main">

        <h1 class="page-title">Agent Dashboard</h1>
        <p class="muted">
            Manage support tickets and AI classifications.
        </p>

        <section class="cards">

            <div class="stat-card">
                <small>Total Tickets</small>
                <h2>{{ $stats['total'] }}</h2>
            </div>

            <div class="stat-card">
                <small>New Tickets</small>
                <h2>{{ $stats['new'] }}</h2>
            </div>

            <div class="stat-card">
                <small>Open Tickets</small>
                <h2>{{ $stats['open'] }}</h2>
            </div>

            <div class="stat-card">
                <small>Resolved Tickets</small>
                <h2>{{ $stats['resolved'] }}</h2>
            </div>

        </section>

        <section class="panel">

            <h3>AI Performance</h3>

            <p>
                Human Reclassification:
                <strong>
                    {{ $stats['reclassified_percent'] }}%
                </strong>
            </p>

            <h4>Tickets by Category</h4>

            @forelse ($stats['categories'] as $category => $total)
                <span class="badge">
                    {{ ucfirst(str_replace('_', ' ', $category ?: 'pending')) }}
                    : {{ $total }}
                </span>
            @empty
                <p class="muted">No tickets yet.</p>
            @endforelse

        </section>

        <section class="panel">

            <h3>Ticket Management</h3>

            <form class="filters" method="GET"
                  action="{{ route('agent.dashboard') }}">

                <select name="status">
                    <option value="">All Statuses</option>
                    @foreach (['new', 'open', 'resolved'] as $status)
                        <option value="{{ $status }}"
                            @selected(request('status') === $status)>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>

                <select name="category">
                    <option value="">All Categories</option>
                    @foreach (['billing', 'technical', 'account',
                               'feature_request', 'other'] as $category)
                        <option value="{{ $category }}"
                            @selected(request('category') === $category)>
                            {{ ucfirst(str_replace('_', ' ', $category)) }}
                        </option>
                    @endforeach
                </select>

                <select name="priority">
                    <option value="">All Priorities</option>
                    @foreach (['low', 'medium', 'high', 'urgent'] as $priority)
                        <option value="{{ $priority }}"
                            @selected(request('priority') === $priority)>
                            {{ ucfirst($priority) }}
                        </option>
                    @endforeach
                </select>

                <input type="search" name="search"
                       placeholder="Search tickets..."
                       value="{{ request('search') }}"
                       maxlength="100">

                <button type="submit">Filter</button>

                <a href="{{ route('agent.dashboard') }}">
                    Reset
                </a>
            </form>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Category</th>
                            <th>Priority</th>
                            <th>AI Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($tickets as $ticket)
                            <tr>
                                <td>
                                    <a href="{{ route('agent.tickets.show', $ticket) }}">
                                        {{ $ticket->reference }}
                                    </a>
                                </td>

                                <td>{{ $ticket->subject }}</td>

                                <td>
                                    <span class="badge {{ $ticket->status }}">
                                        {{ ucfirst($ticket->status) }}
                                    </span>
                                </td>

                                <td>
                                    {{ ucfirst(str_replace('_', ' ', $ticket->category ?? 'pending')) }}
                                </td>

                                <td>
                                    <span class="badge {{ $ticket->priority }}">
                                        {{ ucfirst($ticket->priority ?? 'pending') }}
                                    </span>
                                </td>

                                <td>
                                    <span class="badge {{ $ticket->ai_status }}">
                                        {{ ucfirst($ticket->ai_status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    No matching tickets found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top:20px">
                {{ $tickets->links() }}
            </div>

        </section>

    </main>
</div>

</body>
</html>

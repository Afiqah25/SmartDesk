
<!DOCTYPE html>
<html>
<head>

<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="{{ asset('css/smartdesk.css') }}">

    <title>SmartDesk Ticket Detail</title>
</head>
<body>
    <div class="app">
    <aside class="sidebar">
        <h2>SmartDesk</h2>
        <a href="{{ route('agent.dashboard') }}">Dashboard</a>
        <a href="{{ route('tickets.create') }}">New Ticket Form</a>
    </aside>

    <main class="main">
        
    <h2>Ticket {{ $ticket->reference }}</h2>

    <p><b>Customer:</b> {{ $ticket->name }}</p>
    <p><b>Email:</b> {{ $ticket->email }}</p>
    <p><b>Subject:</b> {{ $ticket->subject }}</p>
    <p><b>Message:</b> {{ $ticket->message }}</p>

    <hr>

<section class="panel">
    <h3>AI Classification</h3>

    <p>AI Status: {{ $ticket->ai_status }}</p>
    <p>Category: {{ $ticket->category ?? 'Pending' }}</p>
    <p>Priority: {{ $ticket->priority ?? 'Pending' }}</p>
    <p>Sentiment: {{ $ticket->sentiment ?? '-' }}</p>
    <p>Summary: {{ $ticket->summary ?? '-' }}</p>
    <p>Language: {{ $ticket->language ?? '-' }}</p>
</section>

<section class="panel">
<h3>Human Agent Override</h3>

@if (session('success'))
    <p style="color:green">{{ session('success') }}</p>
@endif

<form method="POST" action="{{ route('agent.tickets.update', $ticket) }}">
    @csrf
    @method('PATCH')

    <label>Category:</label>
    <select name="category" required>
        @foreach (['billing', 'technical', 'account', 'feature_request', 'other'] as $category)
            <option value="{{ $category }}"
                @selected(old('category', $ticket->category) === $category)>
                {{ ucfirst($category) }}
            </option>
        @endforeach
    </select>

    <br><br>

    <label>Priority:</label>
    <select name="priority" required>
        @foreach (['low', 'medium', 'high', 'urgent'] as $priority)
            <option value="{{ $priority }}"
                @selected(old('priority', $ticket->priority) === $priority)>
                {{ ucfirst($priority) }}
            </option>
        @endforeach
    </select>

    <br><br>

    <label>Status:</label>
    <select name="status" required>
        @foreach (['new', 'open', 'resolved'] as $status)
            <option value="{{ $status }}"
                @selected(old('status', $ticket->status) === $status)>
                {{ ucfirst($status) }}
            </option>
        @endforeach
    </select>

    <br><br>

    <button type="submit">Save Changes</button>
</form>

@if ($errors->any())
    <p style="color:red">{{ $errors->first() }}</p>
@endif

</section>

<hr>


<section class="panel">
<h3>AI Draft Reply</h3>

<form method="POST"
      action="{{ route('agent.tickets.generateReply', $ticket) }}">
    @csrf
    <button type="submit">Generate AI Reply</button>
</form>

<br>

<form method="POST"
      action="{{ route('agent.tickets.saveReply', $ticket) }}">
    @csrf

    <textarea name="reply" rows="8" cols="65"
              maxlength="5000" required>{{ old('reply', $ticket->reply) }}</textarea>

    <br><br>

    <button type="submit">Save Reply</button>
</form>

</section>

<hr>





    <hr>

    <a href="{{ route('agent.dashboard') }}">
        Back to Dashboard
    </a>
</body>



    </main>
</div>

</html>

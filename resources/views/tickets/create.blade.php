
<!DOCTYPE html>
<html lang="en">
<head>

<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="{{ asset('css/smartdesk.css') }}">

    <meta charset="UTF-8">
    <title>SmartDesk - Submit Ticket</title>
</head>
<body>

<div class="main" style="max-width:760px; margin:40px auto;">

    <h1>SmartDesk Support</h1>
    <p class="muted">
        Submit a support request. Our AI will help
        classify your ticket for the support team.
    </p>

    <section class="panel">

    <form class="ticket-form" method="POST" action="{{ route('tickets.store') }}">
        @csrf

        <input name="name" placeholder="Your name"
               value="{{ old('name') }}" required>
        <br>

        <input type="email" name="email"
               placeholder="Your email"
               value="{{ old('email') }}" required>
        <br>

        <input name="subject" placeholder="Subject"
               value="{{ old('subject') }}" required>
        <br>

        <textarea name="message"
                  placeholder="Describe your issue"
                  minlength="20" maxlength="5000"
                  required>{{ old('message') }}</textarea>
        <br>

        @if ($errors->any())
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <button type="submit">Submit Ticket</button>
    </form>
    </section>

</div>
</body>
</html>

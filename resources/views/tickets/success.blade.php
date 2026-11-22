
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ticket Submitted | SmartDesk</title>
    <link rel="stylesheet" href="{{ asset('css/smartdesk.css') }}">
</head>
<body>

<div class="main" style="max-width:650px; margin:80px auto;">

    <section class="panel" style="text-align:center;">

        <h1>Ticket Submitted Successfully!</h1>

        <p class="muted">
            Thank you for contacting SmartDesk Support.
        </p>

        <p>Your ticket reference:</p>

        <h2 style="color:#635bff;">
            {{ $ticket->reference }}
        </h2>

        <p>
            Your request has been received and
            will be reviewed by our support team.
        </p>

        <a href="{{ route('tickets.create') }}">
            Submit Another Ticket
        </a>

    </section>

</div>

</body>
</html>

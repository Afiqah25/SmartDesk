
<!DOCTYPE html>
<html>
<head>
    <title>SmartDesk Agent Login</title>
</head>
<body>
    <h2>SmartDesk — Agent Login</h2>

    @if ($errors->any())
        <p style="color:red">{{ $errors->first() }}</p>
    @endif

    <form method="POST" action="{{ route('login.submit') }}">
        @csrf

        <label>Email</label>
        <input type="email" name="email"
               value="{{ old('email') }}" required>

        <br><br>

        <label>Password</label>
        <input type="password" name="password" required>

        <br><br>

        <button type="submit">Login</button>
    </form>
</body>
</html>

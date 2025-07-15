<!DOCTYPE html>
<html>
<head>
    <title>Login Web</title>
</head>
<body>
    <h2>Login</h2>
    @if(session('error'))
        <p style="color:red;">{{ session('error') }}</p>
    @endif

    <form method="POST" action="{{ route('login.web') }}">
        @csrf
        <label>Nama Pengguna:</label>
        <input type="text" name="nama_pengguna" required><br><br>

        <label>Password:</label>
        <input type="password" name="password" required><br><br>

        <button type="submit">Login</button>
    </form>
</body>
</html>

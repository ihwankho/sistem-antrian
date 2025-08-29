<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login Test</title>
</head>
<body>
    <h2>Login</h2>
    @if($errors->any())
        <p style="color:red">{{ $errors->first() }}</p>
    @endif
    <form method="POST" action="{{ route('login.submit') }}">
        @csrf
        <label>Nama Pengguna:</label><br>
        <input type="text" name="nama_pengguna" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Login</button>
    </form>
</body>
</html>

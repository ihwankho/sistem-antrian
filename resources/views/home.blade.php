<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
    <style>
        table {
            border-collapse: collapse;
            width: 50%;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
        }
        .error {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h2>Selamat datang, {{ session('user.nama') }}</h2>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Logout</button>
    </form>

    @if(!empty($tokenExpired) && $tokenExpired)
        <p class="error">Token kadaluarsa, silahkan login terlebih dahulu.</p>
    @endif

    <h3>Daftar Loket</h3>
    @if(!empty($lokets))
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Loket</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lokets as $loket)
                    <tr>
                        <td>{{ $loket['id'] }}</td>
                        <td>{{ $loket['nama_loket'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>Tidak ada data loket.</p>
    @endif
</body>
</html>

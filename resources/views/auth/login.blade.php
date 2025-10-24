<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin / Petugas</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            overflow: hidden;
            background: linear-gradient(135deg, #2c3e50, #1a1a2e);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-container {
            position: relative;
            width: 100%;
            max-width: 400px;
            padding: 40px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            z-index: 10;
            transform: scale(0.95);
            animation: scaleIn 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }

        @keyframes scaleIn {
            from { transform: scale(0.95); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h2 {
            color: #ffffff;
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 2.2rem;
            background: linear-gradient(90deg, #3498db, #8e44ad);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .login-header p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 1rem;
        }

        .form-label {
            display: block;
            text-align: left;
            margin-bottom: 8px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.85);
        }

        .form-control {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            border-radius: 8px;
            padding: 12px 15px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            width: 100%;
        }

        .form-control:focus {
            background-color: rgba(255, 255, 255, 0.15);
            color: white;
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
            outline: none;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .btn-login {
            background: linear-gradient(90deg, #3498db, #8e44ad);
            color: white;
            font-weight: 700;
            padding: 12px;
            border-radius: 8px;
            border: none;
            width: 100%;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, #8e44ad, #3498db);
            transition: all 0.6s ease;
            z-index: -1;
        }

        .btn-login:hover::before {
            left: 0;
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 7px 15px rgba(0, 0, 0, 0.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .error-message {
            color: #ff6b6b;
            text-align: center;
            margin-bottom: 20px;
            font-weight: 500;
            animation: shake 0.5s ease;
            display: block;
            background: rgba(255, 107, 107, 0.1);
            padding: 10px;
            border-radius: 8px;
            border-left: 4px solid #ff6b6b;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-5px); }
            40%, 80% { transform: translateX(5px); }
        }

        .decoration {
            position: absolute;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: linear-gradient(45deg, #3498db, transparent);
            filter: blur(60px);
            z-index: 0;
        }

        .decoration:nth-child(1) {
            top: -80px;
            left: -80px;
            width: 250px;
            height: 250px;
            background: linear-gradient(45deg, #8e44ad, transparent);
        }

        .decoration:nth-child(2) {
            bottom: -100px;
            right: -80px;
            width: 300px;
            height: 300px;
            background: linear-gradient(45deg, #3498db, transparent);
        }

        .decoration:nth-child(3) {
            top: 50%;
            left: 20%;
            width: 150px;
            height: 150px;
            background: linear-gradient(45deg, #e74c3c, transparent);
            filter: blur(50px);
        }

        .password-container {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 42px;
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.6);
            cursor: pointer;
        }

        .footer-text {
            text-align: center;
            margin-top: 20px;
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
        }

        .footer-text a {
            color: #3498db;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-text a:hover {
            color: #8e44ad;
            text-decoration: underline;
        }

        .brand {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            z-index: 10;
        }
        
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: #3498db;
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="decoration"></div>
    <div class="decoration"></div>
    <div class="decoration"></div>
    
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>
    
    <div class="brand">Sistem Login</div>
    
    <div class="login-container">
        <div class="login-header">
            <h2>Selamat Datang</h2>
            <p>Silakan masuk ke akun Anda</p>
        </div>
        
        @if(session('error'))
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="error-message">
                @foreach($errors->all() as $error)
                    <i class="fas fa-exclamation-circle"></i> {{ $error }}<br>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" id="loginForm">
            @csrf
            <div class="mb-3">
                <label for="nama_pengguna" class="form-label">Nama Pengguna</label>
                <input type="text" class="form-control @error('nama_pengguna') is-invalid @enderror" id="nama_pengguna" name="nama_pengguna" value="{{ old('nama_pengguna') }}" required placeholder="Masukkan nama pengguna">
                @error('nama_pengguna')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3 password-container">
                <label for="password" class="form-label">Kata Sandi</label>
                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required placeholder="Masukkan kata sandi">
                <button type="button" class="toggle-password" id="togglePassword">
                    <i class="fas fa-eye"></i>
                </button>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn-login" id="loginButton">
                <span>Masuk</span>
            </button>
        </form>
        
        <div class="footer-text">
            Lupa kata sandi? <a href="#">Klik di sini</a><br>
            Belum punya akun? <a href="#">Daftar sekarang</a>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const loginForm = document.getElementById('loginForm');
            const loadingOverlay = document.getElementById('loadingOverlay');
            
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                if (type === 'password') {
                    this.innerHTML = '<i class="fas fa-eye"></i>';
                } else {
                    this.innerHTML = '<i class="fas fa-eye-slash"></i>';
                }
            });
            
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach((input, index) => {
                input.style.opacity = "0";
                input.style.transform = "translateY(20px)";
                setTimeout(() => {
                    input.style.transition = "opacity 0.5s ease, transform 0.5s ease";
                    input.style.opacity = "1";
                    input.style.transform = "translateY(0)";
                }, 300 + (index * 100));
            });
            
            loginForm.addEventListener('submit', function() {
                loadingOverlay.style.display = 'flex';
            });
        });
    </script>
</body>
</html>
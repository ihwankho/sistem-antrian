<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin / Petugas</title>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">

    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
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

        <form method="POST" action="{{ route('login.post') }}" id="loginForm">
            @csrf
            <div class="mb-3">
                <label for="nama_pengguna" class="form-label">Email</label> 
                <input type="email" class="form-control @error('nama_pengguna') is-invalid @enderror" 
                       id="nama_pengguna" name="nama_pengguna" value="{{ old('nama_pengguna') }}" 
                       required placeholder="Masukkan email Anda">
                
                @error('nama_pengguna')
                    <div class="invalid-field-message">
                        {{ $message }}
                    </div>
                @enderror
            </div>
            
            <div class="mb-3 password-container">
                <label for="password" class="form-label">Kata Sandi</label>
                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                       id="password" name="password" required placeholder="Masukkan kata sandi">
                <button type="button" class="toggle-password" id="togglePassword">
                    <i class="fas fa-eye"></i>
                </button>

                @error('password')
                    <div class="invalid-field-message">
                        {{ $message }}
                    </div>
                @enderror
            </div>
            
            <div class="mb-3 d-flex justify-content-center flex-column">
                <div class="g-recaptcha" 
                     data-sitekey="{{ config('services.recaptcha.site_key') }}"
                     data-theme="dark"
                     style="margin: 0 auto;"> </div>
                
                @error('g-recaptcha-response')
                    @php
                        // Logika untuk pesan error reCAPTCHA yang lebih ramah
                        $recaptchaError = $message == 'Verifikasi "I\'m not a robot" gagal. Silakan coba lagi.' 
                                        ? 'Verifikasi Keamanan gagal, silakan centang kotak.' 
                                        : $message;
                    @endphp
                    <div class="invalid-field-message text-center mt-2">
                        {{ $recaptchaError }}
                    </div>
                @enderror
            </div>
            
            <button type="submit" class="btn-login" id="loginButton">
                <span>Masuk</span>
            </button>
        </form>
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
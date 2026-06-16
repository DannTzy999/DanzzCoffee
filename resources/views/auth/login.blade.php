@extends('/layouts/auth')

@push('css-dependencies')
<link href="/css/auth.css" rel="stylesheet" />
@endpush

@section("content")
<div class="auth-wrapper">
    <!-- Left Side - Coffee Brand -->
    <div class="auth-left">
        <!-- Coffee Beans Pattern -->
        <div class="coffee-pattern">
            <span></span><span></span><span></span><span></span>
            <span></span><span></span><span></span>
        </div>

        <!-- Brand Name -->
        <div class="brand-section">
            <h1 class="brand-title">Danzz Coffe</h1>
            <p class="brand-subtitle">Selamat datang kembali!</p>
            <p class="brand-description">Kopimu sudah menunggumu.</p>
        </div>

        <!-- Quote Section -->
        <div class="quote-box">
            <p class="quote-text">"Hari yang baik dimulai dari secangkir kopi yang tepat dan orang-orang yang hangat."</p>
            <p class="quote-author">— Danzz Coffe</p>
        </div>

        <!-- Features List -->
        <div class="features-list">
            <div class="feature-item">
                <i class="fas fa-coffee"></i>
                <span>Kopi premium dari kebun pilihan</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-truck"></i>
                <span>Pengiriman cepat ke seluruh kota</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-heart"></i>
                <span>Dibuat dengan cinta setiap hari</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="auth-footer-left">
            <p>EST. 2024 · DANZZ COFFE</p>
        </div>
    </div>

    <!-- Right Side - Login Form -->
    <div class="auth-right">
        <div class="form-container">
            <!-- Header -->
            <div class="form-header">
                <h2>Masuk ke akun kamu</h2>
                <p>Senang melihatmu lagi</p>
            </div>

            <!-- Messages -->
            @if(session()->has('message'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                {!! session("message") !!}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <!-- Login Form -->
            <form method="post" action="/auth/login" autocomplete="off">
                @csrf

                <!-- Email Field -->
                <div class="form-group">
                    <label for="email">EMAIL ADDRESS</label>
                    <input 
                        type="email" 
                        class="form-control @error('email') is-invalid @enderror"
                        id="email" 
                        name="email" 
                        placeholder="Enter your email"
                        value="{{ @old('email') }}" 
                        autocomplete="off"
                        required>
                    @error('email')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password Field -->
                <div class="form-group">
                    <label for="password">PASSWORD</label>
                    <div class="password-wrapper">
                        <input 
                            type="password"
                            class="form-control @error('password') is-invalid @enderror" 
                            id="password"
                            name="password" 
                            placeholder="Enter your password" 
                            required>
                        <span class="toggle-password" onclick="togglePasswordVisibility('password')">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    @error('password')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Forgot Password Link -->
                <div class="forgot-password-link">
                    <a href="/auth/forgot_password">Lupa password?</a>
                </div>

                <!-- Login Button -->
                <button type="submit" class="btn-submit">Sign In</button>
            </form>

            <!-- Register Link -->
            <div class="auth-footer-form">
                <p>Belum punya akun? <a href="/auth/register" class="register-link">Daftar sekarang</a></p>
                <p class="footer-text">EST. 2024 · DANZZ COFFE</p>
            </div>
        </div>
    </div>
</div>

<script>
function togglePasswordVisibility(fieldId) {
    const input = document.getElementById(fieldId);
    const icon = event.currentTarget.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>
@endsection
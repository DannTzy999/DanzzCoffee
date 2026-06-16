@extends('/layouts/auth')

@push('css-dependencies')
<link href="/css/auth.css" rel="stylesheet" />
@endpush

@section("content")
<div class="auth-wrapper">
    <!-- Left Side - Coffee Brand & Instructions -->
    <div class="auth-left">
        <!-- Coffee Beans Pattern -->
        <div class="coffee-pattern">
            <span></span><span></span><span></span><span></span>
            <span></span><span></span><span></span>
        </div>

        <!-- Brand Name -->
        <div class="brand-section">
            <h1 class="brand-title">Danzz Coffe</h1>
            <p class="brand-subtitle">Tenang, kami bantu kamu</p>
            <p class="brand-description">kembali ke akun mu.</p>
        </div>

        <!-- Reset Password Steps -->
        <div class="reset-steps">
            <h3 class="reset-title">CARA RESET PASSWORD</h3>
            <div class="steps-container">
                <div class="step-item">
                    <span class="step-number">1</span>
                    <p class="step-text">Masukkan email yang terdaftar</p>
                </div>
                <div class="step-item">
                    <span class="step-number">2</span>
                    <p class="step-text">Kami kirim link verifikasi ke email kamu</p>
                </div>
                <div class="step-item">
                    <span class="step-number">3</span>
                    <p class="step-text">Buka link dan buat password baru</p>
                </div>
            </div>
        </div>

        <!-- Security Features -->
        <div class="security-features">
            <div class="security-item">
                <i class="fas fa-lock"></i>
                <span>Akunmu aman dan terenkripsi</span>
            </div>
            <div class="security-item">
                <i class="fas fa-bolt"></i>
                <span>Proses verifikasi cepat & mudah</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="auth-footer-left">
            <p>EST. 2024 · DANZZ COFFE</p>
        </div>
    </div>

    <!-- Right Side - Reset Password Form -->
    <div class="auth-right">
        <div class="form-container">
            <!-- Header -->
            <div class="form-header">
                <div class="form-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <h2>Reset password</h2>
                <p>Masukkan email yang terdaftar di akun kamu. Kami akan kirim link verifikasi ke email kamu.</p>
            </div>

            <!-- Messages -->
            @if(session()->has('message'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                {!! session("message") !!}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <!-- Verification Section Label -->
            <div class="verification-label">
                VERIFY ACCOUNT
            </div>

            <!-- Forgot Password Form -->
            <form method="post" action="/auth/forgot_password" autocomplete="off" class="register-form">
                @csrf

                <!-- Email Field -->
                <div class="form-group">
                    <label for="email">EMAIL ADDRESS</label>
                    <input 
                        type="email" 
                        class="form-control @error('email') is-invalid @enderror"
                        id="email" 
                        name="email" 
                        placeholder="your@email.com"
                        value="{{ @old('email') }}" 
                        autocomplete="off"
                        required>
                    @error('email')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-submit">Kirim Link Verifikasi</button>
            </form>

            <!-- Back to Login Link -->
            <div class="auth-footer-form">
                <p><a href="/auth/login" class="back-login-link">Kembali ke login</a></p>
                <p class="footer-text">EST. 2024 · DANZZ COFFE</p>
            </div>
        </div>
    </div>
</div>
@endsection

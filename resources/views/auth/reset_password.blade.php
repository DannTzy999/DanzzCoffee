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
            <p class="brand-subtitle">Buat password baru yang</p>
            <p class="brand-description">lebih aman dan mudah diingat.</p>
        </div>

        <!-- Reset Password Steps -->
        <div class="reset-steps">
            <h3 class="reset-title">CARA BUAT PASSWORD BARU</h3>
            <div class="steps-container">
                <div class="step-item">
                    <span class="step-number">1</span>
                    <p class="step-text">Pastikan password minimal 8 karakter</p>
                </div>
                <div class="step-item">
                    <span class="step-number">2</span>
                    <p class="step-text">Gunakan kombinasi huruf & angka</p>
                </div>
                <div class="step-item">
                    <span class="step-number">3</span>
                    <p class="step-text">Konfirmasi password & simpan</p>
                </div>
            </div>
        </div>

        <!-- Security Features -->
        <div class="security-features">
            <div class="security-item">
                <i class="fas fa-lock"></i>
                <span>Password terenkripsi & aman</span>
            </div>
            <div class="security-item">
                <i class="fas fa-shield-alt"></i>
                <span>Lindungi akun mu dengan baik</span>
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
                    <i class="fas fa-key"></i>
                </div>
                <h2>Buat password baru</h2>
                <p>Buat password yang kuat untuk melindungi akun kamu</p>
            </div>

            <!-- Messages -->
            @if(session()->has('message'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                {!! session("message") !!}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <!-- Password Update Section Label -->
            <div class="verification-label">
                UPDATE PASSWORD
            </div>

            <!-- Reset Password Form -->
            <form method="post" action="/auth/reset_password" autocomplete="off" class="register-form">
                @csrf

                <!-- New Password Field -->
                <div class="form-group">
                    <label for="password">PASSWORD BARU</label>
                    <div class="password-wrapper">
                        <input 
                            type="password"
                            class="form-control @error('password') is-invalid @enderror" 
                            id="password"
                            name="password" 
                            placeholder="Buat password kuat" 
                            required>
                        <span class="toggle-password" onclick="togglePasswordVisibility('password')">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    @error('password')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Confirm Password Field -->
                <div class="form-group">
                    <label for="password_confirmation">KONFIRMASI PASSWORD</label>
                    <div class="password-wrapper">
                        <input 
                            type="password"
                            class="form-control @error('password_confirmation') is-invalid @enderror"
                            id="password_confirmation" 
                            name="password_confirmation" 
                            placeholder="Ulangi password" 
                            required>
                        <span class="toggle-password" onclick="togglePasswordVisibility('password_confirmation')">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    @error('password_confirmation')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-submit">Simpan Password Baru</button>
            </form>

            <!-- Back to Login Link -->
            <div class="auth-footer-form">
                <p><a href="/auth/login" class="back-login-link">Kembali ke login</a></p>
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

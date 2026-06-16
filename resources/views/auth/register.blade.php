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
            <p class="brand-subtitle">Tempat kopi terbaik untuk</p>
            <p class="brand-description">hari-hari terbaikmu.</p>
        </div>

        <!-- Quote Section -->
        <div class="quote-box">
            <p class="quote-text">"Secangkir kopi bukan sekadar minuman — ia adalah ritual, kehangatan, dan semangat pagi."</p>
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

    <!-- Right Side - Register Form -->
    <div class="auth-right">
        <div class="form-container">
            <!-- Header -->
            <div class="form-header">
                <h2>Buat akun baru</h2>
                <p>Bergabung dengan keluarga kopi kami</p>
            </div>

            <!-- Messages -->
            @if(session()->has('message'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                {!! session("message") !!}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <!-- Registration Form -->
            <form method="post" action="/auth/register" autocomplete="off" class="register-form">
                @csrf

                <!-- Full Name -->
                <div class="form-group">
                    <label for="fullname">NAMA LENGKAP</label>
                    <input 
                        type="text" 
                        class="form-control @error('fullname') is-invalid @enderror"
                        id="fullname" 
                        name="fullname" 
                        placeholder="e.g. John Doe"
                        value="{{ @old('fullname') }}" 
                        required>
                    @error('fullname')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Username -->
                <div class="form-group">
                    <label for="username">USERNAME</label>
                    <input 
                        type="text" 
                        class="form-control @error('username') is-invalid @enderror"
                        id="username" 
                        name="username" 
                        placeholder="Choose your username"
                        value="{{ @old('username') }}" 
                        required>
                    @error('username')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label for="email">EMAIL ADDRESS</label>
                    <input 
                        type="email" 
                        class="form-control @error('email') is-invalid @enderror"
                        id="email"
                        name="email" 
                        placeholder="your@email.com"
                        value="{{ @old('email') }}" 
                        required>
                    @error('email')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password">PASSWORD</label>
                    <div class="password-wrapper">
                        <input 
                            type="password"
                            class="form-control @error('password') is-invalid @enderror" 
                            id="password"
                            name="password" 
                            placeholder="Create strong password" 
                            required>
                        <span class="toggle-password" onclick="togglePasswordVisibility('password')">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    @error('password')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password Confirmation -->
                <div class="form-group">
                    <label for="password_confirmation">KONFIRMASI PASSWORD</label>
                    <div class="password-wrapper">
                        <input 
                            type="password"
                            class="form-control @error('password_confirmation') is-invalid @enderror"
                            id="password_confirmation" 
                            name="password_confirmation"
                            placeholder="Repeat password" 
                            required>
                        <span class="toggle-password" onclick="togglePasswordVisibility('password_confirmation')">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    @error('password_confirmation')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Gender -->
                <div class="form-group">
                    <label>JENIS KELAMIN</label>
                    <div class="gender-options">
                        <div class="form-check">
                            <input 
                                class="form-check-input" 
                                type="radio" 
                                name="gender" 
                                id="male" 
                                value="M" 
                                {{ old('gender') == 'M' ? 'checked' : '' }}>
                            <label class="form-check-label" for="male">
                                <i class="fas fa-mars"></i> LAKI-LAKI
                            </label>
                        </div>
                        <div class="form-check">
                            <input 
                                class="form-check-input" 
                                type="radio" 
                                name="gender" 
                                id="female" 
                                value="F" 
                                {{ old('gender') == 'F' ? 'checked' : '' }}>
                            <label class="form-check-label" for="female">
                                <i class="fas fa-venus"></i> PEREMPUAN
                            </label>
                        </div>
                    </div>
                    @error('gender')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Phone -->
                <div class="form-group">
                    <label for="phone">NO. TELEPON</label>
                    <input 
                        type="text" 
                        class="form-control @error('phone') is-invalid @enderror"
                        id="phone"
                        name="phone" 
                        placeholder="08xx xxxx xxxx"
                        value="{{ @old('phone') }}">
                    @error('phone')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Address -->
                <div class="form-group">
                    <label for="address">ALAMAT</label>
                    <textarea 
                        class="form-control @error('address') is-invalid @enderror"
                        id="address"
                        name="address" 
                        placeholder="Enter your address"
                        rows="2">{{ @old('address') }}</textarea>
                    @error('address')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-submit">Daftar Sekarang</button>
            </form>

            <!-- Login Link -->
            <div class="auth-footer-form">
                <p>Sudah punya akun? <a href="/auth/login" class="register-link">Login sekarang</a></p>
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
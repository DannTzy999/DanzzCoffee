@extends('layouts.auth')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-envelope-check" style="font-size: 3rem; color: #007bff;"></i>
                    </div>

                    <h2 class="card-title text-center mb-3">Verifikasi Email Anda</h2>

                    <div class="alert alert-info" role="alert">
                        <strong>Email verification diperlukan!</strong>
                        <p class="mb-0 mt-2">
                            Kami telah mengirimkan link verifikasi ke email Anda. Silakan cek inbox email Anda dan klik link verifikasi untuk melanjutkan.
                        </p>
                    </div>

                    @if (session('status') == 'verification-link-sent')
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            Link verifikasi baru telah dikirim ke email Anda!
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="card bg-light border-0 mb-4">
                        <div class="card-body">
                            <h5 class="card-title">📧 Langkah-langkah Verifikasi:</h5>
                            <ol class="mb-0">
                                <li>Cek <strong>inbox</strong> email Anda</li>
                                <li>Jika tidak ditemukan, cek folder <strong>Spam/Junk</strong></li>
                                <li>Klik link verifikasi dalam email</li>
                                <li>Akun Anda siap digunakan!</li>
                            </ol>
                        </div>
                    </div>

                    <form action="{{ route('verification.send') }}" method="POST" class="mb-3">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="bi bi-arrow-clockwise me-2"></i>
                            Kirim Ulang Email Verifikasi
                        </button>
                    </form>

                    <div class="text-center">
                        <p class="text-muted small mb-0">
                            Verifikasi email berlaku hingga <strong>24 jam</strong> sejak pendaftaran
                        </p>
                    </div>

                    <hr class="my-4">

                    <div class="alert alert-warning" role="alert">
                        <h6 class="alert-heading">⚠️ Perlu Bantuan?</h6>
                        <ul class="mb-0 small">
                            <li>Pastikan email yang Anda gunakan sudah benar</li>
                            <li>Cek folder Spam/Junk di email Anda</li>
                            <li>Whitelist email danzzyt1603@gmail.com di email client Anda</li>
                            <li>Jika masalah berlanjut, hubungi support kami</li>
                        </ul>
                    </div>

                    <div class="text-center mt-4">
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary">
                                <i class="bi bi-box-arrow-right me-2"></i>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <p class="text-muted small">
                    <i class="bi bi-info-circle me-2"></i>
                    Verifikasi email membantu kami melindungi akun Anda dan memastikan Anda dapat menerima notifikasi penting
                </p>
            </div>
        </div>
    </div>
</div>

<style>
    .container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .card {
        border-radius: 10px;
        border: none;
        background: white;
    }

    .btn {
        border-radius: 5px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background-color: #0056b3;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .alert {
        border-radius: 5px;
    }
</style>
@endsection

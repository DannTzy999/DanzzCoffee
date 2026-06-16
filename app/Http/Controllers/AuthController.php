<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use App\Rules\ValidEmailDomain;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\{Auth, Hash, DB, Password};
use Illuminate\Validation\Rules\Password as PasswordRule;

class AuthController extends Controller
{
    public function loginGet()
    {
        $title = "Login";

        return view('/auth/login', compact("title"));
    }

    public function loginPost(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email:dns',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Check if email is verified
            if (!$user->hasVerifiedEmail()) {
                Auth::logout();
                $message = "Silakan verifikasi email Anda terlebih dahulu. Cek inbox email Anda untuk link verifikasi.";
                myFlasherBuilder(message: $message, failed: true);
                return redirect('/auth/login');
            }

            $request->session()->regenerate();
            $message = "Login success";

            myFlasherBuilder(message: $message, success: true);
            return redirect('/home');
        }

        $message = "Wrong credential";

        myFlasherBuilder(message: $message, failed: true);
        return back();
    }

    public function registrationGet()
    {
        $title = "Registration";

        return view('/auth/register', compact("title"));
    }

    public function registrationPost(Request $request)
    {
        // Validasi data dengan password yang lebih kuat
        $validatedData = $request->validate(
            [
                'fullname' => 'required|max:255',
                'username' => 'required|max:15|unique:users,username',
                'email' => [
                    'required',
                    'email:rfc,dns',
                    'unique:users,email',
                    new ValidEmailDomain()
                ],
                'password' => [
                    'required',
                    'confirmed',
                    PasswordRule::min(8)
                        ->mixedCase()
                        ->numbers()
                        ->symbols()
                        ->uncompromised()
                ],
                'phone' => 'required|numeric|min:10',
                'gender' => 'required|in:M,F',
                'address' => 'required|max:255',
            ],
            [
                'password.min' => 'Password harus minimal 8 karakter',
                'password.mixed_case' => 'Password harus mengandung huruf besar dan huruf kecil',
                'password.numbers' => 'Password harus mengandung angka',
                'password.symbols' => 'Password harus mengandung simbol (!@#$%^&*)',
                'password.uncompromised' => 'Password ini terlalu umum. Silakan gunakan password yang lebih kuat',
                'email.unique' => 'Email sudah terdaftar',
                'username.unique' => 'Username sudah digunakan',
            ]
        );

        $validatedData['password'] = Hash::make($validatedData['password']);
        $validatedData['image'] = env("IMAGE_PROFILE");
        $validatedData = array_merge($validatedData, [
            "coupon" => 0,
            "point" => 0,
            'remember_token' => Str::random(30),
            'role_id' => 2 // value 2 for customer role
        ]);
        
        try {
            $user = User::create($validatedData);
            
            // Send email verification notification
            $user->sendEmailVerificationNotification();
            
            $message = "Akun Anda berhasil dibuat! Silakan cek email Anda untuk verifikasi. Link verifikasi berlaku selama 24 jam.";
            myFlasherBuilder(message: $message, success: true);

            return redirect('/auth/login')
                ->with('info', 'Email verifikasi telah dikirim. Silakan cek inbox atau folder spam Anda.');
        } catch (\Illuminate\Database\QueryException $exception) {
            return back()->withErrors(['error' => 'Gagal membuat akun. Pastikan database terkoneksi.'])->withInput();
        }
    }


    public function forgotPasswordGet()
    {
        $title = "Forgot Password";

        return view('/auth/forgot_password', compact("title"));
    }

    public function forgotPasswordPost(Request $request)
    {
        $request->validate([
            'email' => 'required|email:dns|exists:users,email',
        ], [
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.exists' => 'Email tidak terdaftar di sistem kami'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            $message = "Email tidak ditemukan di sistem kami!";
            myFlasherBuilder(message: $message, failed: true);
            return back()->withInput();
        }

        try {
            // Generate token untuk reset password
            $token = Str::random(64);

            // Simpan token ke database
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                [
                    'token' => Hash::make($token),
                    'created_at' => now()
                ]
            );

            // Kirim email dengan link verifikasi
            $user->notify(new ResetPasswordNotification($token));

            $message = "Link verifikasi reset password telah dikirim ke email Anda. Silakan cek inbox atau folder spam. Link berlaku selama 1 jam.";
            myFlasherBuilder(message: $message, success: true);
            return redirect('/auth/login');
        } catch (\Exception $e) {
            $message = "Terjadi kesalahan saat mengirim email. Silakan coba lagi.";
            myFlasherBuilder(message: $message, failed: true);
            return back()->withInput();
        }
    }

    public function resetPasswordGet(Request $request)
    {
        $token = $request->query('token');
        $email = $request->query('email');

        // Validasi token dan email
        if (!$token || !$email) {
            $message = "Link reset password tidak valid atau sudah kadaluarsa!";
            myFlasherBuilder(message: $message, failed: true);
            return redirect('/auth/login');
        }

        // Cek token di database
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$resetRecord) {
            $message = "Link reset password tidak valid atau sudah kadaluarsa!";
            myFlasherBuilder(message: $message, failed: true);
            return redirect('/auth/login');
        }

        // Cek apakah token cocok dan belum kadaluarsa
        if (!Hash::check($token, $resetRecord->token)) {
            $message = "Link reset password tidak valid!";
            myFlasherBuilder(message: $message, failed: true);
            return redirect('/auth/login');
        }

        // Cek apakah token masih dalam 1 jam
        $createdAt = \Carbon\Carbon::parse($resetRecord->created_at);
        if ($createdAt->addHour()->isPast()) {
            // Hapus token yang sudah expired
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            $message = "Link reset password sudah kadaluarsa. Silakan minta link baru.";
            myFlasherBuilder(message: $message, failed: true);
            return redirect('/auth/forgot_password');
        }

        $title = "Reset Password";
        $data = compact('token', 'email', 'title');

        return view('/auth/reset_password', $data);
    }

    public function resetPasswordPost(Request $request)
    {
        $token = $request->input('token');
        $email = $request->input('email');

        // Validasi token dan email
        if (!$token || !$email) {
            $message = "Silakan akses link reset password dari email Anda!";
            myFlasherBuilder(message: $message, failed: true);
            return redirect('/auth/forgot_password');
        }

        // Cek token di database
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$resetRecord) {
            $message = "Link reset password tidak valid atau sudah kadaluarsa!";
            myFlasherBuilder(message: $message, failed: true);
            return redirect('/auth/login');
        }

        // Validasi password dengan rules yang kuat
        $validated = $request->validate(
            [
                'password' => [
                    'required',
                    'confirmed',
                    PasswordRule::min(8)
                        ->mixedCase()
                        ->numbers()
                        ->symbols()
                        ->uncompromised()
                ],
                'password_confirmation' => 'required',
            ],
            [
                'password.min' => 'Password harus minimal 8 karakter',
                'password.mixed_case' => 'Password harus mengandung huruf besar dan huruf kecil',
                'password.numbers' => 'Password harus mengandung angka',
                'password.symbols' => 'Password harus mengandung simbol (!@#$%^&*)',
                'password.uncompromised' => 'Password ini terlalu umum. Silakan gunakan password yang lebih kuat',
            ]
        );

        try {
            // Update password user
            User::where('email', $email)->update(['password' => Hash::make($validated['password'])]);

            // Hapus token dari database
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            $message = "Password berhasil diubah! Silakan login dengan password baru Anda.";
            myFlasherBuilder(message: $message, success: true);
            return redirect('/auth/login');
        } catch (\Exception $e) {
            $message = "Terjadi kesalahan saat mereset password. Silakan coba lagi.";
            myFlasherBuilder(message: $message, failed: true);
            return back()->withInput();
        }
    }


    public function logoutPost()
    {
        try {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            $message = "Session ended, you logout <strong>successfully</strong>";

            myFlasherBuilder(message: $message, success: true);

            return redirect('/auth');
        } catch (\Illuminate\Database\QueryException $exception) {
            return abort(500);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Rules\ValidEmailDomain;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\{Auth, Hash};
use Illuminate\Validation\Rules\Password;

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
                    Password::min(8)
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
            'email' => 'required|email:dns',
            'phone' => 'required|numeric',
        ]);

        $user = User::where('email', $request->email)
            ->where('phone', $request->phone)
            ->first();

        if (!$user) {
            $message = "Email dan nomor HP tidak cocok dengan data kami!";

            myFlasherBuilder(message: $message, failed: true);
            return back()->withInput();
        }

        // tandai user terverifikasi untuk reset password di session
        $request->session()->put('reset_password_user_id', $user->id);

        return redirect('/auth/reset_password');
    }

    public function resetPasswordGet(Request $request)
    {
        if (!$request->session()->has('reset_password_user_id')) {
            $message = "Silakan verifikasi akun Anda terlebih dahulu!";

            myFlasherBuilder(message: $message, failed: true);
            return redirect('/auth/forgot_password');
        }

        $title = "Reset Password";

        return view('/auth/reset_password', compact("title"));
    }

    public function resetPasswordPost(Request $request)
    {
        if (!$request->session()->has('reset_password_user_id')) {
            $message = "Silakan verifikasi akun Anda terlebih dahulu!";

            myFlasherBuilder(message: $message, failed: true);
            return redirect('/auth/forgot_password');
        }

        // Validasi dengan password rules yang kuat
        $validated = $request->validate(
            [
                'password' => [
                    'required',
                    'confirmed',
                    Password::min(8)
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

        User::where('id', $request->session()->get('reset_password_user_id'))
            ->update(['password' => Hash::make($validated['password'])]);

        $request->session()->forget('reset_password_user_id');

        $message = "Password berhasil diubah, silakan login!";

        myFlasherBuilder(message: $message, success: true);
        return redirect('/auth/login');
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

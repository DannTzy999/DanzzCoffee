<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class CustomEmailVerificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Ambil user berdasarkan ID dari URL parameter
        $user = User::find($this->route('id'));

        if (!$user) {
            return false;
        }

        // Verify hash matches dengan email
        $hash = sha1($user->getEmailForVerification());
        
        return hash_equals($hash, (string) $this->route('hash'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [];
    }

    /**
     * Fulfill the email verification.
     *
     * @return void
     */
    public function fulfill()
    {
        $user = User::find($this->route('id'));

        if ($user && !$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }
    }
}

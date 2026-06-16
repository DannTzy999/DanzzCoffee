<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidEmailDomain implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Ambil domain dari email
        $parts = explode('@', $value);
        if (count($parts) !== 2) {
            return false;
        }

        $domain = $parts[1];
        
        // Domain yang diperbolehkan
        $allowedDomains = [
            'gmail.com',
            'yahoo.com',
            'outlook.com',
            'hotmail.com',
            'yahoo.co.id',
            'gmail.co.id',
            'outlook.co.id',
        ];

        // Tambahan: domain bisnis dengan TLD yang valid
        $validTlds = ['com', 'co.id', 'net', 'org', 'edu', 'gov', 'io'];
        
        // Check apakah di dalam daftar khusus
        if (in_array($domain, $allowedDomains)) {
            return true;
        }

        // Atau check apakah domain bisnis dengan TLD valid
        foreach ($validTlds as $tld) {
            if (str_ends_with($domain, '.' . $tld)) {
                // Pastikan domain mengandung karakter yang valid (letters, numbers, dots, hyphens)
                if (preg_match('/^[a-zA-Z0-9.-]+\.' . preg_quote($tld, '/') . '$/', $domain)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Email domain tidak valid. Gunakan domain seperti @gmail.com, @yahoo.com, @outlook.com, atau domain bisnis yang valid dengan TLD .com, .co.id, .net, .org, .edu, .gov, atau .io';
    }
}

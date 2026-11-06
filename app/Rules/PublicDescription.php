<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PublicDescription implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (preg_match('/[\w.+-]+@[\w.-]+\.[a-z]{2,}|(?:\+?\d[\s().-]*){9,}|\b(?:jalan|alamat|whatsapp|rt\/rw)\b|\bjl\./iu', (string) $value)) {
            $fail('Jangan cantumkan alamat, nomor telepon, atau email pada informasi publik. Gunakan catatan pengambilan privat setelah penerima dipilih.');
        }
    }
}

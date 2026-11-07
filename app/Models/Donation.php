<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    protected $guarded = ['id'];

    public const CATEGORIES = ['Buku', 'Pakaian', 'Perabot', 'Elektronik', 'Perlengkapan anak'];

    public const CITIES = ['Jakarta', 'Bandung', 'Surabaya', 'Yogyakarta', 'Semarang', 'Medan', 'Makassar', 'Denpasar', 'Kota lainnya'];

    public const CONDITIONS = ['Seperti baru', 'Baik', 'Layak pakai'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected function casts(): array
    {
        return ['donor_confirmed' => 'boolean', 'recipient_confirmed' => 'boolean', 'completed_at' => 'datetime'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function requests()
    {
        return $this->hasMany(DonationRequest::class);
    }

    public function events()
    {
        return $this->hasMany(DonationEvent::class);
    }
}

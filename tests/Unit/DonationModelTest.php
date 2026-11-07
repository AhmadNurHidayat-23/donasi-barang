<?php

namespace Tests\Unit;

use App\Models\Donation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DonationModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_donation_belongs_to_owner_and_recipient(): void
    {
        $owner = User::factory()->create();
        $recipient = User::factory()->create();

        $donation = Donation::create([
            'slug' => (string) Str::uuid(),
            'user_id' => $owner->id,
            'recipient_id' => $recipient->id,
            'title' => 'Meja Belajar Kayu',
            'description' => 'Meja belajar kayu jati kokoh dan bersih.',
            'category' => 'Perabot',
            'condition' => 'Baik',
            'city' => 'Jakarta',
            'photo' => 'donations/table.jpg',
        ]);

        $this->assertTrue($donation->user->is($owner));
        $this->assertTrue($donation->recipient->is($recipient));
    }
}

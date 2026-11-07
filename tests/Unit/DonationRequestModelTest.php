<?php

namespace Tests\Unit;

use App\Models\Donation;
use App\Models\DonationRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DonationRequestModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_donation_request_status_helpers_and_scopes(): void
    {
        $owner = User::factory()->create();
        $applicant = User::factory()->create();

        $donation = Donation::create([
            'slug' => (string) Str::uuid(),
            'user_id' => $owner->id,
            'title' => 'Tas Ransel Sekolah',
            'description' => 'Tas ransel warna hitam masih sangat bagus dan resleting lancar.',
            'category' => 'Perlengkapan anak',
            'condition' => 'Baik',
            'city' => 'Surabaya',
            'photo' => 'donations/bag.jpg',
        ]);

        $request = DonationRequest::create([
            'donation_id' => $donation->id,
            'user_id' => $applicant->id,
            'reason' => 'Dibutuhkan untuk keperluan adik sekolah dasar tahun ajaran baru.',
            'status' => 'pending',
        ]);

        $this->assertTrue($request->isPending());
        $this->assertFalse($request->isAccepted());
        $this->assertFalse($request->isRejected());

        $request->status = 'accepted';
        $request->save();
        $this->assertTrue($request->isAccepted());
        $this->assertFalse($request->isPending());

        $this->assertCount(1, DonationRequest::accepted()->get());
        $this->assertCount(0, DonationRequest::pending()->get());
    }
}

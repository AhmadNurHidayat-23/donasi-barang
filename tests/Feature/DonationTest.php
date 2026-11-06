<?php

namespace Tests\Feature;

use App\Models\Donation;
use App\Models\DonationRequest;
use App\Models\User;
use App\Services\DonationService;
use App\Services\PhotoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class DonationTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $recipient;

    private DonationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('public');
        $this->owner = User::factory()->create(['name' => 'Nama Privat Donatur', 'email' => 'donatur-private@example.test']);
        $this->recipient = User::factory()->create();
        $this->service = new DonationService;
    }

    private function donation(): Donation
    {
        return Donation::create(['slug' => (string) Str::uuid(), 'user_id' => $this->owner->id, 'title' => 'Buku Cerita Anak', 'description' => 'Buku cerita dengan halaman lengkap dan kondisi bersih.', 'category' => 'Buku', 'condition' => 'Baik', 'city' => 'Bandung', 'photo' => 'donations/book.jpg']);
    }

    private function reserved(): Donation
    {
        $d = $this->donation();
        $this->service->apply($d, $this->recipient, 'Untuk perpustakaan kecil di komunitas belajar.');
        $this->service->select($d, $this->owner, $d->requests()->first()->id, 'accept');

        return $d->fresh();
    }

    public function test_photo_is_reencoded_and_resized_without_original_metadata(): void
    {
        $p = (new PhotoService)->store(UploadedFile::fake()->image('gambar.png', 2000, 1000));
        $bytes = Storage::disk('public')->get($p);
        $size = getimagesizefromstring($bytes);
        $this->assertSame('image/jpeg', $size['mime']);
        $this->assertSame(1600, $size[0]);
        $this->assertSame(800, $size[1]);
    }

    public function test_posting_saves_real_donation(): void
    {
        $this->actingAs($this->owner)->post('/donasi', ['title' => 'Buku cerita', 'description' => 'Buku cerita lengkap, bersih, dan masih sangat layak dibaca.', 'category' => 'Buku', 'condition' => 'Baik', 'city' => 'Bandung', 'photo' => UploadedFile::fake()->image('buku.jpg')])->assertRedirect();
        $this->assertSame(1, Donation::count());
        Storage::disk('public')->assertExists(Donation::first()->photo);
    }

    public function test_public_details_exclude_identity_and_private_data(): void
    {
        $d = $this->reserved();
        $d->update(['pickup_note' => 'Alamat privat rahasia nomor rumah 123']);
        $this->get('/donasi/'.$d->slug)->assertOk()->assertDontSee('Nama Privat Donatur')->assertDontSee('donatur-private@example.test')->assertDontSee('Alamat privat rahasia')->assertDontSee('Untuk perpustakaan');
    }

    public function test_pickup_note_only_visible_to_matched_recipient(): void
    {
        $d = $this->reserved();
        $d->update(['pickup_note' => 'Petunjuk pengambilan yang rahasia']);
        $this->actingAs($this->recipient)->get('/donasi/'.$d->slug)->assertSee('Petunjuk pengambilan yang rahasia');
        $this->actingAs(User::factory()->create())->get('/donasi/'.$d->slug)->assertDontSee('Petunjuk pengambilan yang rahasia');
    }

    public function test_only_one_recipient_can_be_selected(): void
    {
        $d = $this->donation();
        $other = User::factory()->create();
        $this->service->apply($d, $this->recipient, 'Kebutuhan belajar anak pada sore hari.');
        $this->service->apply($d, $other, 'Untuk perpustakaan di komunitas lain.');
        $first = $d->requests()->where('user_id', $this->recipient->id)->first();
        $second = $d->requests()->where('user_id', $other->id)->first();
        $this->service->select($d, $this->owner, $first->id, 'accept');
        $this->actingAs($this->owner)->patch('/donasi/'.$d->slug.'/pengajuan/'.$second->id, ['action' => 'accept'])->assertSessionHasErrors('request');
        $this->assertSame($this->recipient->id, $d->fresh()->recipient_id);
        $this->assertSame('rejected', $second->fresh()->status);
    }

    public function test_both_confirmations_required_to_complete(): void
    {
        $d = $this->reserved();
        $this->service->confirm($d, $this->owner);
        $this->assertSame('reserved', $d->fresh()->status);
        $this->service->confirm($d, $this->recipient);
        $this->assertSame('completed', $d->fresh()->status);
        $this->assertNotNull($d->fresh()->completed_at);
    }

    public function test_confirmations_cannot_be_reused(): void
    {
        $d = $this->reserved();
        $this->service->confirm($d, $this->owner);
        $this->actingAs($this->owner)->post('/donasi/'.$d->slug.'/serah-terima')->assertSessionHasErrors('handover');
        $this->assertSame('reserved', $d->fresh()->status);
    }

    public function test_unrelated_user_cannot_confirm_or_select(): void
    {
        $d = $this->reserved();
        $this->actingAs(User::factory()->create())->post('/donasi/'.$d->slug.'/serah-terima')->assertForbidden();
        $this->patch('/donasi/'.$d->slug.'/pengajuan/'.$d->requests()->first()->id, ['action' => 'accept'])->assertForbidden();
    }

    public function test_donor_cannot_apply_and_duplicate_requests_rejected(): void
    {
        $d = $this->donation();
        $this->actingAs($this->owner)->post('/donasi/'.$d->slug.'/ajukan', ['reason' => 'Saya ingin mengambil barang milik sendiri.'])->assertSessionHasErrors('request');
        $this->service->apply($d, $this->recipient, 'Buku ini akan membantu anak belajar.');
        $this->actingAs($this->recipient)->post('/donasi/'.$d->slug.'/ajukan', ['reason' => 'Mengirim pengajuan kedua untuk barang sama.'])->assertSessionHasErrors('request');
        $this->assertSame(1, DonationRequest::count());
    }

    public function test_private_contact_is_rejected_on_public_fields(): void
    {
        foreach (['Hubungi 081234567890', 'Kirim ke alamat Jalan Melati', 'Email saya@test.example'] as $description) {
            $this->actingAs($this->owner)->post('/donasi', ['title' => 'Buku anak', 'description' => $description, 'category' => 'Buku', 'condition' => 'Baik', 'city' => 'Bandung', 'photo' => UploadedFile::fake()->image('buku.jpg')])->assertSessionHasErrors('description');
        }$this->assertSame(0, Donation::count());
    }

    public function test_reopening_clears_previous_recipient_and_instructions(): void
    {
        $d = $this->reserved();
        $d->update(['pickup_note' => 'Petunjuk lama harus dihapus']);
        $this->service->reopen($d, $this->owner);
        $this->assertSame('available', $d->fresh()->status);
        $this->assertNull($d->fresh()->recipient_id);
        $this->assertNull($d->fresh()->pickup_note);
    }

    public function test_reopening_after_handover_confirmation_is_blocked(): void
    {
        $d = $this->reserved();
        $this->service->confirm($d, $this->owner);
        $this->actingAs($this->owner)->post('/donasi/'.$d->slug.'/buka-kembali')->assertSessionHasErrors('handover');
    }

    public function test_catalog_filters_real_available_items(): void
    {
        $a = $this->donation();
        $this->get('/?category=Buku&city=Bandung&q=Cerita')->assertSee('Buku Cerita Anak');
        $this->get('/?city=Jakarta')->assertDontSee('Buku Cerita Anak');
        $a->update(['status' => 'completed']);
        $this->get('/')->assertDontSee('Buku Cerita Anak');
    }

    public function test_invalid_photo_rejected(): void
    {
        $this->actingAs($this->owner)->post('/donasi', ['title' => 'Buku anak', 'description' => 'Buku cerita dengan halaman lengkap dan layak baca.', 'category' => 'Buku', 'condition' => 'Baik', 'city' => 'Bandung', 'photo' => UploadedFile::fake()->create('kode.php', 1, 'application/x-php')])->assertSessionHasErrors('photo');
    }

    public function test_history_is_private_to_account(): void
    {
        $d = $this->donation();
        $this->actingAs($this->recipient)->get('/riwayat')->assertDontSee($d->title);
        $this->actingAs($this->owner)->get('/riwayat')->assertSee($d->title);
    }
}

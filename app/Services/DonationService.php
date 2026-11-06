<?php

namespace App\Services;

use App\Models\Donation;
use App\Models\DonationRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DonationService
{
    private function event(Donation $d, User $u, string $text): void
    {
        $d->events()->create(['actor_id' => $u->id, 'description' => $text]);
    }

    public function apply(Donation $donation, User $user, string $reason): void
    {
        DB::transaction(function () use ($donation, $user, $reason) {
            $d = Donation::lockForUpdate()->findOrFail($donation->id);
            if ($d->user_id === $user->id || $d->status !== 'available') {
                throw ValidationException::withMessages(['request' => 'Barang tidak dapat diajukan.']);
            }if (DonationRequest::where('donation_id', $d->id)->where('user_id', $user->id)->exists()) {
                throw ValidationException::withMessages(['request' => 'Anda sudah pernah mengajukan barang ini.']);
            }$d->requests()->create(['user_id' => $user->id, 'reason' => $reason]);
            $this->event($d, $user, 'Pengajuan penerimaan dibuat.');
        }, 3);
    }

    public function select(Donation $donation, User $owner, int $id, string $action): void
    {
        DB::transaction(function () use ($donation, $owner, $id, $action) {
            $d = Donation::lockForUpdate()->findOrFail($donation->id);
            abort_unless($d->user_id === $owner->id, 403);
            $r = $d->requests()->lockForUpdate()->findOrFail($id);
            if ($d->status !== 'available' || $r->status !== 'pending') {
                throw ValidationException::withMessages(['request' => 'Barang atau pengajuan sudah diproses.']);
            }if ($action === 'accept') {
                $d->update(['status' => 'reserved', 'recipient_id' => $r->user_id]);
                $r->update(['status' => 'accepted']);
                $d->requests()->whereKeyNot($r->id)->where('status', 'pending')->update(['status' => 'rejected']);
                $this->event($d, $owner, 'Penerima dipilih. Barang dicadangkan untuk serah terima.');
            } else {
                $r->update(['status' => 'rejected']);
                $this->event($d, $owner, 'Pengajuan ditolak donatur.');
            }
        }, 3);
    }

    public function confirm(Donation $donation, User $actor): void
    {
        DB::transaction(function () use ($donation, $actor) {
            $d = Donation::lockForUpdate()->findOrFail($donation->id);
            abort_unless(in_array($actor->id, [$d->user_id, $d->recipient_id], true), 403);
            if ($d->status !== 'reserved') {
                throw ValidationException::withMessages(['handover' => 'Serah terima sudah selesai atau belum dapat diproses.']);
            }$field = $actor->id === $d->user_id ? 'donor_confirmed' : 'recipient_confirmed';
            if ($d->$field) {
                throw ValidationException::withMessages(['handover' => 'Konfirmasi Anda sudah tercatat.']);
            }$d->$field = true;
            if ($d->donor_confirmed && $d->recipient_confirmed) {
                $d->status = 'completed';
                $d->completed_at = now();
            }$d->save();
            $this->event($d, $actor, $field === 'donor_confirmed' ? 'Donatur mengonfirmasi penyerahan barang.' : 'Penerima mengonfirmasi barang diterima.');
        }, 3);
    }

    public function reopen(Donation $donation, User $owner): void
    {
        DB::transaction(function () use ($donation, $owner) {
            $d = Donation::lockForUpdate()->findOrFail($donation->id);
            abort_unless($d->user_id === $owner->id, 403);
            if ($d->status !== 'reserved' || $d->donor_confirmed || $d->recipient_confirmed) {
                throw ValidationException::withMessages(['handover' => 'Tidak dapat membuka kembali setelah konfirmasi serah terima.']);
            }$d->requests()->where('status', 'accepted')->update(['status' => 'cancelled']);
            $d->update(['status' => 'available', 'recipient_id' => null, 'pickup_note' => null]);
            $this->event($d,$owner,'Pencadangan dibatalkan; barang tersedia kembali.');
        }, 3);
    }
}

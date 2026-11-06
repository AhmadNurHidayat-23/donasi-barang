<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\DonationRequest;
use App\Rules\PublicDescription;
use App\Services\DonationService;
use App\Services\PhotoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DonationController extends Controller
{
    public function index(Request $r)
    {
        $f = $r->validate(['q' => 'nullable|string|max:80', 'category' => ['nullable', Rule::in(Donation::CATEGORIES)], 'city' => ['nullable', Rule::in(Donation::CITIES)]]);
        $donations = Donation::where('status', 'available')->when($f['q'] ?? null, fn ($q, $s) => $q->where(fn ($q) => $q->where('title', 'like', '%'.$s.'%')->orWhere('description', 'like', '%'.$s.'%')))->when($f['category'] ?? null, fn ($q, $s) => $q->where('category', $s))->when($f['city'] ?? null, fn ($q, $s) => $q->where('city', $s))->latest()->paginate(12)->withQueryString();

        return view('home', compact('donations', 'f'));
    }

    public function create()
    {
        return view('create');
    }

    public function store(Request $r, PhotoService $photos)
    {
        $d = $r->validate(['title' => ['required', 'string', 'max:90', new PublicDescription], 'description' => ['required', 'string', 'min:20', 'max:1000', new PublicDescription], 'category' => ['required', Rule::in(Donation::CATEGORIES)], 'condition' => ['required', Rule::in(Donation::CONDITIONS)], 'city' => ['required', Rule::in(Donation::CITIES)], 'photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:4096|dimensions:max_width=4000,max_height=4000']);
        $path = $photos->store($r->file('photo'));
        unset($d['photo']);
        try {
            $donation = Donation::create($d + ['slug' => (string) Str::uuid(), 'user_id' => $r->user()->id, 'photo' => $path]);
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($path);
            throw $e;
        }

return redirect()->route('donation.show', $donation)->with('success', 'Donasi dipublikasikan. Informasi kontak Anda tidak ditampilkan.');
    }

    public function show(Request $r, Donation $donation)
    {
        $owner = $r->user()?->id === $donation->user_id;
        $participant = $owner || ($r->user() && $r->user()->id === $donation->recipient_id);
        $applications = $owner ? $donation->requests()->with('user:id,name')->latest()->get() : collect();
        $own = $r->user() ? $donation->requests()->where('user_id', $r->user()->id)->first() : null;
        $events = $participant ? $donation->events()->oldest()->get() : collect();

        return view('show', compact('donation', 'owner', 'participant', 'applications', 'own', 'events'));
    }

    public function apply(Request $r, Donation $donation, DonationService $s)
    {
        $d = $r->validate(['reason' => 'required|string|min:20|max:500']);
        $s->apply($donation, $r->user(), $d['reason']);

        return back()->with('success', 'Pengajuan dikirim secara privat kepada donatur.');
    }

    public function review(Request $r, Donation $donation, int $id, DonationService $s)
    {
        $d = $r->validate(['action' => 'required|in:accept,reject']);
        $s->select($donation, $r->user(), $id, $d['action']);

        return back()->with('success', 'Pengajuan diperbarui.');
    }

    public function pickup(Request $r, Donation $donation)
    {
        abort_unless($r->user()->id === $donation->user_id && $donation->status === 'reserved', 403);
        $d = $r->validate(['pickup_note' => 'required|string|min:10|max:1000']);
        $donation->update($d);

        return back()->with('success', 'Catatan privat disimpan untuk penerima terpilih.');
    }

    public function handover(Request $r, Donation $donation, DonationService $s)
    {
        $s->confirm($donation, $r->user());

        return back()->with('success', 'Konfirmasi serah terima dicatat. Selesai setelah kedua pihak mengonfirmasi.');
    }

    public function reopen(Request $r, Donation $donation, DonationService $s)
    {
        $s->reopen($donation, $r->user());

        return back()->with('success', 'Barang tersedia kembali.');
    }

    public function history(Request $r)
    {
        $donations = Donation::where('user_id', $r->user()->id)->latest()->paginate(10, ['*'], 'donasi_page');
        $applications = DonationRequest::with('donation')->where('user_id',$r->user()->id)->latest()->paginate(10,['*'],'pengajuan_page');

        return view('history',compact('donations','applications'));
    }
}

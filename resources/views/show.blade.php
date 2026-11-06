@extends('layouts.app')
@section('title',$donation->title)
@section('content')
@php($statusLabels=['available'=>'Tersedia','reserved'=>'Dicadangkan','completed'=>'Selesai diserahterimakan'])
<section class="workspace">
<a class="back-link" href="/#jelajah">← Kembali ke donasi</a>
<div class="detail-layout">
<div class="item-photo">
<img src="{{ asset('storage/'.$donation->photo) }}" alt="{{ $donation->title }}">
</div>
<div class="item-info">
<span class="eyebrow">{{ $donation->category }} / {{ $donation->city }}</span>
<span class="badge {{ $donation->status }}">{{ $statusLabels[$donation->status] }}</span>
<h1>{{ $donation->title }}</h1>
<p class="condition">{{ $donation->condition }} · Gratis untuk diterima</p>
<p class="item-description">{{ $donation->description }}</p>
<p class="privacy-note">Lokasi hanya ditampilkan pada tingkat kota. Identitas dan kontak donatur tidak tersedia untuk publik.</p>
@if($owner)<a class="btn secondary full" href="#pengajuan">Kelola pengajuan ({{ $applications->count() }}) ↓</a>
@elseif($own)<div class="notice">Status pengajuan Anda: <strong>{{ ['pending'=>'Menunggu donatur','accepted'=>'Diterima','rejected'=>'Belum terpilih','cancelled'=>'Pencadangan dibatalkan'][$own->status] }}</strong>
</div>
@elseif($donation->status==='available')@auth<form action="{{ route('donation.show',$donation) }}/ajukan" method="post">
@csrf
<label>Ceritakan kebutuhan Anda<textarea name="reason" required minlength="20" maxlength="500" rows="3" placeholder="Alasan pengajuan ini hanya dilihat donatur.">{{ old('reason') }}</textarea>
</label>
<button class="btn primary full" type="submit">Ajukan penerimaan barang</button>
</form>
@else
<a class="btn primary full" href="/masuk">Masuk untuk mengajukan barang</a>
@endauth
@endif
</div>
</div>
@if($owner)<section class="applications" id="pengajuan">
<div class="section-heading">
<div>
<span class="eyebrow">HANYA UNTUK DONATUR</span>
<h2>Pengajuan yang masuk.</h2>
</div>
</div>
@forelse($applications as $application)<article class="application">
<div>
<h3>{{ $application->user->name }}</h3>
<p>{{ $application->reason }}</p>
<small>{{ ['pending'=>'Menunggu','accepted'=>'Diterima','rejected'=>'Ditolak','cancelled'=>'Dibatalkan'][$application->status] }}</small>
</div>
@if($application->status==='pending'&&$donation->status==='available')<form action="{{ route('donation.show',$donation) }}/pengajuan/{{ $application->id }}" method="post">
@csrf @method('PATCH')<button class="btn primary small" name="action" value="accept" type="submit">Pilih penerima</button>
<button class="btn secondary small" name="action" value="reject" type="submit">Tolak</button>
</form>
@endif
</article>
@empty
<div class="empty">
<h3>Belum ada pengajuan.</h3>
<p>Barang Anda sudah dapat ditemukan melalui katalog.</p>
</div>
@endforelse
</section>
@endif
@if($participant&&in_array($donation->status,['reserved','completed']))<section class="private-panel">
<span class="eyebrow">RUANG SERAH TERIMA / PRIVAT</span>
<h2>Saling sepakat. Saling konfirmasi.</h2>
@if($donation->pickup_note)<div class="pickup-note">
<strong>Petunjuk pengambilan</strong>
<p>{{ $donation->pickup_note }}</p>
</div>
@elseif(!$owner)<p>Donatur belum menambahkan petunjuk pengambilan. Periksa kembali halaman ini.</p>
@endif
@if($owner&&$donation->status==='reserved')<form action="{{ route('donation.show',$donation) }}/pengambilan" method="post">
@csrf
<label>Petunjuk pengambilan privat<textarea name="pickup_note" required minlength="10" maxlength="1000" rows="3">{{ old('pickup_note',$donation->pickup_note) }}</textarea>
</label>
<button class="btn secondary" type="submit">Simpan petunjuk privat</button>
</form>
@endif
<div class="handover-state">
<span>{{ $donation->donor_confirmed?'✓':'○' }} Konfirmasi donatur</span>
<span>{{ $donation->recipient_confirmed?'✓':'○' }} Konfirmasi penerima</span>
</div>
@if($donation->status==='reserved'&&!(($owner&&$donation->donor_confirmed)||(!$owner&&$donation->recipient_confirmed)))<form action="{{ route('donation.show',$donation) }}/serah-terima" method="post">
@csrf
<p>Tekan hanya setelah barang benar-benar {{ $owner?'diserahkan':'diterima' }}.</p>
<button class="btn primary" type="submit">{{ $owner?'Saya sudah menyerahkan barang':'Saya sudah menerima barang' }}</button>
</form>
@endif
@if($owner&&$donation->status==='reserved'&&!$donation->donor_confirmed&&!$donation->recipient_confirmed)<form action="{{ route('donation.show',$donation) }}/buka-kembali" method="post">
@csrf
<button class="text-btn" type="submit">Batalkan pencadangan dan buka kembali</button>
</form>
@endif
<div class="timeline">
@foreach($events as $event)<p>{{ $event->description }}<small>{{ $event->created_at->format('d/m/Y H:i') }}</small>
</p>
@endforeach
</div>
</section>
@endif
</section>
@endsection


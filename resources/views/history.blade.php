@extends('layouts.app')
@section('title','Riwayat berbagi')
@section('content')
<section class="workspace">
<div class="section-heading">
<div>
<span class="eyebrow">RUANG PRIBADI / {{ auth()->user()->name }}</span>
<h1>Kebaikan yang berlanjut.</h1>
<p>Lihat barang yang dibagikan dan perkembangan pengajuan Anda.</p>
</div>
<a class="btn primary" href="/donasi/baru">Bagikan barang baru ↗</a>
</div>
<h2 class="history-heading">Donasi saya</h2>
<div class="history-list">
@forelse($donations as $d)<a href="{{ route('donation.show',$d) }}">
<div>
<strong>{{ $d->title }}</strong>
<small>{{ $d->city }} · {{ $d->created_at->format('d/m/Y') }}</small>
</div>
<span class="badge {{ $d->status }}">{{ ['available'=>'Tersedia','reserved'=>'Dicadangkan','completed'=>'Selesai'][$d->status] }}</span>
<span>↗</span>
</a>
@empty
<div class="empty">
<h3>Belum ada donasi yang Anda bagikan.</h3>
<p>Mulai dari satu barang yang masih bermanfaat.</p>
</div>
@endforelse
</div>{{ $donations->links() }}<h2 class="history-heading">Pengajuan saya</h2>
<div class="history-list">
@forelse($applications as $a)<a href="{{ route('donation.show',$a->donation) }}">
<div>
<strong>{{ $a->donation->title }}</strong>
<small>{{ $a->created_at->format('d/m/Y') }}</small>
</div>
<span class="badge">{{ ['pending'=>'Menunggu','accepted'=>'Diterima','rejected'=>'Belum terpilih','cancelled'=>'Dibatalkan'][$a->status] }}</span>
<span>↗</span>
</a>
@empty
<div class="empty">
<h3>Belum ada pengajuan.</h3>
<a class="text-link" href="/#jelajah">Jelajahi barang yang tersedia ↗</a>
</div>
@endforelse
</div>{{ $applications->links() }}</section>
@endsection


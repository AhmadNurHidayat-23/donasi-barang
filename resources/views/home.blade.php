@extends('layouts.app')
@section('title','Beri ruang untuk cerita baru')
@section('content')
<section class="hero">
<div>
<span class="eyebrow">PILIH UNTUK BERBAGI</span>
<h1>Masih berguna.<br/>Bisa <em>berarti lagi.</em>
</h1>
<p>Yang tak lagi Anda pakai, mungkin sedang dibutuhkan orang lain. Temukan rumah baru untuk barang yang masih layak.</p>
<div class="hero-actions">
<a class="btn primary" href="/donasi/baru">Mulai berbagi <span>↗</span>
</a>
<a class="text-link" href="#jelajah">Temukan barang ↓</a>
</div>
<div class="hero-tags">
<span>TANPA BIAYA BARANG</span>
<span>LOKASI UMUM SAJA</span>
<span>SERAH TERIMA DISKUSIKAN</span>
</div>
</div>
<figure>
<img src="/images/giving.jpg" alt="Koleksi buku yang bisa kembali bermanfaat untuk pembaca baru">
<figcaption>SETIAP BARANG PUNYA KESEMPATAN KEDUA.</figcaption>
</figure>
</section>
<section class="catalog" id="jelajah">
<div class="section-heading">
<div>
<span class="eyebrow">JELAJAHI YANG TERSEDIA</span>
<h2>Temukan manfaat berikutnya.</h2>
</div>
<span class="count">{{ $donations->total() }} barang tersedia</span>
</div>
<form class="filters" method="get" action="/#jelajah">
<label>Cari barang<input name="q" maxlength="80" value="{{ $f['q']??'' }}" placeholder="Buku, pakaian, perlengkapan…">
</label>
<label>Kategori<select name="category">
<option value="">Semua kategori</option>
@foreach(\App\Models\Donation::CATEGORIES as $category)<option @selected(($f['category']??'')===$category)>{{ $category }}</option>
@endforeach
</select>
</label>
<label>Kota<select name="city">
<option value="">Semua kota</option>
@foreach(\App\Models\Donation::CITIES as $city)<option @selected(($f['city']??'')===$city)>{{ $city }}</option>
@endforeach
</select>
</label>
<button class="btn primary" type="submit">Cari donasi</button>
</form>
<div class="donation-grid">
@foreach($donations as $item)@include('partials.card',['item'=>$item])@endforeach
</div>
@if($donations->isEmpty())<div class="empty">
<span class="empty-symbol">↗</span>
<h3>Ruang untuk kebaikan pertama.</h3>
<p>Belum ada barang yang cocok. Coba filter lain atau mulai bagikan barang Anda.</p>
<a class="btn secondary" href="/donasi/baru">Bagikan barang layak pakai</a>
</div>
@endif{{ $donations->links() }}</section>
<section class="how">
<div>
<span class="eyebrow">SEDERHANA, DENGAN PERHATIAN</span>
<h2>Tiga langkah,<br/>manfaat berlanjut.</h2>
</div>
<ol>
<li>
<strong>Bagikan yang layak</strong>
<p>Foto barang, jelaskan kondisinya dengan jujur, dan pilih kota.</p>
</li>
<li>
<strong>Pilih penerima</strong>
<p>Baca pengajuan secara privat. Petunjuk pengambilan hanya untuk penerima terpilih.</p>
</li>
<li>
<strong>Konfirmasi bersama</strong>
<p>Donatur dan penerima mengonfirmasi serah terima agar riwayatnya jelas.</p>
</li>
</ol>
</section>
@endsection


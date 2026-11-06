@extends('layouts.app')
@section('title','Bagikan barang')
@section('content')
<section class="workspace">
<div class="section-heading">
<div>
<span class="eyebrow">DONASI BARU / BARANG LAYAK PAKAI</span>
<h1>Beri ruang. Beri manfaat.</h1>
<p>Ceritakan kondisi barang dengan jujur. Jangan cantumkan alamat, kontak pribadi, atau foto yang memuat data sensitif.</p>
</div>
</div>
<form class="posting-layout" action="/donasi" method="post" enctype="multipart/form-data">
@csrf
<div class="panel">
<h2>Detail yang perlu diketahui.</h2>
<label>Nama barang<input name="title" required maxlength="90" value="{{ old('title') }}" placeholder="Contoh: Buku cerita anak, 5 buku">
</label>
<label>Deskripsi & kekurangan<textarea name="description" required minlength="20" maxlength="1000" rows="5" placeholder="Kelengkapan, ukuran, serta kekurangan barang. Tanpa alamat atau nomor kontak.">{{ old('description') }}</textarea>
</label>
<div class="form-grid">
<label>Kategori<select name="category" required>
<option value="">Pilih kategori</option>
@foreach(\App\Models\Donation::CATEGORIES as $c)<option @selected(old('category')===$c)>{{ $c }}</option>
@endforeach
</select>
</label>
<label>Kondisi barang<select name="condition" required>
<option value="">Pilih kondisi</option>
@foreach(\App\Models\Donation::CONDITIONS as $c)<option @selected(old('condition')===$c)>{{ $c }}</option>
@endforeach
</select>
</label>
</div>
<label>Kota (informasi umum)<select name="city" required>
<option value="">Pilih kota</option>
@foreach(\App\Models\Donation::CITIES as $c)<option @selected(old('city')===$c)>{{ $c }}</option>
@endforeach
</select>
</label>
<label class="upload">Foto barang<input name="photo" type="file" required accept="image/jpeg,image/png,image/webp">
<small>JPG/PNG/WebP · Maksimal 4 MB dan 4000 × 4000 piksel. Hindari foto label alamat atau dokumen pribadi.</small>
</label>
<button class="btn primary full" type="submit">Publikasikan donasi</button>
</div>
<aside class="guidance">
<span class="eyebrow">BERBAGI DENGAN NYAMAN</span>
<h2>Layak pakai,<br/>layak dihargai.</h2>
<ul>
<li>Pastikan barang bersih dan dapat digunakan.</li>
<li>Jelaskan kerusakan kecil atau kelengkapan yang hilang.</li>
<li>Informasi pengambilan dibagikan secara privat setelah penerima dipilih.</li>
<li>Metadata gambar dibuang saat foto disimpan.</li>
</ul>
<p>Donasi berarti barang diberikan tanpa biaya. Sepakati pengambilan langsung bersama penerima.</p>
</aside>
</form>
</section>
@endsection


@extends('layouts.app')
@section('title','Masuk dan daftar')
@section('content')
@php($register=request('mode')==='daftar')
<section class="auth-layout">
<div>
<span class="eyebrow">KOMUNITAS BERBAGI</span>
<h1>Lebih sedikit menumpuk.<br/>
<em>Lebih banyak berarti.</em>
</h1>
<p>Buat akun untuk berbagi, mengajukan barang, dan mengatur serah terima secara privat.</p>
<img src="/images/giving.jpg" alt="Buku-buku siap menemukan pembaca baru">
</div>
<div class="auth-panel">
<span class="eyebrow">RUANG ANGGOTA</span>
<h2>{{ $register?'Mari mulai berbagi.':'Selamat datang kembali.' }}</h2>
<form method="post" action="{{ $register?'/daftar':'/masuk' }}">
@csrf
@if($register)<label>Nama lengkap<input name="name" required maxlength="80" autocomplete="name" value="{{ old('name') }}">
</label>
@endif
<label>Email<input type="email" name="email" required maxlength="150" autocomplete="username" value="{{ old('email') }}">
</label>
<label>Kata sandi<input type="password" name="password" required @if($register) minlength="12" autocomplete="new-password" @else autocomplete="current-password" @endif>
</label>
@if($register)<label>Ulangi kata sandi<input type="password" name="password_confirmation" required minlength="12" autocomplete="new-password">
</label>
@endif
<button class="btn primary full" type="submit">{{ $register?'Daftar anggota':'Masuk' }}</button>
</form>
<a class="text-link auth-switch" href="{{ $register?'/masuk':'/masuk?mode=daftar' }}">{{ $register?'Sudah punya akun? Masuk':'Belum punya akun? Daftar' }}</a>
<small>Nama dan email akun tidak dipublikasikan pada katalog barang.</small>
</div>
</section>
@endsection


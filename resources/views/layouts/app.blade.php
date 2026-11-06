<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>@yield('title','Donasi Barang') · Beri Lagi</title>@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body>
<header>
<a class="brand" href="/">beri lagi<span>BARANG BAIK, CERITA BARU</span>
</a>
<nav>
<a href="/#jelajah">Jelajahi donasi</a>
@auth<a href="/riwayat">Riwayat saya</a>
<a class="btn primary small" href="/donasi/baru">Bagikan barang</a>
<form method="post" action="/keluar">
@csrf
<button class="text-btn" type="submit">Keluar</button>
</form>
@else
<a class="btn secondary small" href="/masuk">Masuk / Daftar</a>
@endauth
</nav>
</header>
@if(session('success'))<div role="status" class="notice success">{{ session('success') }}</div>
@endif
@if($errors->any())<div role="alert" class="notice error">
@foreach($errors->all() as $error)<p>{{ $error }}</p>
@endforeach
</div>
@endif
<main>@yield('content')</main>
<footer>
<strong>beri lagi.</strong>
<p>Barang berguna tidak harus berakhir di gudang.</p>
<small>Serah terima saling setuju · Informasi pribadi tetap privat</small>
</footer>
</body>
</html>


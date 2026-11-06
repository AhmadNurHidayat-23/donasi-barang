<article class="donation-card">
<a href="{{ route('donation.show',$item) }}">
<img src="{{ asset('storage/'.$item->photo) }}" alt="{{ $item->title }}" loading="lazy">
</a>
<div>
<span class="eyebrow">{{ $item->category }} / {{ $item->city }}</span>
<h3>
<a href="{{ route('donation.show',$item) }}">{{ $item->title }}</a>
</h3>
<p>{{ $item->condition }}</p>
<div class="card-bottom">
<strong>Gratis untuk diterima</strong>
<a aria-label="Lihat {{ $item->title }}" href="{{ route('donation.show',$item) }}">↗</a>
</div>
</div>
</article>


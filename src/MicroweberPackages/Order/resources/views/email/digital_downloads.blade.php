<p>Thanks for your purchase. Your digital download link{{ count($downloads) === 1 ? '' : 's' }} {{ count($downloads) === 1 ? 'is' : 'are' }} ready:</p>

<ul>
@foreach ($downloads as $download)
    @php
        $productTitle = $download->product ? $download->product->title : 'Product';
        $downloadUrl = route('digital.download', ['token' => $download->token]);
    @endphp
    <li>
        <strong>{{ $productTitle }}</strong><br>
        <a href="{{ $downloadUrl }}">{{ $downloadUrl }}</a>
        @if ($download->expires_at)
            <br><small>Expires on {{ $download->expires_at->format('Y-m-d') }}</small>
        @endif
        @if ($download->max_downloads)
            <br><small>Max downloads: {{ $download->max_downloads }}</small>
        @endif
    </li>
@endforeach
</ul>

<p>Order #{{ $order->id }}</p>

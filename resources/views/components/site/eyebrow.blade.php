@props(['num' => null, 'text', 'action' => null, 'href' => null, 'speed' => false])

<div class="eyebrow {{ $speed ? 'eyebrow--speed' : '' }}">
    @if($num)<span class="eyebrow__num">{{ $num }}</span>@endif
    <span>{{ $text }}</span>
    @if($action && $href)
        <a href="{{ $href }}" class="eyebrow__more">{{ $action }} →</a>
    @endif
</div>

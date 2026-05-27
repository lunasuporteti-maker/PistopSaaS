{{-- x-avatar: iniciais do nome com cor derivada automaticamente
     Uso: <x-avatar name="João Silva" />
     Uso: <x-avatar name="João Silva" size="sm" />
     Sizes: sm (24px) | md (32px, padrão) | lg (40px) --}}
@props(['name' => '?', 'size' => 'md'])

@php
    // Extrai iniciais (máximo 2)
    $parts    = array_filter(explode(' ', trim($name)));
    $initials = strtoupper(substr($parts[0] ?? '?', 0, 1));
    if (count($parts) > 1) $initials .= strtoupper(substr(end($parts), 0, 1));

    // Cor determinística baseada no nome
    $palette = ['#e07b39','#3b82f6','#10b981','#8b5cf6','#ef4444','#f59e0b','#06b6d4'];
    $color   = $palette[crc32($name) % count($palette)];

    $sizes = ['sm' => 24, 'md' => 32, 'lg' => 40];
    $px    = $sizes[$size] ?? 32;
    $fs    = (int)($px * 0.38);
@endphp

<span style="
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: {{ $px }}px;
    height: {{ $px }}px;
    border-radius: 50%;
    background: {{ $color }};
    color: #fff;
    font-size: {{ $fs }}px;
    font-weight: 600;
    font-family: var(--font-sans);
    flex-shrink: 0;
    user-select: none;
" title="{{ $name }}">{{ $initials }}</span>

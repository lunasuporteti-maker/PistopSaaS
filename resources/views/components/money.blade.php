{{-- x-money: valor monetário formatado em pt-BR com fonte tabular
     Uso: <x-money :value="$os->valor_total" />
     Uso: <x-money :value="-150.50" :signed="true" />  →  −R$ 150,50 --}}
@props(['value' => 0, 'signed' => false, 'mono' => true])

@php
    $abs  = abs($value);
    $sign = $signed ? ($value < 0 ? '−' : '+') : ($value < 0 ? '−' : '');
@endphp

<span class="{{ $mono ? 'mono tabnum' : 'tabnum' }}">{{ $sign }}R$&nbsp;{{ number_format($abs, 2, ',', '.') }}</span>

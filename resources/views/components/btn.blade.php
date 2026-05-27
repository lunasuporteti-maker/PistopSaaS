{{-- x-btn: botão com variantes do design system
     Uso:
       <x-btn>Padrão</x-btn>
       <x-btn variant="primary">Salvar</x-btn>
       <x-btn variant="ghost" size="sm">Cancelar</x-btn>
       <x-btn variant="danger">Excluir</x-btn>
     Variants: default · primary · ghost · danger
     Sizes:    (padrão) · sm · lg · icon
     Todos os atributos HTML passam direto (type, href, wire:click, etc.) --}}
@props(['variant' => 'default', 'size' => null])

@php
    $classes = 'btn';
    if ($variant !== 'default') $classes .= ' btn-' . $variant;
    if ($size)                  $classes .= ' btn-' . $size;
@endphp

<button {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</button>

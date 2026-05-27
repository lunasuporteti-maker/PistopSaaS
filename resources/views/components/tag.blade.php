{{-- x-tag: chip de status com ponto colorido
     Uso: <x-tag variant="success">Concluído</x-tag>
     Uso: <x-tag variant="warning" :dot="false">Em Serviço</x-tag>
     Variants: default · success · warning · danger · info · brand --}}
@props(['variant' => 'default', 'dot' => true])

<span class="tag {{ $variant !== 'default' ? 'tag-'.$variant : '' }} {{ $dot ? '' : 'no-dot' }}">
    {{ $slot }}
</span>

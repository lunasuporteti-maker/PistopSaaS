{{-- x-card: card com header opcional e slot de ações
     Uso simples:
       <x-card title="Título">conteúdo</x-card>

     Com ações no header:
       <x-card title="Título">
           <x-slot:actions>
               <button class="btn btn-sm">Ver tudo</button>
           </x-slot:actions>
           conteúdo
       </x-card>

     Sem padding interno (tabela, etc.):
       <x-card title="Tabela" :flush="true">
           <table>...</table>
       </x-card> --}}
@props(['title' => null, 'flush' => false])

<div class="card">
    @if ($title || isset($actions))
        <div class="card-header">
            @if ($title)
                <h3 class="card-title">{{ $title }}</h3>
            @endif
            @isset($actions)
                <div class="row-flex">{{ $actions }}</div>
            @endisset
        </div>
    @endif

    <div class="card-body {{ $flush ? 'flush' : '' }}">
        {{ $slot }}
    </div>
</div>

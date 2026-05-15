@extends('adminlte::page')
@section('title', 'Orçamentos')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 font-weight-bold"><i class="fas fa-file-invoice mr-2 text-danger"></i>Orçamentos</h1>
        <small class="text-muted">{{ $orcamentos->total() }} resultado(s)</small>
    </div>
    <a href="{{ route('orcamentos.create') }}" class="btn btn-danger btn-sm px-3">
        <i class="fas fa-plus mr-1"></i> Novo Orçamento
    </a>
</div>
@endsection

@section('content')
@include('pitstop._partials.alerts')

@php
$statusConfig = [
    'orcamento'           => ['label' => 'Orçamento',      'class' => 'badge-secondary'],
    'aguardando_aprovacao'=> ['label' => 'Ag. Aprovação',  'class' => 'badge-info'],
    'aprovado'            => ['label' => 'Aprovado',       'class' => 'badge-primary'],
    'em_servico'          => ['label' => 'Em Serviço',     'class' => 'badge-warning'],
    'concluido'           => ['label' => 'Concluído',      'class' => 'badge-success'],
    'fechado'             => ['label' => 'Concluído',      'class' => 'badge-success'],
    'cancelado'           => ['label' => 'Cancelado',      'class' => 'badge-danger'],
];
@endphp

<div class="card shadow-sm">
    <div class="card-header py-2">
        <div class="d-flex align-items-center flex-wrap gap-2" style="gap:6px">
            <form method="GET" class="d-flex align-items-center mr-2" style="gap:6px">
                <div class="input-group input-group-sm" style="max-width:260px">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-search text-muted"></i></span>
                    </div>
                    <input type="text" name="search" class="form-control"
                           placeholder="Cliente, veículo ou placa..."
                           value="{{ request('search') }}">
                    @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
                </div>
                <button class="btn btn-sm btn-danger">Buscar</button>
                @if(request()->hasAny(['search','status']))
                <a href="{{ route('orcamentos.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times"></i></a>
                @endif
            </form>

            <div class="d-flex flex-wrap" style="gap:4px">
                @foreach($statusConfig as $s => $cfg)
                <a href="{{ route('orcamentos.index', array_filter(['status'=>$s,'search'=>request('search')])) }}"
                   class="btn btn-xs {{ request('status')===$s ? 'btn-dark' : 'btn-outline-secondary' }}">
                    {{ $cfg['label'] }}
                </a>
                @endforeach
                <a href="{{ route('orcamentos.index', array_filter(['search'=>request('search')])) }}"
                   class="btn btn-xs {{ !request('status') ? 'btn-dark' : 'btn-outline-secondary' }}">Todos</a>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th class="pl-3">#</th>
                    <th>Cliente</th>
                    <th>Veículo</th>
                    <th>Valor</th>
                    <th>Status</th>
                    <th>Data</th>
                    <th class="text-right pr-3" width="100">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orcamentos as $orc)
                @php $sc = $statusConfig[$orc->status] ?? ['label'=>ucfirst($orc->status),'class'=>'badge-secondary'] @endphp
                <tr>
                    <td class="pl-3 text-muted">#{{ $orc->id }}</td>
                    <td>
                        <a href="{{ route('clientes.show', $orc->cliente) }}" class="font-weight-600 text-dark">
                            {{ $orc->cliente->nome }}
                        </a>
                    </td>
                    <td>
                        {{ $orc->veiculo->marca }} {{ $orc->veiculo->modelo }}
                        <span class="badge badge-secondary ml-1" style="font-size:.7rem">{{ $orc->veiculo->placa }}</span>
                    </td>
                    <td class="font-weight-600">R$ {{ number_format($orc->valor_total, 2, ',', '.') }}</td>
                    <td><span class="badge {{ $sc['class'] }}">{{ $sc['label'] }}</span></td>
                    <td class="text-muted">{{ $orc->created_at->format('d/m/Y') }}</td>
                    <td class="text-right pr-3">
                        <a href="{{ route('orcamentos.show', $orc) }}" class="btn btn-xs btn-outline-primary" title="Ver"><i class="fas fa-eye"></i></a>
                        <a href="{{ route('orcamentos.edit', $orc) }}" class="btn btn-xs btn-outline-secondary" title="Editar"><i class="fas fa-edit"></i></a>
                        <form method="POST" action="{{ route('orcamentos.destroy', $orc) }}" class="d-inline"
                              onsubmit="return confirm('Excluir orçamento #{{ $orc->id }}?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-xs btn-outline-danger" title="Excluir"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-5">
                        <i class="fas fa-file-invoice fa-2x d-block mb-2 text-light"></i>
                        Nenhum orçamento encontrado.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($orcamentos->hasPages())
    <div class="card-footer">{{ $orcamentos->withQueryString()->links() }}</div>
    @endif
</div>
@endsection

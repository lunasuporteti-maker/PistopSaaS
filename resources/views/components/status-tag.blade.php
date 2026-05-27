{{-- x-status-tag: converte status de OS/Orçamento em x-tag automaticamente
     Uso: <x-status-tag :status="$os->status" />
     Suporta status de Ordens de Serviço e Orçamentos --}}
@props(['status'])

@php
    $map = [
        // Ordens de Serviço
        'aguardando'   => ['default',  'Aguardando'],
        'aprovado'     => ['info',     'Aprovado'],
        'em_servico'   => ['warning',  'Em Serviço'],
        'pronto'       => ['success',  'Pronto'],
        'entregue'     => ['brand',    'Entregue'],
        // Orçamentos
        'pendente'     => ['default',  'Pendente'],
        'enviado'      => ['info',     'Enviado'],
        'aceito'       => ['success',  'Aceito'],
        'recusado'     => ['danger',   'Recusado'],
        'expirado'     => ['default',  'Expirado'],
        'cancelado'    => ['danger',   'Cancelado'],
        // Entradas de Estoque
        'concluida'    => ['success',  'Concluída'],
        'em_andamento' => ['warning',  'Em Andamento'],
    ];
    [$variant, $label] = $map[$status] ?? ['default', ucfirst(str_replace('_', ' ', $status))];
@endphp

<x-tag variant="{{ $variant }}">{{ $label }}</x-tag>

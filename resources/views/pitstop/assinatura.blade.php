@extends('layouts.pitstop')
@section('title', 'Minha Assinatura')

@section('content_header')
<div>
    <h1 class="m-0 font-weight-bold"><i class="fas fa-credit-card mr-2"></i>Minha Assinatura</h1>
    <small class="text-muted">Status do seu plano PitStop</small>
</div>
@endsection

@section('content')
@php
    $trialAtivo   = $tenant->trialAtivo();
    $planoPago    = $tenant->emDia();
    $legado       = $tenant->trial_ends_at === null && !$tenant->plano_ativo;
    $expirou      = !$trialAtivo && !$planoPago && !$legado;
    $dias         = $tenant->diasTrialRestantes();
@endphp

{{-- Tenant legado: assinatura manual (Story 6.4) --}}
@if($tenantLegado)
<div class="card card-info shadow mb-4">
    <div class="card-body">
        <p class="mb-0">
            <i class="fas fa-info-circle mr-1"></i>
            Sua assinatura é gerenciada manualmente pelo time PitStop.
            Entre em contato com o suporte para dúvidas sobre cobrança.
        </p>
    </div>
</div>
@endif

{{-- API Asaas indisponível: aviso não-bloqueante e dismissível (Story 6.4) --}}
@if($asaasIndisponivel && !$tenantLegado)
<div class="alert alert-warning alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <i class="icon fas fa-exclamation-triangle"></i>
    Não foi possível carregar suas cobranças agora. Tente novamente em alguns minutos.
</div>
@endif

{{-- Pagamento pendente (Story 6.2) --}}
@if(count($pendentes) > 0)
<div class="card card-warning shadow mb-4">
    <div class="card-header">
        <h3 class="card-title font-weight-bold"><i class="fas fa-exclamation-circle mr-1"></i>Pagamento pendente</h3>
    </div>
    <div class="card-body">
        @foreach($pendentes as $cobranca)
            @php $vencida = ($cobranca['status'] ?? '') === 'OVERDUE'; @endphp
            <div class="alert alert-{{ $vencida ? 'danger' : 'warning' }} d-flex flex-wrap align-items-center mb-2">
                <span class="mr-2">
                    <span class="badge badge-{{ $vencida ? 'danger' : 'warning' }}">{{ $vencida ? 'Vencida' : 'A vencer' }}</span>
                    <strong class="ml-1">R$ {{ number_format($cobranca['value'] ?? 0, 2, ',', '.') }}</strong>
                    @if(!empty($cobranca['dueDate']))
                        — Vencimento: {{ \Carbon\Carbon::parse($cobranca['dueDate'])->format('d/m/Y') }}
                    @endif
                </span>
                <span class="ml-auto">
                    @if(!empty($cobranca['invoiceUrl']))
                        <a href="{{ $cobranca['invoiceUrl'] }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-sm">Pagar agora</a>
                    @endif
                    @if(!empty($cobranca['bankSlipUrl']))
                        <a href="{{ $cobranca['bankSlipUrl'] }}" target="_blank" rel="noopener noreferrer" class="btn btn-link btn-sm">Baixar boleto</a>
                    @endif
                </span>
            </div>
        @endforeach
    </div>
</div>
@endif

<div class="row">

    {{-- Card de status do plano --}}
    <div class="col-md-5 mb-4">
        <div class="card shadow">
            <div class="card-body text-center py-4">

                @if($planoPago)
                    <div style="font-size:2.5rem;">✅</div>
                    <h4 class="mt-2 font-weight-bold text-success">{{ $tenant->nomePlano() }}</h4>
                    <p class="text-muted mb-1">Acesso completo ativo</p>
                    @if($validade)
                        <small class="text-muted d-block">
                            Próximo vencimento: <strong>{{ $validade }}</strong>
                        </small>
                    @endif
                    @if($diasRestantes !== null)
                        <div class="mt-2">
                            @if($diasRestantes < 0)
                                <span class="badge badge-danger">Vencido</span>
                            @elseif($diasRestantes <= 5)
                                <span class="badge badge-warning">{{ $diasRestantes }} dia(s) restante(s)</span>
                            @else
                                <span class="badge badge-success">{{ $diasRestantes }} dia(s)</span>
                            @endif
                        </div>
                    @endif

                @elseif($trialAtivo)
                    <div style="font-size:2.5rem;">⏳</div>
                    <h4 class="mt-2 font-weight-bold text-warning">Trial Gratuito</h4>
                    <p class="text-muted mb-1">
                        @if($dias === 0)
                            Expira <strong>hoje</strong>!
                        @elseif($dias === 1)
                            Expira <strong>amanhã</strong>
                        @else
                            Expira em <strong>{{ $dias }} dias</strong>
                        @endif
                    </p>
                    <small class="text-muted">
                        Data limite: {{ $tenant->trial_ends_at->format('d/m/Y') }}
                    </small>

                @elseif($legado)
                    <div style="font-size:2.5rem;">🏛️</div>
                    <h4 class="mt-2 font-weight-bold">Acesso Legado</h4>
                    <p class="text-muted mb-0">Conta criada antes do sistema de planos</p>

                @else
                    <div style="font-size:2.5rem;">🔒</div>
                    <h4 class="mt-2 font-weight-bold text-danger">Trial Expirado</h4>
                    <p class="text-muted mb-3">Seu período gratuito encerrou.</p>
                    <a href="{{ route('assine') }}" class="btn btn-danger btn-block">
                        <i class="fas fa-bolt mr-1"></i> Assinar Plano Pro — R$99,90/mês
                    </a>
                @endif

                @if($trialAtivo)
                    <div class="mt-3">
                        <a href="{{ route('assine') }}" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-bolt mr-1"></i> Assinar agora e não perder o acesso
                        </a>
                    </div>
                @endif

            </div>
        </div>
    </div>

    {{-- Uso do trial --}}
    @if($uso)
    <div class="col-md-7 mb-4">
        <div class="card shadow">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold">Uso do Trial</h6>
            </div>
            <div class="card-body">
                @foreach($uso as $recurso => $dados)
                @php
                    $pct      = $dados['limite'] > 0 ? min(100, round($dados['atual'] / $dados['limite'] * 100)) : 0;
                    $corBarra = $pct >= 90 ? '#dc2626' : ($pct >= 70 ? '#d97706' : '#2563eb');
                    $nomes    = ['clientes'=>'Clientes','orcamentos'=>'Orçamentos','usuarios'=>'Usuários','pecas'=>'Peças'];
                @endphp
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1" style="font-size:.82rem">
                        <span>{{ $nomes[$recurso] ?? $recurso }}</span>
                        <span style="color:#64748b">{{ $dados['atual'] }} / {{ $dados['limite'] }}</span>
                    </div>
                    <div style="background:#e2e8f0;border-radius:4px;height:6px">
                        <div style="background:{{ $corBarra }};width:{{ $pct }}%;height:6px;border-radius:4px;transition:width .3s"></div>
                    </div>
                </div>
                @endforeach
                <a href="{{ route('assine') }}" class="btn btn-danger btn-sm btn-block mt-2">
                    <i class="fas fa-bolt mr-1"></i> Assinar Plano Pro — sem limites
                </a>
            </div>
        </div>
    </div>
    @endif

    {{-- Contato --}}
    <div class="col-md-7 mb-4">
        <div class="card shadow">
            <div class="card-body" style="font-size:.85rem;">
                <strong>Dúvidas sobre sua assinatura?</strong><br>
                Entre em contato: <a href="mailto:iaqueatende@gmail.com">iaqueatende@gmail.com</a>
            </div>
        </div>
    </div>

</div>

{{-- Histórico de pagamentos (Story 6.3) --}}
@if(count($historico) > 0)
<div class="card shadow mb-4">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold">Histórico de pagamentos</h6>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-striped table-sm mb-0">
            <thead>
                <tr><th class="pl-3">Data</th><th>Descrição</th><th>Valor</th><th>Status</th><th>Ação</th></tr>
            </thead>
            <tbody>
                @foreach($historico as $p)
                    @php
                        $statusAsaas = $p['status'] ?? '';
                        $dataBruta   = !empty($p['paymentDate']) ? $p['paymentDate'] : ($p['dateCreated'] ?? null);
                        $dataFmt     = $dataBruta ? \Carbon\Carbon::parse($dataBruta)->format('d/m/Y') : '—';
                        $statusInfo  = $mapStatus($statusAsaas);
                        $pago        = in_array($statusAsaas, ['CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH'], true);
                        $aPagar      = in_array($statusAsaas, ['PENDING', 'OVERDUE'], true);
                    @endphp
                    <tr>
                        <td class="pl-3" style="white-space:nowrap;">{{ $dataFmt }}</td>
                        <td style="font-size:.85rem;">{{ $p['description'] ?? '—' }}</td>
                        <td style="white-space:nowrap;">R$ {{ number_format($p['value'] ?? 0, 2, ',', '.') }}</td>
                        <td><span class="badge badge-{{ $statusInfo['badge'] }}">{{ $statusInfo['label'] }}</span></td>
                        <td>
                            @if($pago && !empty($p['transactionReceiptUrl']))
                                <a href="{{ $p['transactionReceiptUrl'] }}" target="_blank" rel="noopener noreferrer">Ver recibo</a>
                            @elseif($aPagar && !empty($p['invoiceUrl']))
                                <a href="{{ $p['invoiceUrl'] }}" target="_blank" rel="noopener noreferrer">Pagar</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Histórico de eventos do sistema — seção secundária colapsável (Story 6.3) --}}
<div class="card card-secondary collapsed-card shadow mb-4">
    <div class="card-header" data-card-widget="collapse" style="cursor:pointer;">
        <h6 class="card-title m-0 font-weight-bold">Histórico de eventos do sistema</h6>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
        </div>
    </div>
    <div class="card-body p-0" style="display:none;">
        @if($logs->isEmpty())
            <p class="text-muted text-center py-4">Nenhum evento registrado.</p>
        @else
        <table class="table table-sm table-borderless mb-0">
            <tbody>
            @foreach($logs as $log)
                <tr>
                    <td class="pl-3" style="font-size:.8rem;color:#64748b;white-space:nowrap;">
                        {{ $log->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td style="font-size:.85rem;">
                        {{ match($log->evento) {
                            'payment_confirmed'          => '✅ Pagamento confirmado',
                            'payment_canceled'           => '❌ Pagamento cancelado',
                            'payment_overdue'            => '⚠️ Pagamento em atraso',
                            'email_trial_expirando_3_dias' => '📧 Email: trial expira em 3 dias',
                            'email_trial_expirando_1_dia'  => '📧 Email: trial expira amanhã',
                            'email_trial_expirado'         => '📧 Email: trial expirado',
                            default                        => $log->evento,
                        } }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>
@endsection

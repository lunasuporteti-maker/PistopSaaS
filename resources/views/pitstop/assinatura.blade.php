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

<div class="row">

    {{-- Card de status do plano --}}
    <div class="col-md-5 mb-4">
        <div class="card shadow">
            <div class="card-body text-center py-4">

                @if($planoPago)
                    <div style="font-size:2.5rem;">✅</div>
                    <h4 class="mt-2 font-weight-bold text-success">Plano Pro</h4>
                    <p class="text-muted mb-1">Acesso completo ativo</p>
                    @if($tenant->plano_vence_em)
                        <small class="text-muted">
                            Próximo vencimento: <strong>{{ \Carbon\Carbon::parse($tenant->plano_vence_em)->format('d/m/Y') }}</strong>
                        </small>
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

    {{-- Detalhes e histórico --}}
    <div class="col-md-7 mb-4">
        <div class="card shadow">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold">Histórico de eventos</h6>
            </div>
            <div class="card-body p-0">
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

        <div class="card shadow mt-3">
            <div class="card-body" style="font-size:.85rem;">
                <strong>Dúvidas sobre sua assinatura?</strong><br>
                Entre em contato: <a href="mailto:iaqueatende@gmail.com">iaqueatende@gmail.com</a>
            </div>
        </div>
    </div>

</div>
@endsection

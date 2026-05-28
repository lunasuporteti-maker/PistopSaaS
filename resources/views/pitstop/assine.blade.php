@extends('layouts.pitstop')
@section('title', 'Escolha seu Plano')

@section('content_header')
<div>
    <h1 class="m-0 font-weight-bold"><i class="fas fa-star mr-2 text-danger"></i>Planos PitStop</h1>
    <small class="text-muted">Continue usando todas as funcionalidades</small>
</div>
@endsection

@section('content')

@if(session('aviso'))
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('aviso') }}
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
</div>
@endif

@if($tenant)
@php
    $diasRestantes = $tenant->diasTrialRestantes();
    $trialAtivo    = $tenant->trialAtivo();
    $expirou       = !$trialAtivo && !$tenant->emDia() && $tenant->trial_ends_at !== null;
@endphp

@if($trialAtivo && $diasRestantes <= 7)
<div class="alert alert-warning">
    <i class="fas fa-hourglass-half mr-2"></i>
    Seu trial expira em <strong>{{ $diasRestantes }} {{ $diasRestantes == 1 ? 'dia' : 'dias' }}</strong>.
    Assine agora para não perder o acesso.
</div>
@elseif($expirou)
<div class="alert alert-danger">
    <i class="fas fa-lock mr-2"></i>
    Seu período de avaliação encerrou. Assine um plano para continuar usando o PitStop.
</div>
@endif
@endif

{{-- Cards de plano --}}
<div class="row justify-content-center mt-2">

    {{-- Plano Pro --}}
    <div class="col-md-5 mb-4">
        <div class="card shadow h-100" style="border:2px solid var(--danger,#e53e3e)">
            <div class="card-header py-3 text-center" style="background:var(--danger,#e53e3e)">
                <h5 class="m-0 font-weight-bold text-white">Plano Pro</h5>
                <div class="mt-2 text-white" style="font-size:2.2rem;font-weight:800;line-height:1">
                    R$ 99,90
                    <span style="font-size:.9rem;font-weight:400;opacity:.8">/mês</span>
                </div>
                <div class="mt-1 text-white" style="font-size:.78rem;opacity:.85">Acesso completo a todos os recursos</div>
            </div>
            <div class="card-body">
                <ul class="list-unstyled" style="font-size:.9rem">
                    <li class="py-1"><i class="fas fa-check text-success mr-2"></i>Ordens de serviço ilimitadas</li>
                    <li class="py-1"><i class="fas fa-check text-success mr-2"></i>Kanban de produção em tempo real</li>
                    <li class="py-1"><i class="fas fa-check text-success mr-2"></i>Controle financeiro completo</li>
                    <li class="py-1"><i class="fas fa-check text-success mr-2"></i>Clientes, veículos e histórico</li>
                    <li class="py-1"><i class="fas fa-check text-success mr-2"></i>Controle de estoque e peças</li>
                    <li class="py-1"><i class="fas fa-check text-success mr-2"></i>Agendamentos e lembretes</li>
                    <li class="py-1"><i class="fas fa-check text-success mr-2"></i>Relatórios e exportação PDF</li>
                    <li class="py-1"><i class="fas fa-check text-success mr-2"></i>Múltiplos usuários</li>
                    <li class="py-1"><i class="fas fa-check text-success mr-2"></i>Suporte por WhatsApp</li>
                </ul>
            </div>
            <div class="card-footer bg-transparent text-center pb-3">
                @if($tenant)
                <form action="{{ route('assine.checkout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-block btn-lg">
                        <i class="fas fa-bolt mr-1"></i> Assinar Agora — R$99,90/mês
                    </button>
                </form>
                @else
                <a href="{{ route('cadastro.form') }}" class="btn btn-danger btn-block btn-lg">
                    <i class="fas fa-bolt mr-1"></i> Criar conta grátis
                </a>
                @endif
                <small class="text-muted d-block mt-2">Cancele quando quiser</small>
            </div>
        </div>
    </div>

</div>

{{-- Rodapé informativo --}}
<div class="text-center text-muted mt-2 mb-4" style="font-size:.85rem">
    <p>
        Pagamento seguro via Pix ou cartão de crédito. Cancele quando quiser.<br>
        Dúvidas? Entre em contato: <a href="mailto:suporte@iaqueatende.com.br" class="text-danger">suporte@iaqueatende.com.br</a>
    </p>
</div>

@endsection


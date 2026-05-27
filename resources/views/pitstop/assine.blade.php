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

    {{-- Plano Básico --}}
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3 text-center">
                <h5 class="m-0 font-weight-bold text-muted">Básico</h5>
                <div class="mt-2" style="font-size:2rem;font-weight:800;line-height:1">
                    R$ 97
                    <span style="font-size:.9rem;font-weight:400;color:#6c757d">/mês</span>
                </div>
            </div>
            <div class="card-body">
                <ul class="list-unstyled" style="font-size:.9rem">
                    <li class="py-1"><i class="fas fa-check text-success mr-2"></i>Ordens de serviço ilimitadas</li>
                    <li class="py-1"><i class="fas fa-check text-success mr-2"></i>Kanban de produção</li>
                    <li class="py-1"><i class="fas fa-check text-success mr-2"></i>Controle financeiro</li>
                    <li class="py-1"><i class="fas fa-check text-success mr-2"></i>Clientes e veículos</li>
                    <li class="py-1"><i class="fas fa-check text-success mr-2"></i>Relatórios em PDF</li>
                    <li class="py-1"><i class="fas fa-check text-success mr-2"></i>Agendamentos</li>
                    <li class="py-1 text-muted"><i class="fas fa-times mr-2"></i>Múltiplos usuários</li>
                    <li class="py-1 text-muted"><i class="fas fa-times mr-2"></i>Suporte prioritário</li>
                </ul>
            </div>
            <div class="card-footer bg-transparent text-center pb-3">
                {{-- Substituir href pelo link de pagamento Asaas --}}
                <a href="#" class="btn btn-outline-danger btn-block" data-plan="basico">
                    Assinar Básico
                </a>
            </div>
        </div>
    </div>

    {{-- Plano Profissional (destaque) --}}
    <div class="col-md-4 mb-4">
        <div class="card shadow h-100" style="border:2px solid var(--danger,#e53e3e)">
            <div class="card-header py-3 text-center" style="background:var(--danger,#e53e3e)">
                <span class="badge badge-light mb-1" style="font-size:.65rem">MAIS POPULAR</span>
                <h5 class="m-0 font-weight-bold text-white">Profissional</h5>
                <div class="mt-2 text-white" style="font-size:2rem;font-weight:800;line-height:1">
                    R$ 197
                    <span style="font-size:.9rem;font-weight:400;opacity:.8">/mês</span>
                </div>
            </div>
            <div class="card-body">
                <ul class="list-unstyled" style="font-size:.9rem">
                    <li class="py-1"><i class="fas fa-check text-success mr-2"></i>Tudo do plano Básico</li>
                    <li class="py-1"><i class="fas fa-check text-success mr-2"></i>Até 5 usuários</li>
                    <li class="py-1"><i class="fas fa-check text-success mr-2"></i>Controle de estoque</li>
                    <li class="py-1"><i class="fas fa-check text-success mr-2"></i>Catálogo de serviços</li>
                    <li class="py-1"><i class="fas fa-check text-success mr-2"></i>Comissões de equipe</li>
                    <li class="py-1"><i class="fas fa-check text-success mr-2"></i>Lembretes automáticos</li>
                    <li class="py-1"><i class="fas fa-check text-success mr-2"></i>Suporte prioritário</li>
                    <li class="py-1"><i class="fas fa-check text-success mr-2"></i>Domínio customizado</li>
                </ul>
            </div>
            <div class="card-footer bg-transparent text-center pb-3">
                {{-- Substituir href pelo link de pagamento Asaas --}}
                <a href="#" class="btn btn-danger btn-block" data-plan="profissional">
                    Assinar Profissional
                </a>
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

@push('js')
<script>
// Placeholder: substitua os href dos botões pelos links reais do Asaas
document.querySelectorAll('[data-plan]').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        alert('Link de pagamento em breve. Entre em contato com suporte@iaqueatende.com.br para assinar.');
    });
});
</script>
@endpush

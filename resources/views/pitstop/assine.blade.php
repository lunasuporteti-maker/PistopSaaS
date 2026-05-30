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
    $desconto      = $tenant->desconto_percentual ?? 0;
    $precoProFinal    = $desconto > 0 ? $tenant->precoComDesconto() : null; // só usado se tier=pro
    $precoMaxFinal    = $desconto > 0
        ? round(\App\Models\Tenant::PRECOS['pro_max'] * (1 - $desconto / 100), 2)
        : null;
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
        <div class="card shadow h-100" style="border:2px solid #718096">
            <div class="card-header py-3 text-center" style="background:#2d3748">
                <h5 class="m-0 font-weight-bold text-white">Plano Pro</h5>
                <div class="mt-2 text-white" style="font-size:2.2rem;font-weight:800;line-height:1">
                    @if($tenant && $desconto > 0 && $tenant->tier() === 'pro')
                        <span style="font-size:1rem;text-decoration:line-through;opacity:.5">R$ 99,90</span><br>
                        R$ {{ number_format($tenant->precoComDesconto(), 2, ',', '.') }}
                    @else
                        R$ 99,90
                    @endif
                    <span style="font-size:.9rem;font-weight:400;opacity:.8">/mês</span>
                </div>
                <div class="mt-1 text-white" style="font-size:.78rem;opacity:.85">Gestão completa da oficina</div>
            </div>
            <div class="card-body">
                <ul class="list-unstyled" style="font-size:.9rem">
                    <li class="py-1"><i class="fas fa-check text-success mr-2"></i>Ordens de serviço ilimitadas</li>
                    <li class="py-1"><i class="fas fa-check text-success mr-2"></i>Kanban de produção em tempo real</li>
                    <li class="py-1"><i class="fas fa-check text-success mr-2"></i>Controle financeiro completo</li>
                    <li class="py-1"><i class="fas fa-check text-success mr-2"></i>Clientes, veículos e histórico</li>
                    <li class="py-1"><i class="fas fa-check text-success mr-2"></i>Controle de estoque e peças</li>
                    <li class="py-1"><i class="fas fa-check text-success mr-2"></i>Agendamentos e lembretes</li>
                    <li class="py-1"><i class="fas fa-check text-success mr-2"></i>Relatórios e exportação PDF/Excel</li>
                    <li class="py-1"><i class="fas fa-check text-success mr-2"></i>Múltiplos usuários</li>
                    <li class="py-1"><i class="fas fa-check text-success mr-2"></i>Aprovação online pelo cliente</li>
                    <li class="py-1"><i class="fas fa-check text-success mr-2"></i>Suporte por WhatsApp</li>
                    <li class="py-1 text-muted"><i class="fas fa-times mr-2" style="color:#718096"></i>Galeria de fotos no portal</li>
                </ul>
            </div>
            <div class="card-footer bg-transparent text-center pb-3">
                @if($tenant)
                <form action="{{ route('assine.checkout') }}" method="POST">
                    @csrf
                    <input type="hidden" name="plano_tier" value="pro">
                    <button type="submit" class="btn btn-block btn-lg" style="background:#2d3748;color:#fff;border:1px solid #718096">
                        <i class="fas fa-bolt mr-1"></i> Assinar Pro — R$99,90/mês
                    </button>
                </form>
                @else
                <a href="{{ route('cadastro.form') }}" class="btn btn-block btn-lg" style="background:#2d3748;color:#fff;border:1px solid #718096">
                    <i class="fas fa-bolt mr-1"></i> Criar conta grátis
                </a>
                @endif
                <small class="text-muted d-block mt-2">Cancele quando quiser</small>
            </div>
        </div>
    </div>

    {{-- Plano Pro Max --}}
    <div class="col-md-5 mb-4">
        <div class="card shadow h-100" style="border:2px solid var(--danger,#e53e3e);position:relative">
            <div style="position:absolute;top:-12px;left:50%;transform:translateX(-50%);
                        background:#e53e3e;color:#fff;font-size:.72rem;font-weight:700;
                        padding:2px 14px;border-radius:20px;letter-spacing:.05em;white-space:nowrap">
                MAIS POPULAR
            </div>
            <div class="card-header py-3 text-center" style="background:var(--danger,#e53e3e)">
                <h5 class="m-0 font-weight-bold text-white">Plano Pro Max</h5>
                <div class="mt-2 text-white" style="font-size:2.2rem;font-weight:800;line-height:1">
                    @if($tenant && $desconto > 0 && $tenant->tier() === 'pro_max')
                        <span style="font-size:1rem;text-decoration:line-through;opacity:.5">R$ 157,50</span><br>
                        R$ {{ number_format($tenant->precoComDesconto(), 2, ',', '.') }}
                    @else
                        R$ 157,50
                    @endif
                    <span style="font-size:.9rem;font-weight:400;opacity:.8">/mês</span>
                </div>
                <div class="mt-1 text-white" style="font-size:.78rem;opacity:.85">Tudo do Pro + galeria de fotos</div>
            </div>
            <div class="card-body">
                <ul class="list-unstyled" style="font-size:.9rem">
                    <li class="py-1"><i class="fas fa-check text-success mr-2"></i>Tudo do Plano Pro</li>
                    <li class="py-1"><i class="fas fa-check text-success mr-2"></i>Upload de fotos por OS (antes/durante/depois)</li>
                    <li class="py-1"><i class="fas fa-check text-success mr-2"></i>Galeria pública no portal do cliente</li>
                    <li class="py-1"><i class="fas fa-check text-success mr-2"></i>Thumbnails automáticos</li>
                    <li class="py-1"><i class="fas fa-check text-success mr-2"></i>Até 5 fotos por orçamento</li>
                    <li class="py-1"><i class="fas fa-check text-success mr-2"></i>Histórico fotográfico do veículo</li>
                </ul>
            </div>
            <div class="card-footer bg-transparent text-center pb-3">
                @if($tenant)
                <form action="{{ route('assine.checkout') }}" method="POST">
                    @csrf
                    <input type="hidden" name="plano_tier" value="pro_max">
                    <button type="submit" class="btn btn-danger btn-block btn-lg">
                        <i class="fas fa-bolt mr-1"></i> Assinar Pro Max — R$157,50/mês
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
        Dúvidas? Entre em contato: <a href="mailto:iaqueatende@gmail.com" class="text-danger">iaqueatende@gmail.com</a>
    </p>
</div>

@endsection

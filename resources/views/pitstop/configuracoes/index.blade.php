@extends('layouts.pitstop')
@section('title', 'Configurações do Sistema')

@section('content_header')
<div class="d-flex align-items-center">
    <div>
        <h1 class="m-0 font-weight-bold text-dark"><i class="fas fa-cog mr-2 text-danger"></i>Configurações</h1>
        <small class="text-muted">Dados da oficina, WhatsApp e integrações</small>
    </div>
</div>
@endsection

@section('content')

<form method="POST" action="{{ route('configuracoes.update') }}">
@csrf

<div class="row">
    {{-- Dados da Oficina --}}
    <div class="col-md-6">
        <div class="card card-outline card-danger shadow-sm">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-store mr-2"></i>Dados da Oficina</h5>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="font-weight-600">Nome da Oficina <span class="text-danger">*</span></label>
                    <input type="text" name="nome_oficina"
                           class="form-control @error('nome_oficina') is-invalid @enderror"
                           value="{{ old('nome_oficina', $configs['nome_oficina']?->valor ?? 'PitStop') }}"
                           maxlength="120"
                           data-uppercase
                           required>
                    @error('nome_oficina')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="font-weight-600">Telefone / WhatsApp</label>
                    <input type="text" name="telefone_oficina"
                           class="form-control"
                           value="{{ old('telefone_oficina', $configs['telefone_oficina']?->valor ?? '') }}"
                           placeholder="(99) 99999-9999"
                           maxlength="20"
                           data-phone>
                    <small class="text-muted">Usado nas mensagens de WhatsApp enviadas pela oficina</small>
                </div>
                <div class="form-group">
                    <label class="font-weight-600">Endereço</label>
                    <input type="text" name="endereco_oficina"
                           class="form-control"
                           value="{{ old('endereco_oficina', $configs['endereco_oficina']?->valor ?? '') }}"
                           placeholder="Rua, número, bairro, cidade"
                           maxlength="200"
                           data-uppercase>
                </div>
                <div class="form-group">
                    <label class="font-weight-600">E-mail</label>
                    <input type="email" name="email_oficina"
                           class="form-control"
                           value="{{ old('email_oficina', $configs['email_oficina']?->valor ?? '') }}"
                           placeholder="contato@oficina.com.br"
                           maxlength="120">
                </div>
                <div class="form-group">
                    <label class="font-weight-600">CNPJ (ou CPF)</label>
                    <input type="text" name="cnpj_oficina"
                           class="form-control"
                           value="{{ old('cnpj_oficina', $configs['cnpj_oficina']?->valor ?? '') }}"
                           placeholder="CNPJ da oficina ou CPF do responsável"
                           maxlength="20">
                    <small class="form-text text-muted">Se a oficina não tem CNPJ, preencha com o CPF. Necessário para emitir orçamentos e ativar a assinatura.</small>
                </div>
                <div class="form-group mb-0">
                    <label class="font-weight-600">Instagram</label>
                    <input type="text" name="instagram_oficina"
                           class="form-control"
                           value="{{ old('instagram_oficina', $configs['instagram_oficina']?->valor ?? '') }}"
                           placeholder="@oficina.mecanica"
                           maxlength="60">
                </div>
            </div>
        </div>
    </div>

    {{-- Google Review --}}
    <div class="col-md-6">
        <div class="card card-outline card-success shadow-sm">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="20" height="20" class="mr-2" style="vertical-align:middle">
                        <path fill="#FFC107" d="M43.6 20H24v8h11.3C33.6 32.6 29.3 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3 0 5.7 1.1 7.8 2.9l5.7-5.7C34.1 6.5 29.3 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20c11 0 19.6-7.9 19.6-20 0-1.3-.1-2.7-.4-4z"/>
                        <path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.5 15.1 18.9 12 24 12c3 0 5.7 1.1 7.8 2.9l5.7-5.7C34.1 6.5 29.3 4 24 4 16.3 4 9.7 8.4 6.3 14.7z"/>
                        <path fill="#4CAF50" d="M24 44c5.2 0 9.9-1.9 13.5-5l-6.2-5.2C29.5 35.6 26.9 36.5 24 36.5c-5.2 0-9.6-3.5-11.2-8.3l-6.6 5.1C9.6 39.5 16.3 44 24 44z"/>
                        <path fill="#1976D2" d="M43.6 20H24v8h11.3c-.9 2.5-2.6 4.6-4.9 6l6.2 5.2C40.7 35.8 44 30.3 44 24c0-1.3-.1-2.7-.4-4z"/>
                    </svg>
                    Avaliação no Google
                </h5>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="font-weight-600">Link do Google Meu Negócio / Maps</label>
                    <input type="url" name="google_review_link"
                           class="form-control @error('google_review_link') is-invalid @enderror"
                           value="{{ old('google_review_link', $configs['google_review_link']?->valor ?? '') }}"
                           placeholder="https://g.page/r/XXXXXX/review">
                    <small class="text-muted">
                        Cole aqui o link de avaliação do seu perfil no Google.<br>
                        <a href="https://business.google.com/dashboard" target="_blank" class="text-danger">
                            <i class="fas fa-external-link-alt mr-1"></i>Acessar Google Meu Negócio
                        </a>
                    </small>
                    @error('google_review_link')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                @if(!empty($configs['google_review_link']?->valor))
                <div class="alert alert-success py-2 mb-3">
                    <i class="fas fa-check-circle mr-2"></i>
                    Link configurado! Será enviado junto com a mensagem de conclusão da OS.
                    <br>
                    <small>
                        <a href="{{ $configs['google_review_link']?->valor }}" target="_blank" class="text-success font-weight-bold">
                            {{ Str::limit($configs['google_review_link']?->valor, 60) }}
                        </a>
                    </small>
                </div>
                @else
                <div class="alert alert-warning py-2 mb-3">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Link não configurado. Configure para enviar pedidos de avaliação automaticamente.
                </div>
                @endif

                <div class="form-group mb-0">
                    <label class="font-weight-600">Mensagem de Convite para Avaliação</label>
                    <textarea name="mensagem_review"
                              class="form-control @error('mensagem_review') is-invalid @enderror"
                              rows="3"
                              maxlength="500"
                              placeholder="Mensagem enviada pelo WhatsApp pedindo avaliação">{{ old('mensagem_review', $configs['mensagem_review']?->valor ?? '') }}</textarea>
                    <small class="text-muted">Será enviada junto com o link do Google ao finalizar a OS</small>
                    @error('mensagem_review')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Preview da mensagem WhatsApp --}}
@php
    $reviewLink  = $configs['google_review_link']?->valor ?? '';
    $msgReview   = $configs['mensagem_review']?->valor ?? 'Ficamos felizes em atender você! Poderia nos avaliar no Google?';
    $nomeOficina = $configs['nome_oficina']?->valor ?? 'PitStop';
@endphp
<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fab fa-whatsapp mr-2 text-success"></i>Preview da Mensagem WhatsApp (ao finalizar OS)
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="bg-light rounded p-3" style="border-left:4px solid #25d366; font-family:monospace; font-size:.88rem">
                    <div class="text-muted small mb-2"><i class="fas fa-user-circle mr-1"></i> {{ $nomeOficina }}</div>
                    Olá [NOME DO CLIENTE]! 👋<br><br>
                    Seu veículo está pronto e pode vir buscar! 🚗✅<br><br>
                    <strong>OS:</strong> OS-2026-XXX<br>
                    <strong>Veículo:</strong> [MARCA] [MODELO] ([PLACA])<br>
                    <strong>Serviços:</strong> [lista]<br>
                    <strong>Total:</strong> R$ [VALOR]<br><br>
                    {{ $msgReview }}<br><br>
                    @if($reviewLink)
                    ⭐ Avalie-nos: {{ Str::limit($reviewLink, 50) }}<br><br>
                    @endif
                    Obrigado pela preferência! 🙏<br>
                    <em>— {{ $nomeOficina }}</em>
                </div>
            </div>
            <div class="col-md-6 d-flex align-items-center">
                <div>
                    <h6 class="font-weight-600 mb-2"><i class="fas fa-info-circle mr-2 text-primary"></i>Como funciona</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-check text-success mr-2"></i>Mensagem enviada automaticamente ao <strong>finalizar</strong> a OS</li>
                        <li class="mb-2"><i class="fas fa-check text-success mr-2"></i>Abre o WhatsApp com a mensagem pré-preenchida</li>
                        <li class="mb-2"><i class="fas fa-check text-success mr-2"></i>Inclui resumo dos serviços e valor total</li>
                        @if($reviewLink)
                        <li class="mb-2"><i class="fas fa-star text-warning mr-2"></i>Link do Google incluso na mensagem</li>
                        @else
                        <li class="mb-2"><i class="fas fa-exclamation text-muted mr-2"></i>Configure o link do Google para incluir na mensagem</li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end mb-4">
    <button type="submit" class="btn btn-danger btn-lg px-5 shadow">
        <i class="fas fa-save mr-2"></i>Salvar Configurações
    </button>
</div>

</form>
@endsection

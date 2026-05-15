@extends('adminlte::page')
@section('title', $ordem->numero_os)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0 font-weight-bold text-dark">
                <i class="fas fa-tools mr-2 text-danger"></i>OS {{ $ordem->numero_os }}
            </h1>
            <small class="text-muted">{{ $ordem->finalizado_em ? 'Concluída em ' . $ordem->finalizado_em->format('d/m/Y \à\s H:i') : 'Em andamento' }}</small>
        </div>
        <div class="d-flex gap-2">
            @if(!$ordem->finalizado_em)
            <button class="btn btn-success" data-toggle="modal" data-target="#modalFinalizar">
                <i class="fas fa-check-double mr-1"></i> Finalizar OS
            </button>
            @endif
            @if($ordem->cliente->telefone)
            <a href="{{ 'https://wa.me/55' . preg_replace('/\D/', '', $ordem->cliente->telefone) . '?text=' . urlencode(
                'Olá ' . $ordem->cliente->nome . '! Seu veículo está pronto e pode vir buscar! 🚗✅' . "\n\n" .
                '*OS:* ' . $ordem->numero_os . "\n" .
                '*Veículo:* ' . $ordem->veiculo->marca . ' ' . $ordem->veiculo->modelo . ' (' . $ordem->veiculo->placa . ')' . "\n" .
                '*Total:* R$ ' . number_format($ordem->valor_total, 2, ',', '.') . "\n\n" .
                (app(\App\Models\Configuracao::class)::get('mensagem_review') ?: 'Ficamos felizes em atender você!') . "\n" .
                (app(\App\Models\Configuracao::class)::get('google_review_link') ? "\n⭐ Avalie-nos: " . app(\App\Models\Configuracao::class)::get('google_review_link') : '')
            ) }}"
               target="_blank"
               class="btn btn-success"
               title="Avisar pelo WhatsApp">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="white" class="mr-1"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                WhatsApp
            </a>
            @endif
            <a href="{{ route('ordens.index') }}" class="btn btn-outline-secondary ml-1">
                <i class="fas fa-arrow-left mr-1"></i> Voltar
            </a>
        </div>
    </div>
@endsection

@section('content')
@include('pitstop._partials.alerts')

{{-- Modal WhatsApp pós-finalização --}}
@if(session('show_whatsapp') && $ordem->cliente->telefone)
@php
    $googleLink  = \App\Models\Configuracao::get('google_review_link');
    $msgReview   = \App\Models\Configuracao::get('mensagem_review', 'Ficamos felizes em atender você! Poderia nos avaliar no Google? 🙏');
    $nomeOficina = \App\Models\Configuracao::get('nome_oficina', 'PitStop');
    $servicos    = $ordem->orcamento?->servicos->pluck('servico_nome')->join(', ') ?? '';
    $msgWA = 'Olá ' . $ordem->cliente->nome . '! Seu veículo está pronto e pode vir buscar! 🚗✅' . "\n\n"
           . '*OS:* ' . $ordem->numero_os . "\n"
           . '*Veículo:* ' . $ordem->veiculo->marca . ' ' . $ordem->veiculo->modelo . ' (' . $ordem->veiculo->placa . ')' . "\n"
           . ($servicos ? '*Serviços:* ' . $servicos . "\n" : '')
           . '*Total:* R$ ' . number_format($ordem->valor_total, 2, ',', '.') . "\n\n"
           . $msgReview
           . ($googleLink ? "\n\n⭐ Avalie-nos: " . $googleLink : '');
    $waLink = 'https://wa.me/55' . preg_replace('/\D/', '', $ordem->cliente->telefone) . '?text=' . urlencode($msgWA);
@endphp
<div class="modal fade show d-block" id="modalWppReview" style="background:rgba(0,0,0,.5)" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-premium border-0">
            <div class="modal-header" style="background:linear-gradient(135deg,#1a7a3e,#25d366);color:#fff">
                <h5 class="modal-title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="22" height="22" fill="white" class="mr-2"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    OS Finalizada com Sucesso!
                </h5>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-3">
                    <div style="font-size:3rem">🎉</div>
                    <h5 class="font-weight-700 mb-1">{{ $ordem->numero_os }} finalizada!</h5>
                    <p class="text-muted mb-0">Envie a mensagem de conclusão para o cliente pelo WhatsApp.</p>
                </div>

                <div class="bg-light rounded p-3 mb-3" style="border-left:4px solid #25d366;font-family:monospace;font-size:.82rem;white-space:pre-wrap">{{ $msgWA }}</div>

                @if($googleLink)
                <div class="alert alert-warning py-2 mb-3">
                    <i class="fas fa-star text-warning mr-2"></i>
                    <strong>Link do Google incluso!</strong> O cliente será convidado a avaliar a oficina.
                </div>
                @else
                <div class="alert alert-light border py-2 mb-3">
                    <i class="fas fa-info-circle text-muted mr-2"></i>
                    Sem link do Google configurado.
                    <a href="{{ route('configuracoes.index') }}" class="text-danger ml-1">Configurar agora →</a>
                </div>
                @endif

                <div class="d-flex gap-2">
                    <a href="{{ $waLink }}" target="_blank" class="btn btn-success btn-lg flex-grow-1">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="white" class="mr-2"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        Enviar no WhatsApp
                    </a>
                    <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('modalWppReview').remove()">
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row">
    <div class="col-md-4">
        <div class="card card-{{ $ordem->finalizado_em ? 'success' : 'warning' }} card-outline shadow-sm">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle mr-1"></i>
                    {{ $ordem->finalizado_em ? 'OS Concluída' : 'Em Andamento' }}
                </h3>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5 text-muted small">Cliente</dt>
                    <dd class="col-7"><a href="{{ route('clientes.show', $ordem->cliente) }}" class="text-danger font-weight-600">{{ $ordem->cliente->nome }}</a></dd>
                    <dt class="col-5 text-muted small">Veículo</dt>
                    <dd class="col-7">
                        {{ $ordem->veiculo->marca }} {{ $ordem->veiculo->modelo }}
                        <br><span class="badge badge-secondary badge-pill">{{ $ordem->veiculo->placa }}</span>
                    </dd>
                    <dt class="col-5 text-muted small">Abertura</dt>
                    <dd class="col-7">{{ $ordem->created_at->format('d/m/Y H:i') }}</dd>
                    @if($ordem->finalizado_em)
                    <dt class="col-5 text-muted small">Conclusão</dt>
                    <dd class="col-7">{{ $ordem->finalizado_em->format('d/m/Y H:i') }}</dd>
                    @endif
                    <dt class="col-5 text-muted small">Garantia</dt>
                    <dd class="col-7">{{ $ordem->garantia_dias ? $ordem->garantia_dias . ' dias' : '—' }}</dd>
                </dl>
                <hr>
                <h4 class="text-right mb-0">
                    Total: <strong class="text-{{ $ordem->finalizado_em ? 'success' : 'dark' }}">
                        R$ {{ number_format($ordem->valor_total, 2, ',', '.') }}
                    </strong>
                </h4>
            </div>
        </div>

        {{-- Pagamentos --}}
        @if($ordem->pagamentos->count())
        <div class="card card-success card-outline shadow-sm">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-money-bill mr-1"></i> Pagamentos Registrados</h3></div>
            <div class="card-body p-0">
                @foreach($ordem->pagamentos as $pag)
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <span>
                        <i class="fas fa-{{ in_array($pag->forma,['pix','transferencia']) ? 'qrcode' : 'credit-card' }} mr-2 text-success"></i>
                        {{ ucfirst(str_replace('_',' ',$pag->forma)) }}
                    </span>
                    <strong class="text-success">R$ {{ number_format($pag->valor, 2, ',', '.') }}</strong>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Botões WhatsApp (sempre visíveis após finalização) --}}
        @if($ordem->finalizado_em && $ordem->cliente->telefone)
        @php
            $gLink = \App\Models\Configuracao::get('google_review_link');
            $mReview = \App\Models\Configuracao::get('mensagem_review', 'Ficamos felizes em atender você!');
            $srvs = $ordem->orcamento?->servicos->pluck('servico_nome')->join(', ') ?? '';
            $waMsgFinal = 'Olá ' . $ordem->cliente->nome . '! Seu veículo está pronto e pode vir buscar! 🚗✅' . "\n\n"
                        . '*OS:* ' . $ordem->numero_os . "\n"
                        . '*Veículo:* ' . $ordem->veiculo->marca . ' ' . $ordem->veiculo->modelo . ' (' . $ordem->veiculo->placa . ')' . "\n"
                        . ($srvs ? '*Serviços:* ' . $srvs . "\n" : '')
                        . '*Total:* R$ ' . number_format($ordem->valor_total, 2, ',', '.') . "\n\n"
                        . $mReview
                        . ($gLink ? "\n\n⭐ Avalie-nos: " . $gLink : '');
            $waFinalLink = 'https://wa.me/55' . preg_replace('/\D/', '', $ordem->cliente->telefone) . '?text=' . urlencode($waMsgFinal);
        @endphp
        <div class="card shadow-sm">
            <div class="card-body p-3">
                <a href="{{ $waFinalLink }}" target="_blank" class="btn btn-success btn-block mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="white" class="mr-1"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    Avisar cliente pelo WhatsApp
                </a>
                @if($gLink)
                <small class="text-muted d-block text-center"><i class="fas fa-star text-warning mr-1"></i>Inclui pedido de avaliação Google</small>
                @endif
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-8">
        @if($ordem->orcamento)
        {{-- Serviços --}}
        <div class="card shadow-sm">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-wrench mr-1 text-danger"></i> Serviços Realizados</h3></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="thead-light"><tr><th>Serviço</th><th class="text-right">Valor</th></tr></thead>
                    <tbody>
                        @forelse($ordem->orcamento->servicos as $s)
                        <tr><td>{{ $s->servico_nome }}</td><td class="text-right">R$ {{ number_format($s->valor, 2, ',', '.') }}</td></tr>
                        @empty
                        <tr><td colspan="2" class="text-muted text-center py-3">Nenhum serviço registrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Peças --}}
        <div class="card shadow-sm">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-boxes mr-1 text-danger"></i> Peças Utilizadas</h3></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="thead-light"><tr><th>Peça</th><th>Qtd</th><th>Unit.</th><th class="text-right">Total</th></tr></thead>
                    <tbody>
                        @forelse($ordem->pecas as $p)
                        <tr>
                            <td>{{ $p->peca->nome }}</td>
                            <td>{{ $p->quantidade }}</td>
                            <td>R$ {{ number_format($p->preco_unitario, 2, ',', '.') }}</td>
                            <td class="text-right font-weight-600">R$ {{ number_format($p->quantidade * $p->preco_unitario, 2, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-muted text-center py-3">Nenhuma peça registrada.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal Finalizar OS --}}
@if(!$ordem->finalizado_em)
<div class="modal fade" id="modalFinalizar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content shadow-premium border-0">
            <form method="POST" action="{{ route('ordens.finalizar', $ordem) }}">
                @csrf
                <div class="modal-header" style="background:linear-gradient(135deg,#1a9c47,#27ae60);color:#fff">
                    <h5 class="modal-title"><i class="fas fa-check-double mr-2"></i> Finalizar OS {{ $ordem->numero_os }}</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">Total da OS</span>
                        <strong class="h5 mb-0 text-success">R$ {{ number_format($ordem->valor_total, 2, ',', '.') }}</strong>
                    </div>

                    <hr>
                    <p class="font-weight-600 text-dark mb-2"><i class="fas fa-money-bill mr-2"></i>Formas de Pagamento</p>

                    <div id="pagamentosContainer">
                        <div class="row mb-2 pagamento-row">
                            <div class="col-6">
                                <select name="pagamentos[0][forma]" class="form-control form-control-sm" required>
                                    <option value="">Forma...</option>
                                    <option value="dinheiro">Dinheiro</option>
                                    <option value="pix">PIX</option>
                                    <option value="cartao_credito">Cartão Crédito</option>
                                    <option value="cartao_debito">Cartão Débito</option>
                                    <option value="transferencia">Transferência</option>
                                    <option value="boleto">Boleto</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <input type="number" step="0.01" name="pagamentos[0][valor]"
                                       class="form-control form-control-sm" placeholder="Valor" required
                                       value="{{ $ordem->valor_total }}">
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="adicionarPagamento()">
                        <i class="fas fa-plus mr-1"></i> Dividir pagamento
                    </button>

                    <div class="alert alert-info mt-3 py-2 mb-0">
                        <i class="fab fa-whatsapp mr-2"></i>
                        Após finalizar, você poderá enviar a mensagem de conclusão + avaliação Google para o cliente.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success px-4">
                        <i class="fas fa-check mr-1"></i> Confirmar Pagamento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@push('js')
<script>
let pagIndex = 1;
function adicionarPagamento() {
    const container = document.getElementById('pagamentosContainer');
    container.insertAdjacentHTML('beforeend', `
        <div class="row mb-2">
            <div class="col-6">
                <select name="pagamentos[${pagIndex}][forma]" class="form-control form-control-sm" required>
                    <option value="">Forma...</option>
                    <option value="dinheiro">Dinheiro</option>
                    <option value="pix">PIX</option>
                    <option value="cartao_credito">Cartão Crédito</option>
                    <option value="cartao_debito">Cartão Débito</option>
                    <option value="transferencia">Transferência</option>
                </select>
            </div>
            <div class="col-5">
                <input type="number" step="0.01" name="pagamentos[${pagIndex}][valor]" class="form-control form-control-sm" placeholder="Valor" required>
            </div>
            <div class="col-1">
                <button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.row').remove()"><i class="fas fa-times"></i></button>
            </div>
        </div>`);
    pagIndex++;
}
</script>
@endpush

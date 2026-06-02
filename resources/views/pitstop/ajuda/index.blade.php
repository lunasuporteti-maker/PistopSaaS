@extends('layouts.pitstop')
@section('title', 'Central de Ajuda')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 font-weight-bold"><i class="fas fa-question-circle mr-2 text-primary"></i>Central de Ajuda</h1>
</div>
@endsection

@section('content')

{{-- Busca --}}
<div class="card shadow-sm mb-4">
    <div class="card-body py-3">
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text bg-white border-right-0">
                    <i class="fas fa-search text-muted"></i>
                </span>
            </div>
            <input type="text" id="buscaAjuda" class="form-control border-left-0"
                   placeholder="Pesquisar na ajuda... (ex: orçamento, cliente, kanban)"
                   oninput="filtrarAjuda(this.value)">
        </div>
    </div>
</div>

<div id="containerAjuda">

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- PRIMEIROS PASSOS --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div class="card shadow-sm mb-3 ajuda-secao">
    <div class="card-header bg-primary text-white py-2 cursor-pointer" data-toggle="collapse" data-target="#sec-inicio">
        <i class="fas fa-rocket mr-2"></i><strong>Primeiros Passos</strong>
        <i class="fas fa-chevron-down float-right mt-1"></i>
    </div>
    <div id="sec-inicio" class="collapse show">
        <div class="card-body">
            <div class="accordion" id="acc-inicio">

                <div class="ajuda-item border-bottom pb-3 mb-3" data-tags="onboarding configuração logo horário">
                    <h6 class="font-weight-bold mb-1">
                        <i class="fas fa-check-circle text-success mr-2"></i>Como configurar minha oficina?
                    </h6>
                    <p class="text-muted mb-0" style="font-size:.9rem">
                        Ao entrar pela primeira vez, o <strong>Assistente de Configuração</strong> será exibido automaticamente.
                        Ele guia você em 5 etapas: nome e endereço, logo da oficina, horário de funcionamento,
                        primeiro funcionário e catálogo inicial de serviços.
                        Você pode abrir novamente em <strong>Configurações → Configuração Inicial</strong>.
                    </p>
                </div>

                <div class="ajuda-item border-bottom pb-3 mb-3" data-tags="login senha acesso usuário">
                    <h6 class="font-weight-bold mb-1">
                        <i class="fas fa-key text-warning mr-2"></i>Como fazer login?
                    </h6>
                    <p class="text-muted mb-0" style="font-size:.9rem">
                        Acesse <strong>app.iaqueatende.com.br</strong> e use o <strong>login</strong> (nome de usuário)
                        e a senha criados no cadastro. O login é o endereço que você escolheu para a oficina,
                        por exemplo: <em>gilsons-motos</em>.
                        Se esquecer a senha, use o link <strong>"Esqueci minha senha"</strong> na tela de login.
                    </p>
                </div>

                <div class="ajuda-item" data-tags="trial período gratuito plano dias">
                    <h6 class="font-weight-bold mb-1">
                        <i class="fas fa-hourglass-half text-info mr-2"></i>Como funciona o trial gratuito?
                    </h6>
                    <p class="text-muted mb-0" style="font-size:.9rem">
                        Você tem <strong>30 dias gratuitos</strong> com acesso a todas as funcionalidades (com alguns limites de quantidade).
                        Ao final do trial, o acesso entra em modo de leitura até você assinar um plano.
                        Durante o trial: até 10 clientes, 20 veículos, 15 orçamentos, 2 usuários e 20 peças.
                    </p>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- CLIENTES E VEÍCULOS --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div class="card shadow-sm mb-3 ajuda-secao">
    <div class="card-header bg-light py-2 cursor-pointer" data-toggle="collapse" data-target="#sec-clientes">
        <i class="fas fa-users mr-2 text-primary"></i><strong>Clientes e Veículos</strong>
        <i class="fas fa-chevron-down float-right mt-1"></i>
    </div>
    <div id="sec-clientes" class="collapse" style="display:none">
        <div class="card-body">

            <div class="ajuda-item border-bottom pb-3 mb-3" data-tags="cliente cadastrar novo adicionar">
                <h6 class="font-weight-bold mb-1">
                    <i class="fas fa-user-plus text-success mr-2"></i>Como cadastrar um cliente?
                </h6>
                <p class="text-muted mb-0" style="font-size:.9rem">
                    Vá em <strong>Clientes → Novo Cliente</strong>. Informe nome e telefone (obrigatórios).
                    E-mail, CPF/CNPJ e endereço são opcionais. Um cliente pode ter múltiplos veículos.
                    Você também pode criar o cliente diretamente ao abrir um orçamento, sem sair da tela.
                </p>
            </div>

            <div class="ajuda-item border-bottom pb-3 mb-3" data-tags="veículo placa modelo marca cadastrar">
                <h6 class="font-weight-bold mb-1">
                    <i class="fas fa-car text-primary mr-2"></i>Como cadastrar um veículo?
                </h6>
                <p class="text-muted mb-0" style="font-size:.9rem">
                    Vá em <strong>Veículos → Novo Veículo</strong>. Selecione o cliente, informe marca, modelo, ano e placa.
                    A quilometragem atual é opcional mas ajuda no histórico de manutenção.
                    Veículos também podem ser criados ao abrir um orçamento, no campo "Veículo".
                </p>
            </div>

            <div class="ajuda-item" data-tags="ficha histórico cliente veículo">
                <h6 class="font-weight-bold mb-1">
                    <i class="fas fa-history text-info mr-2"></i>Como ver o histórico de um cliente?
                </h6>
                <p class="text-muted mb-0" style="font-size:.9rem">
                    Na lista de clientes, clique em <strong>Ver Ficha</strong>.
                    Você verá todos os veículos, orçamentos, ordens de serviço e agendamentos do cliente.
                </p>
            </div>

        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- ORÇAMENTOS --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div class="card shadow-sm mb-3 ajuda-secao">
    <div class="card-header bg-light py-2 cursor-pointer" data-toggle="collapse" data-target="#sec-orcamentos">
        <i class="fas fa-file-invoice mr-2 text-warning"></i><strong>Orçamentos</strong>
        <i class="fas fa-chevron-down float-right mt-1"></i>
    </div>
    <div id="sec-orcamentos" class="collapse" style="display:none">
        <div class="card-body">

            <div class="ajuda-item border-bottom pb-3 mb-3" data-tags="orçamento criar novo peça mão de obra serviço">
                <h6 class="font-weight-bold mb-1">
                    <i class="fas fa-plus-circle text-success mr-2"></i>Como criar um orçamento?
                </h6>
                <p class="text-muted mb-0" style="font-size:.9rem">
                    Vá em <strong>Orçamentos → Novo Orçamento</strong>. Selecione o cliente e o veículo
                    (ou crie na hora). Depois adicione os itens: <strong>Peças</strong>, <strong>Mão de Obra</strong>
                    e/ou <strong>Serviços</strong> do catálogo. O valor total é calculado automaticamente.
                </p>
            </div>

            <div class="ajuda-item border-bottom pb-3 mb-3" data-tags="orçamento enviar cliente aprovação link portal">
                <h6 class="font-weight-bold mb-1">
                    <i class="fas fa-share-alt text-primary mr-2"></i>Como enviar o orçamento para o cliente aprovar?
                </h6>
                <p class="text-muted mb-0" style="font-size:.9rem">
                    No Kanban, o card do orçamento em <strong>"Aguardando Aprovação"</strong> tem um botão
                    de WhatsApp com a mensagem pronta, incluindo o valor e um link exclusivo.
                    O cliente clica no link, vê o orçamento e pode aprovar ou solicitar alterações
                    diretamente pelo celular, sem precisar baixar nenhum app.
                </p>
            </div>

            <div class="ajuda-item border-bottom pb-3 mb-3" data-tags="orçamento fotos foto upload imagem">
                <h6 class="font-weight-bold mb-1">
                    <i class="fas fa-camera text-info mr-2"></i>Como adicionar fotos ao orçamento?
                </h6>
                <p class="text-muted mb-0" style="font-size:.9rem">
                    Abra o orçamento e use a seção <strong>Fotos</strong> no final da página.
                    Você pode enviar até <strong>5 fotos por orçamento</strong> (formatos: JPG, PNG, WebP).
                    As fotos ficam visíveis para o cliente no portal de acompanhamento.
                </p>
            </div>

            <div class="ajuda-item" data-tags="orçamento PDF imprimir">
                <h6 class="font-weight-bold mb-1">
                    <i class="fas fa-file-pdf text-danger mr-2"></i>Como gerar PDF do orçamento?
                </h6>
                <p class="text-muted mb-0" style="font-size:.9rem">
                    Dentro do orçamento, clique em <strong>Gerar PDF</strong>.
                    O PDF inclui os dados da oficina, cliente, veículo, itens e valor total.
                </p>
            </div>

        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- ORDENS DE SERVIÇO --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div class="card shadow-sm mb-3 ajuda-secao">
    <div class="card-header bg-light py-2 cursor-pointer" data-toggle="collapse" data-target="#sec-os">
        <i class="fas fa-wrench mr-2 text-secondary"></i><strong>Ordens de Serviço (OS)</strong>
        <i class="fas fa-chevron-down float-right mt-1"></i>
    </div>
    <div id="sec-os" class="collapse" style="display:none">
        <div class="card-body">

            <div class="ajuda-item border-bottom pb-3 mb-3" data-tags="ordem serviço OS criar gerar aprovação">
                <h6 class="font-weight-bold mb-1">
                    <i class="fas fa-tools text-warning mr-2"></i>Como gerar uma Ordem de Serviço?
                </h6>
                <p class="text-muted mb-0" style="font-size:.9rem">
                    Quando o orçamento é aprovado (pelo cliente no portal ou por você), clique em
                    <strong>"Gerar OS"</strong>. A OS é criada automaticamente com todos os itens do orçamento.
                    A partir daí, acompanhe o andamento pelo <strong>Kanban</strong>.
                </p>
            </div>

            <div class="ajuda-item border-bottom pb-3 mb-3" data-tags="OS finalizar pagamento concluir">
                <h6 class="font-weight-bold mb-1">
                    <i class="fas fa-check-double text-success mr-2"></i>Como finalizar uma OS com pagamento?
                </h6>
                <p class="text-muted mb-0" style="font-size:.9rem">
                    No Kanban, mova a OS para <strong>"Concluído"</strong> e registre o pagamento.
                    O sistema aceita múltiplas formas de pagamento e registra automaticamente no financeiro.
                    Uma mensagem de WhatsApp com confirmação é gerada para você enviar ao cliente.
                </p>
            </div>

            <div class="ajuda-item" data-tags="OS PDF imprimir">
                <h6 class="font-weight-bold mb-1">
                    <i class="fas fa-print text-secondary mr-2"></i>Como imprimir a Ordem de Serviço?
                </h6>
                <p class="text-muted mb-0" style="font-size:.9rem">
                    Dentro da OS, clique em <strong>PDF</strong>. O documento gerado serve como comprovante
                    para o cliente assinar na entrega.
                </p>
            </div>

        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- KANBAN --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div class="card shadow-sm mb-3 ajuda-secao">
    <div class="card-header bg-light py-2 cursor-pointer" data-toggle="collapse" data-target="#sec-kanban">
        <i class="fas fa-columns mr-2 text-info"></i><strong>Kanban</strong>
        <i class="fas fa-chevron-down float-right mt-1"></i>
    </div>
    <div id="sec-kanban" class="collapse" style="display:none">
        <div class="card-body">

            <div class="ajuda-item border-bottom pb-3 mb-3" data-tags="kanban etapas status fluxo">
                <h6 class="font-weight-bold mb-1">
                    <i class="fas fa-columns text-info mr-2"></i>Quais são as etapas do Kanban?
                </h6>
                <p class="text-muted mb-0" style="font-size:.9rem">
                    O Kanban tem 5 etapas: <strong>Aguardando Aprovação</strong> (orçamento enviado ao cliente),
                    <strong>Em Andamento</strong> (mecânico trabalhando), <strong>Aguardando Peça</strong>
                    (aguardando chegada de peça), <strong>Pronto</strong> (aguardando retirada)
                    e <strong>Concluído</strong> (OS finalizada e paga).
                </p>
            </div>

            <div class="ajuda-item border-bottom pb-3 mb-3" data-tags="kanban mover card arrastar status">
                <h6 class="font-weight-bold mb-1">
                    <i class="fas fa-exchange-alt text-primary mr-2"></i>Como mover um card no Kanban?
                </h6>
                <p class="text-muted mb-0" style="font-size:.9rem">
                    Clique em <strong>Mover</strong> no card ou use o botão de seta para avançar a OS para
                    a próxima etapa. O Kanban atualiza em tempo real para todos os usuários logados.
                </p>
            </div>

            <div class="ajuda-item" data-tags="kanban whatsapp mensagem cliente enviar">
                <h6 class="font-weight-bold mb-1">
                    <i class="fab fa-whatsapp text-success mr-2"></i>Como enviar mensagem de WhatsApp pelo Kanban?
                </h6>
                <p class="text-muted mb-0" style="font-size:.9rem">
                    Cada card tem um botão de WhatsApp com mensagem pré-formatada adequada para aquela etapa.
                    Clique no botão e o WhatsApp Web (ou app) abre com a mensagem já preenchida.
                    Você só precisa confirmar o envio.
                </p>
            </div>

        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- FINANCEIRO --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div class="card shadow-sm mb-3 ajuda-secao">
    <div class="card-header bg-light py-2 cursor-pointer" data-toggle="collapse" data-target="#sec-financeiro">
        <i class="fas fa-dollar-sign mr-2 text-success"></i><strong>Financeiro e Relatórios</strong>
        <i class="fas fa-chevron-down float-right mt-1"></i>
    </div>
    <div id="sec-financeiro" class="collapse" style="display:none">
        <div class="card-body">

            <div class="ajuda-item border-bottom pb-3 mb-3" data-tags="caixa abrir fechar financeiro">
                <h6 class="font-weight-bold mb-1">
                    <i class="fas fa-cash-register text-success mr-2"></i>Como funciona o Caixa?
                </h6>
                <p class="text-muted mb-0" style="font-size:.9rem">
                    Abra o caixa no início do dia em <strong>Caixa → Abrir Caixa</strong>.
                    Ao fechar no final do dia, o sistema mostra o resumo de entradas e saídas.
                    Pagamentos de OS são registrados automaticamente no caixa quando aberto.
                </p>
            </div>

            <div class="ajuda-item border-bottom pb-3 mb-3" data-tags="relatório financeiro exportar PDF Excel">
                <h6 class="font-weight-bold mb-1">
                    <i class="fas fa-chart-bar text-primary mr-2"></i>Como gerar relatórios?
                </h6>
                <p class="text-muted mb-0" style="font-size:.9rem">
                    Em <strong>Relatórios</strong> você encontra: Financeiro (receitas e despesas),
                    Fluxo de Caixa, Lucro por Serviço e Margem por OS.
                    Todos podem ser exportados em <strong>PDF</strong> ou <strong>Excel</strong>.
                </p>
            </div>

            <div class="ajuda-item" data-tags="comissão funcionário pagamento">
                <h6 class="font-weight-bold mb-1">
                    <i class="fas fa-hand-holding-usd text-warning mr-2"></i>Como registrar comissões?
                </h6>
                <p class="text-muted mb-0" style="font-size:.9rem">
                    Em <strong>Comissões</strong> você registra comissões dos funcionários por OS ou manualmente.
                    Filtre por mês e funcionário. Use o botão <strong>✓</strong> para marcar como pago.
                </p>
            </div>

        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- USUÁRIOS E PERFIS --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div class="card shadow-sm mb-3 ajuda-secao">
    <div class="card-header bg-light py-2 cursor-pointer" data-toggle="collapse" data-target="#sec-usuarios">
        <i class="fas fa-user-shield mr-2 text-danger"></i><strong>Usuários e Permissões</strong>
        <i class="fas fa-chevron-down float-right mt-1"></i>
    </div>
    <div id="sec-usuarios" class="collapse" style="display:none">
        <div class="card-body">

            <div class="ajuda-item border-bottom pb-3 mb-3" data-tags="usuário perfil permissão admin gerente operador mecânico">
                <h6 class="font-weight-bold mb-1">
                    <i class="fas fa-id-badge text-primary mr-2"></i>Quais são os perfis de usuário?
                </h6>
                <div style="font-size:.9rem;color:#374151">
                    <p class="mb-2">O PitStop tem 3 perfis principais:</p>
                    <ul class="mb-0 pl-3">
                        <li class="mb-1"><strong>Administrador</strong> — acesso total: configura o sistema, gerencia usuários e assinatura.</li>
                        <li class="mb-1"><strong>Gerente</strong> — acessa tudo exceto configurações do sistema e gestão de admins.</li>
                        <li><strong>Operador</strong> — acesso restrito: visualiza Kanban, agendamentos e lança serviços.</li>
                    </ul>
                </div>
            </div>

            <div class="ajuda-item border-bottom pb-3 mb-3" data-tags="usuário criar senha login novo">
                <h6 class="font-weight-bold mb-1">
                    <i class="fas fa-user-plus text-success mr-2"></i>Como adicionar um funcionário ao sistema?
                </h6>
                <p class="text-muted mb-0" style="font-size:.9rem">
                    Vá em <strong>Usuários → Novo Usuário</strong>. Defina nome, login, senha e perfil.
                    O login não pode conter espaços ou caracteres especiais.
                    Só administradores podem criar outros administradores.
                </p>
            </div>

            <div class="ajuda-item" data-tags="usuário limite plano pro max">
                <h6 class="font-weight-bold mb-1">
                    <i class="fas fa-users-cog text-info mr-2"></i>Quantos usuários posso ter?
                </h6>
                <p class="text-muted mb-0" style="font-size:.9rem">
                    Depende do seu plano:
                    Trial: até <strong>2 usuários</strong>.
                    Plano Pro: até <strong>6 usuários</strong>.
                    Plano Pro Max: até <strong>10 usuários</strong>.
                    Entre em contato caso precise de mais.
                </p>
            </div>

        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- ASSINATURA E PLANOS --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div class="card shadow-sm mb-3 ajuda-secao">
    <div class="card-header bg-light py-2 cursor-pointer" data-toggle="collapse" data-target="#sec-planos">
        <i class="fas fa-credit-card mr-2" style="color:#7c3aed"></i><strong>Assinatura e Planos</strong>
        <i class="fas fa-chevron-down float-right mt-1"></i>
    </div>
    <div id="sec-planos" class="collapse" style="display:none">
        <div class="card-body">

            <div class="ajuda-item border-bottom pb-3 mb-3" data-tags="plano pro max assinar pagamento preço">
                <h6 class="font-weight-bold mb-1">
                    <i class="fas fa-star text-warning mr-2"></i>Quais são os planos disponíveis?
                </h6>
                <div style="font-size:.9rem;color:#374151">
                    <ul class="mb-0 pl-3">
                        <li class="mb-2"><strong>Plano Pro — R$ 99,90/mês</strong>: todos os módulos, até 6 usuários, até 5 fotos por orçamento.</li>
                        <li><strong>Plano Pro Max — R$ 157,50/mês</strong>: tudo do Pro + até 10 usuários.</li>
                    </ul>
                    <p class="mt-2 mb-0">O pagamento é mensal via Pix ou boleto pela plataforma Asaas.</p>
                </div>
            </div>

            <div class="ajuda-item border-bottom pb-3 mb-3" data-tags="assinar pagar plano checkout">
                <h6 class="font-weight-bold mb-1">
                    <i class="fas fa-shopping-cart text-success mr-2"></i>Como assinar um plano?
                </h6>
                <p class="text-muted mb-0" style="font-size:.9rem">
                    Vá em <strong>Assinatura</strong> no menu lateral (disponível para administradores).
                    Escolha o plano e clique em <strong>Assinar</strong>. Você será redirecionado para o
                    ambiente seguro de pagamento da Asaas. Após o pagamento, a conta é ativada automaticamente.
                </p>
            </div>

            <div class="ajuda-item" data-tags="vencimento renovação boleto pix pagamento">
                <h6 class="font-weight-bold mb-1">
                    <i class="fas fa-calendar-check text-primary mr-2"></i>Como funciona a renovação?
                </h6>
                <p class="text-muted mb-0" style="font-size:.9rem">
                    A assinatura é renovada mensalmente. Você receberá um e-mail de lembrete
                    7, 3 e 1 dia antes do vencimento. Após o vencimento, há um período de carência de
                    6 dias com acesso completo. Depois disso, o acesso fica em modo de leitura até a regularização.
                </p>
            </div>

        </div>
    </div>
</div>

</div>{{-- #containerAjuda --}}

{{-- Contato --}}
<div class="card shadow-sm mt-3 mb-4">
    <div class="card-body text-center py-4">
        <i class="fas fa-headset fa-2x text-muted mb-2 d-block"></i>
        <p class="mb-1 font-weight-bold">Não encontrou o que procurava?</p>
        <p class="text-muted mb-3" style="font-size:.9rem">
            Entre em contato com o suporte — respondemos pelo WhatsApp em horário comercial.
        </p>
        <a href="https://wa.me/5581998114585" target="_blank"
           class="btn btn-success btn-sm px-4">
            <i class="fab fa-whatsapp mr-1"></i> Falar com o Suporte
        </a>
    </div>
</div>

@endsection

@push('js')
<script>
function filtrarAjuda(termo) {
    termo = termo.toLowerCase().trim();
    document.querySelectorAll('.ajuda-item').forEach(function(item) {
        var tags   = (item.dataset.tags || '').toLowerCase();
        var texto  = item.textContent.toLowerCase();
        var visivel = !termo || tags.includes(termo) || texto.includes(termo);
        item.style.display = visivel ? '' : 'none';
    });
    // Abre todas as seções se há busca ativa
    if (termo) {
        document.querySelectorAll('[id^="sec-"]').forEach(function(sec) {
            sec.classList.add('show');
        });
    }
}
</script>
@endpush

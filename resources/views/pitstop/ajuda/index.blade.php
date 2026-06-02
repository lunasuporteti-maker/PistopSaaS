@extends('layouts.pitstop')
@section('title', 'Central de Ajuda')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 font-weight-bold"><i class="fas fa-question-circle mr-2 text-primary"></i>Central de Ajuda</h1>
</div>
@endsection

@push('css')
<style>
.ajuda-grupo {
    border: 1px solid rgba(0,0,0,.125);
    border-radius: .25rem;
    margin-bottom: .75rem;
    overflow: hidden;
}
.ajuda-grupo summary {
    list-style: none;
    cursor: pointer;
    padding: .55rem 1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: space-between;
    user-select: none;
}
.ajuda-grupo summary::-webkit-details-marker { display: none; }
.ajuda-grupo summary .chevron {
    transition: transform .2s ease;
    font-size: .8rem;
    color: rgba(0,0,0,.4);
}
.ajuda-grupo[open] > summary .chevron {
    transform: rotate(180deg);
}
.ajuda-grupo[open] > summary {
    border-bottom: 1px solid rgba(0,0,0,.1);
}
.ajuda-grupo .grupo-body {
    padding: 1rem;
    background: #fff;
}
.ajuda-item {
    padding-bottom: .9rem;
    margin-bottom: .9rem;
    border-bottom: 1px solid #f0f0f0;
}
.ajuda-item:last-child {
    padding-bottom: 0;
    margin-bottom: 0;
    border-bottom: none;
}
.ajuda-item h6 {
    font-size: .9rem;
    margin-bottom: .35rem;
}
.ajuda-item p {
    font-size: .875rem;
    color: #555;
    margin: 0;
}
.ajuda-hidden { display: none !important; }
</style>
@endpush

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

{{-- ═══════════════════════════════════════════════ --}}
<details class="ajuda-grupo shadow-sm" open>
    <summary class="bg-primary text-white">
        <span><i class="fas fa-rocket mr-2"></i>Primeiros Passos</span>
        <i class="fas fa-chevron-down chevron"></i>
    </summary>
    <div class="grupo-body">

        <div class="ajuda-item" data-tags="onboarding configuração logo horário">
            <h6 class="font-weight-bold"><i class="fas fa-check-circle text-success mr-2"></i>Como configurar minha oficina?</h6>
            <p>Ao entrar pela primeira vez, o <strong>Assistente de Configuração</strong> será exibido automaticamente.
            Ele guia você em 5 etapas: nome e endereço, logo da oficina, horário de funcionamento,
            primeiro funcionário e catálogo inicial de serviços.
            Você pode abrir novamente em <strong>Configurações → Configuração Inicial</strong>.</p>
        </div>

        <div class="ajuda-item" data-tags="login senha acesso usuário">
            <h6 class="font-weight-bold"><i class="fas fa-key text-warning mr-2"></i>Como fazer login?</h6>
            <p>Acesse <strong>app.iaqueatende.com.br</strong> e use o <strong>login</strong> (nome de usuário)
            e a senha criados no cadastro. O login é o endereço que você escolheu para a oficina,
            por exemplo: <em>gilsons-motos</em>.
            Se esquecer a senha, use o link <strong>"Esqueci minha senha"</strong> na tela de login.</p>
        </div>

        <div class="ajuda-item" data-tags="trial período gratuito plano dias">
            <h6 class="font-weight-bold"><i class="fas fa-hourglass-half text-info mr-2"></i>Como funciona o trial gratuito?</h6>
            <p>Você tem <strong>30 dias gratuitos</strong> com acesso a todas as funcionalidades (com alguns limites de quantidade).
            Ao final do trial, o acesso entra em modo de leitura até você assinar um plano.
            Durante o trial: até 10 clientes, 20 veículos, 15 orçamentos, 2 usuários e 20 peças.</p>
        </div>

    </div>
</details>

{{-- ═══════════════════════════════════════════════ --}}
<details class="ajuda-grupo shadow-sm">
    <summary class="bg-light">
        <span><i class="fas fa-users mr-2 text-primary"></i>Clientes e Veículos</span>
        <i class="fas fa-chevron-down chevron"></i>
    </summary>
    <div class="grupo-body">

        <div class="ajuda-item" data-tags="cliente cadastrar novo adicionar">
            <h6 class="font-weight-bold"><i class="fas fa-user-plus text-success mr-2"></i>Como cadastrar um cliente?</h6>
            <p>Vá em <strong>Clientes → Novo Cliente</strong>. Informe nome e telefone (obrigatórios).
            E-mail, CPF/CNPJ e endereço são opcionais. Um cliente pode ter múltiplos veículos.
            Você também pode criar o cliente diretamente ao abrir um orçamento, sem sair da tela.</p>
        </div>

        <div class="ajuda-item" data-tags="veículo placa modelo marca cadastrar moto carro caminhão">
            <h6 class="font-weight-bold"><i class="fas fa-car text-primary mr-2"></i>Como cadastrar um veículo ou moto?</h6>
            <p>Vá em <strong>Veículos → Novo Veículo</strong>. Selecione o <strong>tipo</strong> (Carro, Moto, Caminhão…)
            — as marcas disponíveis mudam conforme o tipo. Informe cliente, marca, modelo, ano e placa.
            A quilometragem atual é opcional mas ajuda no histórico de manutenção.
            Veículos também podem ser criados ao abrir um orçamento, no campo "Veículo".</p>
        </div>

        <div class="ajuda-item" data-tags="ficha histórico cliente veículo">
            <h6 class="font-weight-bold"><i class="fas fa-history text-info mr-2"></i>Como ver o histórico de um cliente?</h6>
            <p>Na lista de clientes, clique em <strong>Ver Ficha</strong>.
            Você verá todos os veículos, orçamentos, ordens de serviço e agendamentos do cliente.</p>
        </div>

    </div>
</details>

{{-- ═══════════════════════════════════════════════ --}}
<details class="ajuda-grupo shadow-sm">
    <summary class="bg-light">
        <span><i class="fas fa-file-invoice mr-2 text-warning"></i>Orçamentos</span>
        <i class="fas fa-chevron-down chevron"></i>
    </summary>
    <div class="grupo-body">

        <div class="ajuda-item" data-tags="orçamento criar novo peça mão de obra serviço">
            <h6 class="font-weight-bold"><i class="fas fa-plus-circle text-success mr-2"></i>Como criar um orçamento?</h6>
            <p>Vá em <strong>Orçamentos → Novo Orçamento</strong>. Selecione o cliente e o veículo
            (ou crie na hora). Depois adicione os itens: <strong>Peças</strong>, <strong>Mão de Obra</strong>
            e/ou <strong>Serviços</strong> do catálogo. O valor total é calculado automaticamente.</p>
        </div>

        <div class="ajuda-item" data-tags="orçamento enviar cliente aprovação link portal">
            <h6 class="font-weight-bold"><i class="fas fa-share-alt text-primary mr-2"></i>Como enviar o orçamento para o cliente aprovar?</h6>
            <p>No Kanban, o card do orçamento em <strong>"Aguardando Aprovação"</strong> tem um botão
            de WhatsApp com a mensagem pronta, incluindo o valor e um link exclusivo.
            O cliente clica no link, vê o orçamento e pode aprovar ou solicitar alterações
            diretamente pelo celular, sem precisar baixar nenhum app.</p>
        </div>

        <div class="ajuda-item" data-tags="orçamento fotos foto upload imagem">
            <h6 class="font-weight-bold"><i class="fas fa-camera text-info mr-2"></i>Como adicionar fotos ao orçamento?</h6>
            <p>Abra o orçamento e use a seção <strong>Fotos</strong> no final da página.
            Você pode enviar até <strong>5 fotos por orçamento</strong> (formatos: JPG, PNG, WebP).
            As fotos ficam visíveis para o cliente no portal de acompanhamento.</p>
        </div>

        <div class="ajuda-item" data-tags="orçamento PDF imprimir">
            <h6 class="font-weight-bold"><i class="fas fa-file-pdf text-danger mr-2"></i>Como gerar PDF do orçamento?</h6>
            <p>Dentro do orçamento, clique em <strong>Gerar PDF</strong>.
            O PDF inclui os dados da oficina, cliente, veículo, itens e valor total.</p>
        </div>

    </div>
</details>

{{-- ═══════════════════════════════════════════════ --}}
<details class="ajuda-grupo shadow-sm">
    <summary class="bg-light">
        <span><i class="fas fa-wrench mr-2 text-secondary"></i>Ordens de Serviço (OS)</span>
        <i class="fas fa-chevron-down chevron"></i>
    </summary>
    <div class="grupo-body">

        <div class="ajuda-item" data-tags="ordem serviço OS criar gerar aprovação">
            <h6 class="font-weight-bold"><i class="fas fa-tools text-warning mr-2"></i>Como gerar uma Ordem de Serviço?</h6>
            <p>Quando o orçamento é aprovado (pelo cliente no portal ou por você), clique em
            <strong>"Gerar OS"</strong>. A OS é criada automaticamente com todos os itens do orçamento.
            A partir daí, acompanhe o andamento pelo <strong>Kanban</strong>.</p>
        </div>

        <div class="ajuda-item" data-tags="OS finalizar pagamento concluir">
            <h6 class="font-weight-bold"><i class="fas fa-check-double text-success mr-2"></i>Como finalizar uma OS com pagamento?</h6>
            <p>No Kanban, mova a OS para <strong>"Concluído"</strong> e registre o pagamento.
            O sistema aceita múltiplas formas de pagamento e registra automaticamente no financeiro.
            Uma mensagem de WhatsApp com confirmação é gerada para você enviar ao cliente.</p>
        </div>

        <div class="ajuda-item" data-tags="OS PDF imprimir">
            <h6 class="font-weight-bold"><i class="fas fa-print text-secondary mr-2"></i>Como imprimir a Ordem de Serviço?</h6>
            <p>Dentro da OS, clique em <strong>PDF</strong>. O documento gerado serve como comprovante
            para o cliente assinar na entrega.</p>
        </div>

    </div>
</details>

{{-- ═══════════════════════════════════════════════ --}}
<details class="ajuda-grupo shadow-sm">
    <summary class="bg-light">
        <span><i class="fas fa-columns mr-2 text-info"></i>Kanban</span>
        <i class="fas fa-chevron-down chevron"></i>
    </summary>
    <div class="grupo-body">

        <div class="ajuda-item" data-tags="kanban etapas status fluxo">
            <h6 class="font-weight-bold"><i class="fas fa-columns text-info mr-2"></i>Quais são as etapas do Kanban?</h6>
            <p>O Kanban tem 5 etapas: <strong>Aguardando Aprovação</strong> (orçamento enviado ao cliente),
            <strong>Em Andamento</strong> (mecânico trabalhando), <strong>Aguardando Peça</strong>
            (aguardando chegada de peça), <strong>Pronto</strong> (aguardando retirada)
            e <strong>Concluído</strong> (OS finalizada e paga).</p>
        </div>

        <div class="ajuda-item" data-tags="kanban mover card arrastar status">
            <h6 class="font-weight-bold"><i class="fas fa-exchange-alt text-primary mr-2"></i>Como mover um card no Kanban?</h6>
            <p>Clique em <strong>Mover</strong> no card ou use o botão de seta para avançar a OS para
            a próxima etapa. O Kanban atualiza em tempo real para todos os usuários logados.</p>
        </div>

        <div class="ajuda-item" data-tags="kanban whatsapp mensagem cliente enviar">
            <h6 class="font-weight-bold"><i class="fab fa-whatsapp text-success mr-2"></i>Como enviar mensagem de WhatsApp pelo Kanban?</h6>
            <p>Cada card tem um botão de WhatsApp com mensagem pré-formatada adequada para aquela etapa.
            Clique no botão e o WhatsApp Web (ou app) abre com a mensagem já preenchida.
            Você só precisa confirmar o envio.</p>
        </div>

    </div>
</details>

{{-- ═══════════════════════════════════════════════ --}}
<details class="ajuda-grupo shadow-sm">
    <summary class="bg-light">
        <span><i class="fas fa-dollar-sign mr-2 text-success"></i>Financeiro e Relatórios</span>
        <i class="fas fa-chevron-down chevron"></i>
    </summary>
    <div class="grupo-body">

        <div class="ajuda-item" data-tags="caixa abrir fechar financeiro">
            <h6 class="font-weight-bold"><i class="fas fa-cash-register text-success mr-2"></i>Como funciona o Caixa?</h6>
            <p>Abra o caixa no início do dia em <strong>Caixa → Abrir Caixa</strong>.
            Ao fechar no final do dia, o sistema mostra o resumo de entradas e saídas.
            Pagamentos de OS são registrados automaticamente no caixa quando aberto.</p>
        </div>

        <div class="ajuda-item" data-tags="relatório financeiro exportar PDF Excel">
            <h6 class="font-weight-bold"><i class="fas fa-chart-bar text-primary mr-2"></i>Como gerar relatórios?</h6>
            <p>Em <strong>Relatórios</strong> você encontra: Financeiro (receitas e despesas),
            Fluxo de Caixa, Lucro por Serviço e Margem por OS.
            Todos podem ser exportados em <strong>PDF</strong> ou <strong>Excel</strong>.</p>
        </div>

        <div class="ajuda-item" data-tags="comissão funcionário pagamento">
            <h6 class="font-weight-bold"><i class="fas fa-hand-holding-usd text-warning mr-2"></i>Como registrar comissões?</h6>
            <p>Em <strong>Comissões</strong> você registra comissões dos funcionários por OS ou manualmente.
            Filtre por mês e funcionário. Use o botão <strong>✓</strong> para marcar como pago.</p>
        </div>

    </div>
</details>

{{-- ═══════════════════════════════════════════════ --}}
<details class="ajuda-grupo shadow-sm">
    <summary class="bg-light">
        <span><i class="fas fa-user-shield mr-2 text-danger"></i>Usuários e Permissões</span>
        <i class="fas fa-chevron-down chevron"></i>
    </summary>
    <div class="grupo-body">

        <div class="ajuda-item" data-tags="usuário perfil permissão admin gerente operador mecânico">
            <h6 class="font-weight-bold"><i class="fas fa-id-badge text-primary mr-2"></i>Quais são os perfis de usuário?</h6>
            <div style="font-size:.875rem;color:#555">
                <p class="mb-2">O PitStop tem 3 perfis principais:</p>
                <ul class="mb-0 pl-3">
                    <li class="mb-1"><strong>Administrador</strong> — acesso total: configura o sistema, gerencia usuários e assinatura.</li>
                    <li class="mb-1"><strong>Gerente</strong> — acessa tudo exceto configurações do sistema e gestão de admins.</li>
                    <li><strong>Operador</strong> — acesso restrito: visualiza Kanban, agendamentos e lança serviços.</li>
                </ul>
            </div>
        </div>

        <div class="ajuda-item" data-tags="usuário criar senha login novo">
            <h6 class="font-weight-bold"><i class="fas fa-user-plus text-success mr-2"></i>Como adicionar um funcionário ao sistema?</h6>
            <p>Vá em <strong>Usuários → Novo Usuário</strong>. Defina nome, login, senha e perfil.
            O login não pode conter espaços ou caracteres especiais.
            Só administradores podem criar outros administradores.</p>
        </div>

        <div class="ajuda-item" data-tags="usuário limite plano pro max">
            <h6 class="font-weight-bold"><i class="fas fa-users-cog text-info mr-2"></i>Quantos usuários posso ter?</h6>
            <p>Depende do seu plano:
            Trial: até <strong>2 usuários</strong>.
            Plano Pro: até <strong>6 usuários</strong>.
            Plano Pro Max: até <strong>10 usuários</strong>.
            Entre em contato caso precise de mais.</p>
        </div>

    </div>
</details>

{{-- ═══════════════════════════════════════════════ --}}
<details class="ajuda-grupo shadow-sm">
    <summary class="bg-light">
        <span><i class="fas fa-credit-card mr-2" style="color:#7c3aed"></i>Assinatura e Planos</span>
        <i class="fas fa-chevron-down chevron"></i>
    </summary>
    <div class="grupo-body">

        <div class="ajuda-item" data-tags="plano pro max assinar pagamento preço">
            <h6 class="font-weight-bold"><i class="fas fa-star text-warning mr-2"></i>Quais são os planos disponíveis?</h6>
            <div style="font-size:.875rem;color:#555">
                <ul class="mb-0 pl-3">
                    <li class="mb-2"><strong>Plano Pro — R$ 99,90/mês</strong>: todos os módulos, até 6 usuários, até 5 fotos por orçamento.</li>
                    <li><strong>Plano Pro Max — R$ 157,50/mês</strong>: tudo do Pro + até 10 usuários.</li>
                </ul>
                <p class="mt-2 mb-0">O pagamento é mensal via Pix ou boleto pela plataforma Asaas.</p>
            </div>
        </div>

        <div class="ajuda-item" data-tags="assinar pagar plano checkout">
            <h6 class="font-weight-bold"><i class="fas fa-shopping-cart text-success mr-2"></i>Como assinar um plano?</h6>
            <p>Vá em <strong>Assinatura</strong> no menu lateral (disponível para administradores).
            Escolha o plano e clique em <strong>Assinar</strong>. Você será redirecionado para o
            ambiente seguro de pagamento da Asaas. Após o pagamento, a conta é ativada automaticamente.</p>
        </div>

        <div class="ajuda-item" data-tags="vencimento renovação boleto pix pagamento">
            <h6 class="font-weight-bold"><i class="fas fa-calendar-check text-primary mr-2"></i>Como funciona a renovação?</h6>
            <p>A assinatura é renovada mensalmente. Você receberá um e-mail de lembrete
            7, 3 e 1 dia antes do vencimento. Após o vencimento, há um período de carência de
            6 dias com acesso completo. Depois disso, o acesso fica em modo de leitura até a regularização.</p>
        </div>

    </div>
</details>

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
    var grupos = document.querySelectorAll('.ajuda-grupo');
    grupos.forEach(function(grupo) {
        var itens = grupo.querySelectorAll('.ajuda-item');
        var grupoVisivel = false;
        itens.forEach(function(item) {
            var tags  = (item.dataset.tags || '').toLowerCase();
            var texto = item.textContent.toLowerCase();
            var vis   = !termo || tags.includes(termo) || texto.includes(termo);
            item.classList.toggle('ajuda-hidden', !vis);
            if (vis) grupoVisivel = true;
        });
        grupo.classList.toggle('ajuda-hidden', !!termo && !grupoVisivel);
        if (termo && grupoVisivel) grupo.setAttribute('open', '');
    });
}
</script>
@endpush

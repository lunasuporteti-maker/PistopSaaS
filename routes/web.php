<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminTenantController;
use App\Http\Controllers\Admin\WebhookAsaasController;
use App\Http\Controllers\Public\PublicSignupController;
use App\Http\Controllers\Web\AcompanhamentoPublicoController;
use App\Http\Controllers\Web\AgendamentoWebController;
use App\Http\Controllers\Web\CaixaWebController;
use App\Http\Controllers\Web\CatalogoServicosWebController;
use App\Http\Controllers\Web\ClienteWebController;
use App\Http\Controllers\Web\ConfiguracaoWebController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\FinanceiroWebController;
use App\Http\Controllers\Web\FuncionarioWebController;
use App\Http\Controllers\Web\JsonController;
use App\Http\Controllers\Web\KanbanController;
use App\Http\Controllers\Web\LembreteWebController;
use App\Http\Controllers\Web\MaoDeObraWebController;
use App\Http\Controllers\Web\OrcamentoWebController;
use App\Http\Controllers\Web\OrdemServicoWebController;
use App\Http\Controllers\Web\ParceiroWebController;
use App\Http\Controllers\Web\PdfController;
use App\Http\Controllers\Web\PecaWebController;
use App\Http\Controllers\Web\PerfilWebController;
use App\Http\Controllers\Web\PlanoController;
use App\Http\Controllers\Web\RelatorioWebController;
use App\Http\Controllers\Web\UsuarioWebController;
use App\Http\Controllers\Web\VeiculoWebController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

// Rotas públicas — sem login
Route::middleware('throttle:30,1')->group(function () {
    Route::get('/acompanhar/{token}', [AcompanhamentoPublicoController::class, 'show'])->name('acompanhar.publico');
    Route::get('/publico/orcamento/{token}/pdf', [PdfController::class, 'orcamentoPublico'])->name('orcamentos.pdf.publico');
});

// ── Onboarding self-service público (PRD 03, Story 4.2) ───────────────────
// Subdomínio principal (app.iaqueatende.com.br), sem middleware tenant/auth.
Route::middleware('throttle:30,1')->group(function () {
    Route::get('/cadastro', [PublicSignupController::class, 'create'])->name('cadastro.form');
    Route::get('/cadastro/verificar-slug', [PublicSignupController::class, 'verificarSlug'])->name('cadastro.verificar-slug');
    Route::get('/cadastro/confirmacao', [PublicSignupController::class, 'confirmacao'])->name('cadastro.confirmacao');
});

// AC6 — POST com rate limit dedicado (5/hora por IP).
Route::middleware('throttle:signup')->group(function () {
    Route::post('/cadastro', [PublicSignupController::class, 'store'])->name('cadastro.store');
});

// AC9 — reenvio de e-mail com throttle próprio (máx 3/hora por IP).
Route::middleware('throttle:3,60')->group(function () {
    Route::post('/cadastro/reenviar-email', [PublicSignupController::class, 'reenviarEmail'])->name('cadastro.reenviar-email');
});

Route::get('/', fn () => redirect()->route('dashboard'));
Route::get('/home', fn () => redirect()->route('dashboard'));

Route::middleware(['tenant', 'auth', 'single.session', 'restrict.mecanico', 'check.subscription'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Perfil do usuário logado
    Route::get('/perfil', [PerfilWebController::class, 'edit'])->name('perfil.edit');
    Route::patch('/perfil', [PerfilWebController::class, 'updateDados'])->name('perfil.update.dados');
    Route::put('/perfil', [PerfilWebController::class, 'update'])->name('perfil.update');

    // Kanban
    Route::get('/kanban', [KanbanController::class, 'index'])->name('kanban');
    Route::get('/kanban/estado', [KanbanController::class, 'estado'])->name('kanban.estado');
    Route::patch('/kanban/{orcamento}/status', [KanbanController::class, 'updateStatus'])->name('kanban.status');
    Route::post('/kanban/{orcamento}/arquivar', [KanbanController::class, 'arquivar'])->name('kanban.arquivar');
    Route::post('/kanban/{orcamento}/concluir', [KanbanController::class, 'concluirComPagamento'])->name('kanban.concluir');
    Route::patch('/kanban/{orcamento}/andamento', [KanbanController::class, 'registrarAndamento'])->name('kanban.andamento');

    // Rotas JSON para selects dinâmicos (sessão web, sem token)
    Route::get('/json/veiculos-por-cliente/{clienteId}', [JsonController::class, 'veiculosPorCliente'])->name('json.veiculos-por-cliente');
    Route::post('/json/clientes', [JsonController::class, 'storeCliente'])->name('json.clientes.store');
    Route::post('/json/veiculos', [JsonController::class, 'storeVeiculo'])->name('json.veiculos.store');

    // Operacional
    Route::get('/fila', [OrdemServicoWebController::class, 'fila'])->name('fila');
    Route::resource('agendamentos', AgendamentoWebController::class);
    Route::patch('agendamentos/{agendamento}/concluir', [AgendamentoWebController::class, 'concluir'])->name('agendamentos.concluir');
    Route::post('agendamentos/{agendamento}/iniciar-servico', [AgendamentoWebController::class, 'iniciarServico'])->name('agendamentos.iniciar-servico');
    Route::resource('orcamentos', OrcamentoWebController::class);
    Route::post('orcamentos/{orcamento}/aprovar', [OrcamentoWebController::class, 'aprovar'])->name('orcamentos.aprovar');
    Route::post('orcamentos/{orcamento}/gerar-os', [OrcamentoWebController::class, 'gerarOs'])->name('orcamentos.gerar-os');
    Route::post('orcamentos/{orcamento}/servicos', [OrcamentoWebController::class, 'addServico'])->name('orcamentos.servicos.add');
    Route::delete('orcamentos/{orcamento}/servicos/{servico}', [OrcamentoWebController::class, 'removeServico'])->name('orcamentos.servicos.remove');
    Route::post('orcamentos/{orcamento}/pecas', [OrcamentoWebController::class, 'addPeca'])->name('orcamentos.pecas.add');
    Route::delete('orcamentos/{orcamento}/pecas/{peca}', [OrcamentoWebController::class, 'removePeca'])->name('orcamentos.pecas.remove');
    Route::post('orcamentos/{orcamento}/mao-de-obra', [OrcamentoWebController::class, 'addMaoDeObra'])->name('orcamentos.mdo.add');
    Route::delete('orcamentos/{orcamento}/mao-de-obra/{maoDeObra}', [OrcamentoWebController::class, 'removeMaoDeObra'])->name('orcamentos.mdo.remove');
    Route::resource('ordens', OrdemServicoWebController::class)
        ->except(['create', 'store'])
        ->parameters(['ordens' => 'ordem']);
    Route::post('ordens/{ordem}/finalizar', [OrdemServicoWebController::class, 'finalizar'])->name('ordens.finalizar');

    // Cadastros
    Route::resource('clientes', ClienteWebController::class);
    Route::get('clientes/{cliente}/ficha', [ClienteWebController::class, 'ficha'])->name('clientes.ficha');
    Route::resource('veiculos', VeiculoWebController::class);
    Route::resource('pecas', PecaWebController::class);
    Route::resource('mao-de-obra', MaoDeObraWebController::class)->except(['show'])->parameters(['mao-de-obra' => 'maoDeObra']);
    Route::resource('catalogo-servicos', CatalogoServicosWebController::class)->except(['show'])->parameters(['catalogo-servicos' => 'catalogoServico']);
    Route::resource('funcionarios', FuncionarioWebController::class)->except(['show']);
    Route::resource('parceiros', ParceiroWebController::class)->except(['show']);

    // Financeiro
    Route::get('/financeiro', [FinanceiroWebController::class, 'index'])->name('financeiro.index');
    Route::post('/financeiro', [FinanceiroWebController::class, 'store'])->name('financeiro.store');
    Route::put('/financeiro/{item}', [FinanceiroWebController::class, 'update'])->name('financeiro.update');
    Route::delete('/financeiro/{item}', [FinanceiroWebController::class, 'destroy'])->name('financeiro.destroy');

    // Caixa — abertura e fechamento (#13)
    Route::get('/caixa', [CaixaWebController::class, 'index'])->name('caixa.index');
    Route::post('/caixa/abrir', [CaixaWebController::class, 'abrir'])->name('caixa.abrir');
    Route::post('/caixa/{caixa}/fechar', [CaixaWebController::class, 'fechar'])->name('caixa.fechar');

    // PDFs (#20, #21)
    Route::get('/orcamentos/{orcamento}/pdf', [PdfController::class, 'orcamento'])->name('orcamentos.pdf');
    Route::get('/ordens/{ordem}/pdf', [PdfController::class, 'ordemServico'])->name('ordens.pdf');
    Route::get('/lembretes', [LembreteWebController::class, 'index'])->name('lembretes.index');
    Route::post('/lembretes', [LembreteWebController::class, 'store'])->name('lembretes.store');
    Route::get('/lembretes/{lembrete}/edit', [LembreteWebController::class, 'edit'])->name('lembretes.edit');
    Route::put('/lembretes/{lembrete}', [LembreteWebController::class, 'update'])->name('lembretes.update.full');
    Route::patch('/lembretes/{lembrete}', [LembreteWebController::class, 'update'])->name('lembretes.update');
    Route::delete('/lembretes/{lembrete}', [LembreteWebController::class, 'destroy'])->name('lembretes.destroy');

    // Download assíncrono de relatórios Excel (via fila Redis)
    Route::get('/exports/download/{key}', function (string $key) {
        $path = cache()->get("export:{$key}");
        if (! $path || ! Storage::exists($path)) {
            return back()->with('error', 'Arquivo não pronto ou expirado. Aguarde alguns instantes e tente novamente.');
        }

        return Storage::download($path);
    })->name('exports.download');

    // Relatórios
    Route::prefix('relatorios')->name('relatorios.')->group(function () {
        Route::get('/financeiro', [RelatorioWebController::class, 'financeiro'])->name('financeiro');
        Route::get('/financeiro/export', [RelatorioWebController::class, 'exportFinanceiro'])->name('financeiro.export');
        Route::get('/financeiro/pdf', [RelatorioWebController::class, 'exportFinanceiroPdf'])->name('financeiro.pdf');
        Route::get('/fluxo-caixa', [RelatorioWebController::class, 'fluxoCaixa'])->name('fluxo-caixa');
        Route::get('/fluxo-caixa/export', [RelatorioWebController::class, 'exportFluxoCaixa'])->name('fluxo-caixa.export');
        Route::get('/fluxo-caixa/pdf', [RelatorioWebController::class, 'exportFluxoCaixaPdf'])->name('fluxo-caixa.pdf');
        Route::get('/lucro-servico', [RelatorioWebController::class, 'lucroServico'])->name('lucro-servico');
        Route::get('/lucro-servico/export', [RelatorioWebController::class, 'exportLucroServico'])->name('lucro-servico.export');
        Route::get('/lucro-servico/pdf', [RelatorioWebController::class, 'exportLucroServicoPdf'])->name('lucro-servico.pdf');
    });

    // Usuários
    Route::resource('usuarios', UsuarioWebController::class)->except(['show']);
    Route::post('usuarios/{usuario}/desbloquear', [UsuarioWebController::class, 'desbloquear'])->name('usuarios.desbloquear');

    // Configurações (apenas admin)
    Route::get('/configuracoes', [ConfiguracaoWebController::class, 'index'])->name('configuracoes.index');
    Route::post('/configuracoes', [ConfiguracaoWebController::class, 'update'])->name('configuracoes.update');
});

// ── Página de assinatura (tenant logado, sem plano ativo) ─────────────────
Route::middleware(['tenant', 'auth'])->group(function () {
    Route::get('/assine', [PlanoController::class, 'index'])->name('assine');
});

// ── Painel Admin IAQueAtende (super_admin apenas, sem tenant middleware) ───
Route::prefix('admin')->name('admin.')->middleware(['auth', 'super.admin'])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/tenants', [AdminTenantController::class, 'index'])->name('tenants.index');
    Route::get('/tenants/{tenant}', [AdminTenantController::class, 'show'])->name('tenants.show');
    Route::post('/tenants/{tenant}/extender-trial', [AdminTenantController::class, 'extenderTrial'])->name('tenants.extender-trial');
    Route::post('/tenants/{tenant}/toggle-plano', [AdminTenantController::class, 'togglePlano'])->name('tenants.toggle-plano');
    Route::post('/tenants/{tenant}/toggle-ativo', [AdminTenantController::class, 'toggleAtivo'])->name('tenants.toggle-ativo');
});

// ── Webhook Asaas (público, sem auth — validar via token de cabeçalho) ────
Route::post('/webhooks/asaas', [WebhookAsaasController::class, 'handle'])
    ->middleware('throttle:60,1')
    ->name('webhooks.asaas');

require __DIR__.'/auth.php';

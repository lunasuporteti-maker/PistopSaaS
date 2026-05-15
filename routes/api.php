<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\VeiculoController;
use App\Http\Controllers\Api\OrcamentoController;
use App\Http\Controllers\Api\OrdemServicoController;
use App\Http\Controllers\Api\PecaController;
use App\Http\Controllers\Api\AgendamentoController;
use App\Http\Controllers\Api\FuncionarioController;
use App\Http\Controllers\Api\ParceiroController;
use App\Http\Controllers\Api\PagamentoSaidaController;
use App\Http\Controllers\Api\MaoDeObraController;
use App\Http\Controllers\Api\CatalogoServicoController;
use App\Http\Controllers\Api\LembreteController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\RelatorioController;
use App\Http\Controllers\Api\SyncController;
use App\Http\Controllers\Api\PortalController;

// ── Health check (para Docker/Coolify) — sem tenant ───────────
Route::get('health', function () {
    return response()->json(['status' => 'ok', 'timestamp' => now()->toIso8601String()]);
});

// ── Rotas com identificação de tenant ─────────────────────────
Route::middleware('tenant')->group(function () {

// ── Rota pública (cliente acompanhar OS) ──────────────────────
Route::get('acompanhar/{numeroOs}', [PortalController::class, 'acompanhar']);

// ── Autenticação ──────────────────────────────────────────────
Route::post('login', [AuthController::class, 'login'])
    ->middleware('throttle:5,15');

// ── Rotas protegidas ──────────────────────────────────────────
Route::middleware('auth:sanctum')->name('api.')->group(function () {

    Route::post('logout',         [AuthController::class, 'logout'])->name('logout');
    Route::get('me',              [AuthController::class, 'me'])->name('me');
    Route::post('alterar-senha',  [AuthController::class, 'alterarSenha'])->name('alterar-senha');

    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Clientes
    Route::get('clientes/sem-retorno',     [ClienteController::class, 'semRetorno'])->name('clientes.sem-retorno');
    Route::get('clientes/{cliente}/ficha', [ClienteController::class, 'ficha'])->name('clientes.ficha');
    Route::apiResource('clientes', ClienteController::class);

    // Veículos
    Route::get('veiculos/cliente/{clienteId}',    [VeiculoController::class, 'porCliente'])->name('veiculos.por-cliente');
    Route::get('veiculos/{veiculo}/historico-km', [VeiculoController::class, 'historicoKm'])->name('veiculos.historico-km');
    Route::apiResource('veiculos', VeiculoController::class);

    // Orçamentos
    Route::get('orcamentos/{orcamento}/detalhes',  [OrcamentoController::class, 'detalhes'])->name('orcamentos.detalhes');
    Route::put('orcamentos/{orcamento}/status',    [OrcamentoController::class, 'atualizarStatus'])->name('orcamentos.status');
    Route::post('orcamentos/{orcamento}/gerar-os', [OrcamentoController::class, 'gerarOs'])->name('orcamentos.gerar-os');
    Route::apiResource('orcamentos', OrcamentoController::class);

    // Ordens de Serviço
    Route::get('ordens/{ordemServico}/detalhes',   [OrdemServicoController::class, 'detalhes'])->name('ordens.detalhes');
    Route::post('ordens/{ordemServico}/finalizar', [OrdemServicoController::class, 'finalizar'])->name('ordens.finalizar');
    Route::apiResource('ordens', OrdemServicoController::class)
        ->only(['index', 'show', 'destroy']);

    // Estoque
    Route::apiResource('pecas', PecaController::class)->except(['show']);

    // Agendamentos
    Route::apiResource('agendamentos', AgendamentoController::class)->except(['show']);

    // Equipe
    Route::apiResource('funcionarios', FuncionarioController::class)->except(['show']);
    Route::apiResource('parceiros',    ParceiroController::class)->except(['show']);

    // Financeiro
    Route::apiResource('pagamentos-saida', PagamentoSaidaController::class)
        ->only(['index', 'store', 'destroy']);

    // Tabelas de preços
    Route::apiResource('mao-de-obra',       MaoDeObraController::class)->except(['show']);
    Route::apiResource('catalogo-servicos', CatalogoServicoController::class)->except(['show']);

    // Lembretes
    Route::get('lembretes',              [LembreteController::class, 'index'])->name('lembretes.index');
    Route::put('lembretes/{lembrete}',   [LembreteController::class, 'update'])->name('lembretes.update');

    // Relatórios
    Route::prefix('relatorio')->name('relatorio.')->group(function () {
        Route::get('financeiro',       [RelatorioController::class, 'financeiro'])->name('financeiro');
        Route::get('fluxo-caixa',      [RelatorioController::class, 'fluxoCaixa'])->name('fluxo-caixa');
        Route::get('lucro-servico',    [RelatorioController::class, 'lucroServico'])->name('lucro-servico');
        Route::get('saidas-categoria', [RelatorioController::class, 'saidasCategoria'])->name('saidas-categoria');
        Route::get('detalhado',        [RelatorioController::class, 'detalhado'])->name('detalhado');
    });

    // Sincronização offline
    Route::prefix('sync')->name('sync.')->group(function () {
        Route::get('status', [SyncController::class, 'status'])->name('status');
        Route::get('pull',   [SyncController::class, 'pull'])->name('pull');
        Route::post('push',  [SyncController::class, 'push'])->name('push');
    });

}); // auth:sanctum

}); // tenant

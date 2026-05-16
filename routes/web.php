<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ClienteWebController;
use App\Http\Controllers\Web\VeiculoWebController;
use App\Http\Controllers\Web\OrcamentoWebController;
use App\Http\Controllers\Web\OrdemServicoWebController;
use App\Http\Controllers\Web\PecaWebController;
use App\Http\Controllers\Web\AgendamentoWebController;
use App\Http\Controllers\Web\FuncionarioWebController;
use App\Http\Controllers\Web\ParceiroWebController;
use App\Http\Controllers\Web\MaoDeObraWebController;
use App\Http\Controllers\Web\CatalogoServicosWebController;
use App\Http\Controllers\Web\FinanceiroWebController;
use App\Http\Controllers\Web\LembreteWebController;
use App\Http\Controllers\Web\RelatorioWebController;
use App\Http\Controllers\Web\UsuarioWebController;
use App\Http\Controllers\Web\KanbanController;
use App\Http\Controllers\Web\PerfilWebController;
use App\Http\Controllers\Web\CaixaWebController;
use App\Http\Controllers\Web\PdfController;
use App\Http\Controllers\Web\AcompanhamentoPublicoController;

// Rota pública — acompanhamento do serviço (sem login) #17
Route::get('/acompanhar/{token}', [AcompanhamentoPublicoController::class, 'show'])->name('acompanhar.publico');

Route::get('/', fn() => redirect()->route('dashboard'));

Route::middleware(['tenant', 'auth', 'single.session', 'restrict.mecanico'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Perfil do usuário logado
    Route::get('/perfil',  [PerfilWebController::class, 'edit'])->name('perfil.edit');
    Route::put('/perfil',  [PerfilWebController::class, 'update'])->name('perfil.update');

    // Kanban
    Route::get('/kanban',                        [KanbanController::class, 'index'])->name('kanban');
    Route::get('/kanban/estado',                 [KanbanController::class, 'estado'])->name('kanban.estado');
    Route::patch('/kanban/{orcamento}/status',   [KanbanController::class, 'updateStatus'])->name('kanban.status');
    Route::post('/kanban/{orcamento}/arquivar',  [KanbanController::class, 'arquivar'])->name('kanban.arquivar');

    // Rotas JSON para selects dinâmicos (sessão web, sem token)
    Route::get('/json/veiculos-por-cliente/{clienteId}', function ($clienteId) {
        $veiculos = \App\Models\Veiculo::where('cliente_id', $clienteId)
            ->select('id','marca','modelo','placa','ano')
            ->orderBy('modelo')->get();
        return response()->json($veiculos);
    })->name('json.veiculos-por-cliente');

    Route::post('/json/clientes', function (\Illuminate\Http\Request $request) {
        $data = $request->validate(['nome'=>'required|string|max:100','telefone'=>'nullable|string|max:20','cpf'=>'nullable|string|max:14']);
        $c = \App\Models\Cliente::create($data);
        return response()->json($c);
    })->name('json.clientes.store');

    Route::post('/json/veiculos', function (\Illuminate\Http\Request $request) {
        $data = $request->validate(['cliente_id'=>'required|exists:clientes,id','marca'=>'nullable|string|max:50','modelo'=>'nullable|string|max:100','ano'=>'nullable|integer','placa'=>'nullable|string|max:20','cor'=>'nullable|string|max:30']);
        $v = \App\Models\Veiculo::create($data);
        return response()->json($v);
    })->name('json.veiculos.store');

    // Operacional
    Route::get('/fila',       [OrdemServicoWebController::class, 'fila'])->name('fila');
    Route::resource('agendamentos',  AgendamentoWebController::class);
    Route::resource('orcamentos',    OrcamentoWebController::class);
    Route::post('orcamentos/{orcamento}/aprovar',    [OrcamentoWebController::class, 'aprovar'])->name('orcamentos.aprovar');
    Route::post('orcamentos/{orcamento}/gerar-os',   [OrcamentoWebController::class, 'gerarOs'])->name('orcamentos.gerar-os');
    Route::post('orcamentos/{orcamento}/servicos',   [OrcamentoWebController::class, 'addServico'])->name('orcamentos.servicos.add');
    Route::delete('orcamentos/{orcamento}/servicos/{servico}', [OrcamentoWebController::class, 'removeServico'])->name('orcamentos.servicos.remove');
    Route::resource('ordens', OrdemServicoWebController::class)
        ->except(['create', 'store'])
        ->parameters(['ordens' => 'ordem']);
    Route::post('ordens/{ordem}/finalizar', [OrdemServicoWebController::class, 'finalizar'])->name('ordens.finalizar');

    // Cadastros
    Route::resource('clientes', ClienteWebController::class);
    Route::get('clientes/{cliente}/ficha', [ClienteWebController::class, 'ficha'])->name('clientes.ficha');
    Route::resource('veiculos', VeiculoWebController::class);
    Route::resource('pecas',    PecaWebController::class);
    Route::resource('mao-de-obra',       MaoDeObraWebController::class)->except(['show'])->parameters(['mao-de-obra' => 'maoDeObra']);
    Route::resource('catalogo-servicos', CatalogoServicosWebController::class)->except(['show'])->parameters(['catalogo-servicos' => 'catalogoServico']);
    Route::resource('funcionarios',      FuncionarioWebController::class)->except(['show']);
    Route::resource('parceiros',         ParceiroWebController::class)->except(['show']);

    // Financeiro
    Route::get('/financeiro',            [FinanceiroWebController::class, 'index'])->name('financeiro.index');
    Route::post('/financeiro',           [FinanceiroWebController::class, 'store'])->name('financeiro.store');
    Route::delete('/financeiro/{item}',  [FinanceiroWebController::class, 'destroy'])->name('financeiro.destroy');

    // Caixa — abertura e fechamento (#13)
    Route::get('/caixa',              [CaixaWebController::class, 'index'])->name('caixa.index');
    Route::post('/caixa/abrir',       [CaixaWebController::class, 'abrir'])->name('caixa.abrir');
    Route::post('/caixa/{caixa}/fechar', [CaixaWebController::class, 'fechar'])->name('caixa.fechar');

    // PDFs (#20, #21)
    Route::get('/orcamentos/{orcamento}/pdf', [PdfController::class, 'orcamento'])->name('orcamentos.pdf');
    Route::get('/ordens/{ordem}/pdf',         [PdfController::class, 'ordemServico'])->name('ordens.pdf');
    Route::get('/lembretes',              [LembreteWebController::class, 'index'])->name('lembretes.index');
    Route::post('/lembretes',             [LembreteWebController::class, 'store'])->name('lembretes.store');
    Route::get('/lembretes/{lembrete}/edit', [LembreteWebController::class, 'edit'])->name('lembretes.edit');
    Route::put('/lembretes/{lembrete}',   [LembreteWebController::class, 'update'])->name('lembretes.update.full');
    Route::patch('/lembretes/{lembrete}', [LembreteWebController::class, 'update'])->name('lembretes.update');
    Route::delete('/lembretes/{lembrete}',[LembreteWebController::class, 'destroy'])->name('lembretes.destroy');

    // Relatórios
    Route::prefix('relatorios')->name('relatorios.')->group(function () {
        Route::get('/financeiro',    [RelatorioWebController::class, 'financeiro'])->name('financeiro');
        Route::get('/fluxo-caixa',   [RelatorioWebController::class, 'fluxoCaixa'])->name('fluxo-caixa');
        Route::get('/lucro-servico', [RelatorioWebController::class, 'lucroServico'])->name('lucro-servico');
    });

    // Usuários
    Route::resource('usuarios', UsuarioWebController::class)->except(['show']);
    Route::post('usuarios/{usuario}/desbloquear', [UsuarioWebController::class, 'desbloquear'])->name('usuarios.desbloquear');

    // Configurações (apenas admin)
    Route::get('/configuracoes',  [\App\Http\Controllers\Web\ConfiguracaoWebController::class, 'index'])->name('configuracoes.index');
    Route::post('/configuracoes', [\App\Http\Controllers\Web\ConfiguracaoWebController::class, 'update'])->name('configuracoes.update');
});

require __DIR__.'/auth.php';

<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Configuracao;
use Illuminate\Http\Request;

class ConfiguracaoWebController extends Controller
{
    public function index()
    {
        $this->authorize('admin');

        $configs = Configuracao::orderBy('chave')->get()->keyBy('chave');

        return view('pitstop.configuracoes.index', compact('configs'));
    }

    public function update(Request $request)
    {
        $this->authorize('admin');

        $data = $request->validate([
            'google_review_link' => 'nullable|url|max:500',
            'nome_oficina'       => 'required|string|max:120',
            'telefone_oficina'   => 'nullable|string|max:20',
            'endereco_oficina'   => 'nullable|string|max:200',
            'mensagem_review'    => 'nullable|string|max:500',
        ]);

        foreach ($data as $chave => $valor) {
            Configuracao::set($chave, $valor ?? '');
        }

        return back()->with('success', 'Configurações salvas com sucesso!');
    }
}

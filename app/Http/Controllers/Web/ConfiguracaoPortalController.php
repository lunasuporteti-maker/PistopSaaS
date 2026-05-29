<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Configuracao;
use App\Models\User;
use Illuminate\Http\Request;

class ConfiguracaoPortalController extends Controller
{
    public function edit()
    {
        $usuarios = User::whereIn('perfil', ['admin', 'gerente'])
            ->where('ativo', true)
            ->orderBy('name')
            ->get();

        $notificarIds    = json_decode(Configuracao::get('portal_notificar_usuarios_ids', '[]'), true) ?? [];
        $canais          = json_decode(Configuracao::get('portal_notificar_canais', '{"email":true}'), true) ?? ['email' => true];
        $aprovacaoAtiva  = Configuracao::get('portal_aprovacao_online_ativa', '1') === '1';

        return view('pitstop.configuracoes.portal', compact('usuarios', 'notificarIds', 'canais', 'aprovacaoAtiva'));
    }

    public function update(Request $request)
    {
        Configuracao::set('portal_aprovacao_online_ativa', $request->boolean('aprovacao_online_ativa') ? '1' : '0');

        Configuracao::set('portal_notificar_canais', json_encode([
            'email' => $request->boolean('canal_email'),
        ]));

        $ids = array_filter((array) $request->input('notificar_usuarios_ids', []));
        Configuracao::set('portal_notificar_usuarios_ids', json_encode(array_values($ids)));

        return back()->with('success', 'Configurações do portal salvas.');
    }
}

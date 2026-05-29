<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Jobs\GerarThumbnailJob;
use App\Models\OrcamentoInteracao;
use App\Models\Orcamento;
use App\Models\ServicoFoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FotoController extends Controller
{
    private const MAX_FOTOS = 50;

    public function index(Orcamento $orcamento)
    {
        $fotos = $orcamento->fotos()
            ->with('uploader:id,name')
            ->orderBy('categoria')
            ->orderBy('created_at')
            ->get()
            ->map(fn ($f) => [
                'id'        => $f->id,
                'categoria' => $f->categoria,
                'legenda'   => $f->legenda,
                'url'       => $f->path_thumbnail
                    ? Storage::disk('public')->url($f->path_thumbnail)
                    : Storage::disk('public')->url($f->path_original),
                'url_original' => Storage::disk('public')->url($f->path_original),
                'uploader'  => $f->uploader?->name,
                'pode_excluir' => $this->podeExcluir($f),
            ]);

        return response()->json(['fotos' => $fotos]);
    }

    public function store(Request $request, Orcamento $orcamento)
    {
        $request->validate([
            'fotos'           => 'required|array|min:1|max:10',
            'fotos.*'         => 'file|mimes:jpeg,png,jpg|max:10240',
            'categoria'       => 'nullable|in:antes,durante,depois,peca,outro',
            'legenda'         => 'nullable|string|max:200',
        ]);

        $totalAtual = $orcamento->fotos()->count();
        $novas      = count($request->file('fotos'));

        if ($totalAtual + $novas > self::MAX_FOTOS) {
            return response()->json([
                'ok'    => false,
                'error' => "Limite de " . self::MAX_FOTOS . " fotos por orçamento atingido.",
            ], 422);
        }

        $tenant    = app('tenant');
        $categoria = $request->input('categoria', 'outro');
        $legenda   = $request->input('legenda');
        $criadas   = [];

        foreach ($request->file('fotos') as $arquivo) {
            $uuid     = Str::uuid();
            $ext      = $arquivo->getClientOriginalExtension();
            $path     = "tenants/{$tenant->slug}/fotos/{$orcamento->id}/{$uuid}.{$ext}";

            $arquivo->storeAs('', $path, 'public');

            $foto = ServicoFoto::create([
                'tenant_id'        => $tenant->id,
                'orcamento_id'     => $orcamento->id,
                'categoria'        => $categoria,
                'legenda'          => $legenda,
                'path_original'    => $path,
                'path_thumbnail'   => null,
                'tamanho_bytes'    => $arquivo->getSize(),
                'mime_type'        => $arquivo->getMimeType(),
                'uploaded_by'      => auth()->id(),
            ]);

            GerarThumbnailJob::dispatch($foto->id);

            OrcamentoInteracao::create([
                'tenant_id'    => $orcamento->tenant_id,
                'orcamento_id' => $orcamento->id,
                'tipo'         => OrcamentoInteracao::TIPO_UPLOAD_FOTO,
                'dados_json'   => ['foto_id' => $foto->id, 'categoria' => $categoria],
                'usuario_id'   => auth()->id(),
            ]);

            $criadas[] = $foto->id;
        }

        return response()->json(['ok' => true, 'criadas' => $criadas]);
    }

    public function destroy(ServicoFoto $foto)
    {
        abort_unless($this->podeExcluir($foto), 403, 'Sem permissão para excluir esta foto.');

        OrcamentoInteracao::create([
            'tenant_id'    => $foto->tenant_id,
            'orcamento_id' => $foto->orcamento_id,
            'tipo'         => OrcamentoInteracao::TIPO_EXCLUSAO_FOTO,
            'dados_json'   => ['foto_id' => $foto->id],
            'usuario_id'   => auth()->id(),
        ]);

        $foto->delete();

        return response()->json(['ok' => true]);
    }

    private function podeExcluir(ServicoFoto $foto): bool
    {
        $user = auth()->user();

        if (in_array($user->perfil, ['admin', 'gerente'])) {
            return true;
        }

        // Operador só exclui as próprias fotos e dentro de 24h
        return $user->perfil === 'operador'
            && $foto->uploaded_by === $user->id
            && $foto->created_at->diffInHours(now()) < 24;
    }
}

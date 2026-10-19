<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Configuracao;
use App\Models\Orcamento;
use App\Models\OrdemServico;
use App\Models\Tenant;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PdfController extends Controller
{
    /** Cor de destaque padrão para oficinas sem cor configurada. */
    private const COR_PADRAO = '#475569';

    /**
     * Lê o logo da oficina e devolve um data URI base64 para embutir no PDF.
     *
     * Preferência: a logo que a própria oficina subiu no onboarding
     * (Configuracao 'logo_path', salva via Storage::url no disco 'public').
     * Fallback legado: public/images/logos/{slug}.png. Retorna null se não
     * houver — o PDF gera sem logo em vez de quebrar (multi-tenant: cada
     * oficina a sua). Em rota pública (sem tenant no container) passe $tenantId.
     */
    private function logoBase64(?string $slug, ?int $tenantId = null): ?string
    {
        try {
            // 1) Logo self-service do onboarding (por tenant)
            $logoUrl = $tenantId
                ? Configuracao::getForTenant($tenantId, 'logo_path')
                : Configuracao::get('logo_path');

            $data = $this->logoDataUriDoStorage($logoUrl);
            if ($data !== null) {
                return $data;
            }

            // 2) Fallback legado: arquivo fixo public/images/logos/{slug}.png
            if ($slug) {
                $path = public_path("images/logos/{$slug}.png");
                if (is_file($path) && is_readable($path)) {
                    $conteudo = @file_get_contents($path);
                    if ($conteudo !== false) {
                        return 'data:image/png;base64,' . base64_encode($conteudo);
                    }
                }
            }

            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Converte uma URL de logo salva via Storage::url (ex.:
     * /storage/tenants/{slug}/arquivo.png) no disco 'public' em data URI base64.
     * Null se vazia, fora do disco ou inexistente.
     */
    private function logoDataUriDoStorage(?string $logoUrl): ?string
    {
        if (empty($logoUrl)) {
            return null;
        }

        $marcador = '/storage/';
        $pos = strpos($logoUrl, $marcador);
        if ($pos === false) {
            return null;
        }

        $relativo = ltrim(substr($logoUrl, $pos + strlen($marcador)), '/');
        if ($relativo === '' || str_contains($relativo, '..')) {
            return null;
        }

        $disk = Storage::disk('public');
        if (! $disk->exists($relativo)) {
            return null;
        }

        $conteudo = $disk->get($relativo);
        if ($conteudo === null || $conteudo === '') {
            return null;
        }

        $mime = $disk->mimeType($relativo) ?: 'image/png';
        return 'data:' . $mime . ';base64,' . base64_encode($conteudo);
    }

    /** Slug do tenant atual (rotas autenticadas), ou null se indisponível. */
    private function slugTenantAtual(): ?string
    {
        return app()->bound('tenant') ? optional(app('tenant'))->slug : null;
    }

    private function dadosEmpresa(): array
    {
        return [
            'nome'      => Configuracao::get('nome_oficina', 'PitStop'),
            'cnpj'      => Configuracao::get('cnpj_oficina'),
            'telefone'  => Configuracao::get('telefone_oficina'),
            'endereco'  => Configuracao::get('endereco_oficina'),
            'email'     => Configuracao::get('email_oficina'),
            'instagram' => Configuracao::get('instagram_oficina'),
            'slogan'    => Configuracao::get('slogan_oficina'),
            'cor'       => Configuracao::get('cor_primaria', self::COR_PADRAO),
        ];
    }

    private function dadosEmpresaParaTenant(int $tenantId): array
    {
        $get = fn($chave, $default = '') => Configuracao::getForTenant($tenantId, $chave, $default);
        return [
            'nome'      => $get('nome_oficina', 'PitStop'),
            'cnpj'      => $get('cnpj_oficina'),
            'telefone'  => $get('telefone_oficina'),
            'endereco'  => $get('endereco_oficina'),
            'email'     => $get('email_oficina'),
            'instagram' => $get('instagram_oficina'),
            'slogan'    => $get('slogan_oficina'),
            'cor'       => $get('cor_primaria', self::COR_PADRAO),
        ];
    }

    public function orcamento(Orcamento $orcamento)
    {
        $orcamento->load(['cliente', 'veiculo', 'servicos', 'pecas.peca', 'maoDeObra.maoDeObra']);
        $empresa = $this->dadosEmpresa();
        $logoBase64 = $this->logoBase64($this->slugTenantAtual());

        $pdf = Pdf::loadView('pitstop.pdf.orcamento', compact('orcamento', 'empresa', 'logoBase64'))
            ->setPaper('a4', 'portrait')
            ->setOption('isFontSubsettingEnabled', true);

        return $pdf->download("orcamento-{$orcamento->id}.pdf");
    }

    public function orcamentoPublico(string $token)
    {
        $orcamento = Orcamento::where('token_publico', $token)->firstOrFail();
        $orcamento->load(['cliente', 'veiculo', 'servicos', 'pecas.peca', 'maoDeObra.maoDeObra']);
        $empresa = $this->dadosEmpresaParaTenant($orcamento->tenant_id);
        $logoBase64 = $this->logoBase64(optional(Tenant::find($orcamento->tenant_id))->slug, $orcamento->tenant_id);

        $pdf = Pdf::loadView('pitstop.pdf.orcamento', compact('orcamento', 'empresa', 'logoBase64'))
            ->setPaper('a4', 'portrait')
            ->setOption('isFontSubsettingEnabled', true);

        return $pdf->download("orcamento-{$orcamento->id}.pdf");
    }

    public function ordemServico(OrdemServico $ordem)
    {
        $ordem->load(['cliente', 'veiculo', 'pecas.peca', 'pagamentos', 'orcamento.servicos']);
        $empresa = $this->dadosEmpresa();
        $logoBase64 = $this->logoBase64($this->slugTenantAtual());

        $pdf = Pdf::loadView('pitstop.pdf.ordem-servico', compact('ordem', 'empresa', 'logoBase64'))
            ->setPaper('a4', 'portrait')
            ->setOption('isFontSubsettingEnabled', true);

        return $pdf->download("os-{$ordem->numero_os}.pdf");
    }
}

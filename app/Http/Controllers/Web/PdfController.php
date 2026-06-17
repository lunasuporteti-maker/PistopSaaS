<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Configuracao;
use App\Models\Orcamento;
use App\Models\OrdemServico;
use App\Models\Tenant;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfController extends Controller
{
    /** Cor de destaque padrão para oficinas sem cor configurada. */
    private const COR_PADRAO = '#475569';

    /**
     * Lê o logo da oficina (por slug do tenant) e devolve um data URI base64.
     * Convenção: public/images/logos/{slug}.png. Retorna null se não existir —
     * o PDF gera sem o logo em vez de quebrar (multi-tenant: cada oficina o seu).
     */
    private function logoBase64(?string $slug): ?string
    {
        try {
            if (! $slug) {
                return null;
            }
            $path = public_path("images/logos/{$slug}.png");
            if (! is_file($path) || ! is_readable($path)) {
                return null;
            }
            $conteudo = @file_get_contents($path);
            if ($conteudo === false) {
                return null;
            }
            return 'data:image/png;base64,' . base64_encode($conteudo);
        } catch (\Throwable $e) {
            return null;
        }
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
        $logoBase64 = $this->logoBase64(optional(Tenant::find($orcamento->tenant_id))->slug);

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

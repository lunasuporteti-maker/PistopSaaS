<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Configuracao;
use App\Models\Orcamento;
use App\Models\OrdemServico;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfController extends Controller
{
    /**
     * Lê o logo da oficina e devolve um data URI base64 para embutir no PDF.
     * Retorna null se o arquivo não existir ou falhar — o PDF gera sem o logo
     * em vez de quebrar (resiliência: nunca derruba a geração por causa da imagem).
     */
    private function logoBase64(): ?string
    {
        try {
            $path = public_path('images/logo_autofix.png');
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

    private function dadosEmpresa(): array
    {
        return [
            'nome'      => Configuracao::get('nome_oficina', 'PitStop'),
            'cnpj'      => Configuracao::get('cnpj_oficina'),
            'telefone'  => Configuracao::get('telefone_oficina'),
            'endereco'  => Configuracao::get('endereco_oficina'),
            'email'     => Configuracao::get('email_oficina'),
            'instagram' => Configuracao::get('instagram_oficina'),
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
        ];
    }

    public function orcamento(Orcamento $orcamento)
    {
        $orcamento->load(['cliente', 'veiculo', 'servicos', 'pecas.peca', 'maoDeObra.maoDeObra']);
        $empresa = $this->dadosEmpresa();
        $logoBase64 = $this->logoBase64();

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
        $logoBase64 = $this->logoBase64();

        $pdf = Pdf::loadView('pitstop.pdf.orcamento', compact('orcamento', 'empresa', 'logoBase64'))
            ->setPaper('a4', 'portrait')
            ->setOption('isFontSubsettingEnabled', true);

        return $pdf->download("orcamento-{$orcamento->id}.pdf");
    }

    public function ordemServico(OrdemServico $ordem)
    {
        $ordem->load(['cliente', 'veiculo', 'pecas.peca', 'pagamentos', 'orcamento.servicos']);
        $empresa = $this->dadosEmpresa();
        $logoBase64 = $this->logoBase64();

        $pdf = Pdf::loadView('pitstop.pdf.ordem-servico', compact('ordem', 'empresa', 'logoBase64'))
            ->setPaper('a4', 'portrait')
            ->setOption('isFontSubsettingEnabled', true);

        return $pdf->download("os-{$ordem->numero_os}.pdf");
    }
}

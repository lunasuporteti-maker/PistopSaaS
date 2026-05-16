<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Orcamento;
use App\Models\OrdemServico;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfController extends Controller
{
    private array $empresa = [
        'nome'      => 'AutoFix',
        'cnpj'      => '44.456.671/0001-73',
        'telefone'  => '(84) 99672-2453',
        'endereco'  => 'Rua Salatiel Rufino dos Santos, 221 - Vale do Sol, Parnamirim/RN',
        'email'     => 'AutoFix.atendimento@gmail.com',
        'instagram' => '@autofix.mecanica',
        'dev'       => 'Sistema desenvolvido por IAQueAtende',
    ];

    public function orcamento(Orcamento $orcamento)
    {
        $orcamento->load(['cliente', 'veiculo', 'servicos', 'pecas.peca', 'maoDeObra.maoDeObra']);
        $empresa = $this->empresa;

        $pdf = Pdf::loadView('pitstop.pdf.orcamento', compact('orcamento', 'empresa'))
            ->setPaper('a4', 'portrait');

        return $pdf->download("orcamento-{$orcamento->id}.pdf");
    }

    public function ordemServico(OrdemServico $ordem)
    {
        $ordem->load(['cliente', 'veiculo', 'pecas.peca', 'pagamentos', 'orcamento.servicos']);
        $empresa = $this->empresa;

        $pdf = Pdf::loadView('pitstop.pdf.ordem-servico', compact('ordem', 'empresa'))
            ->setPaper('a4', 'portrait');

        return $pdf->download("os-{$ordem->numero_os}.pdf");
    }
}

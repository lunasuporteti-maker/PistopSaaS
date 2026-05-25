<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

/**
 * Job assíncrono para geração de relatórios Excel pesados.
 *
 * PDFs continuam síncronos (são pequenos). Apenas Excel passa por fila.
 * O arquivo gerado fica em storage/app/exports/tenant-{id}/{filename}
 * e é referenciado via cache por 10 minutos via chave downloadKey.
 */
class ExportarRelatorioJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Número de tentativas antes de falhar definitivamente */
    public int $tries = 3;

    /** Timeout em segundos por tentativa */
    public int $timeout = 120;

    public function __construct(
        public readonly string $exportClass,
        public readonly array  $params,
        public readonly string $filename,
        public readonly int    $tenantId,
        public readonly string $downloadKey,
    ) {}

    public function handle(): void
    {
        // Instancia a export class com os parâmetros passados
        $export = new $this->exportClass(...array_values($this->params));

        $path = "exports/tenant-{$this->tenantId}/{$this->filename}";

        Excel::store($export, $path, 'local');

        // Marcar como pronto no cache por 10 minutos
        cache()->put("export:{$this->downloadKey}", $path, now()->addMinutes(10));
    }
}

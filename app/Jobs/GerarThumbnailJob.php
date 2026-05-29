<?php

namespace App\Jobs;

use App\Models\ServicoFoto;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GerarThumbnailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public readonly int $fotoId) {}

    public function handle(): void
    {
        $foto = ServicoFoto::find($this->fotoId);
        if (! $foto || ! Storage::disk('public')->exists($foto->path_original)) {
            return;
        }

        try {
            $fullPath  = Storage::disk('public')->path($foto->path_original);
            $ext       = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
            $mime      = $foto->mime_type;

            $src = match (true) {
                str_contains($mime, 'png')  => @imagecreatefrompng($fullPath),
                str_contains($mime, 'jpeg'),
                str_contains($mime, 'jpg')  => @imagecreatefromjpeg($fullPath),
                default                     => null,
            };

            if (! $src) {
                return;
            }

            [$w, $h] = getimagesize($fullPath);
            $max = 400;
            $ratio = min($max / $w, $max / $h, 1);
            $nw = (int) round($w * $ratio);
            $nh = (int) round($h * $ratio);

            $dst = imagecreatetruecolor($nw, $nh);

            if (str_contains($mime, 'png')) {
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
            }

            imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);

            $thumbDir  = dirname($foto->path_original) . '/thumbs';
            $thumbFile = $thumbDir . '/' . pathinfo($foto->path_original, PATHINFO_FILENAME) . '_thumb.' . $ext;

            Storage::disk('public')->makeDirectory($thumbDir);
            $thumbFullPath = Storage::disk('public')->path($thumbFile);

            match (true) {
                str_contains($mime, 'png')  => imagepng($dst, $thumbFullPath, 8),
                default                     => imagejpeg($dst, $thumbFullPath, 85),
            };

            imagedestroy($src);
            imagedestroy($dst);

            $foto->update(['path_thumbnail' => $thumbFile]);
        } catch (\Throwable $e) {
            Log::warning("[GerarThumbnail] Foto #{$this->fotoId}: " . $e->getMessage());
        }
    }
}

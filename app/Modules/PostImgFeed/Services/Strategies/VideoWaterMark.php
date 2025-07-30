<?php

declare(strict_types=1);

namespace App\Modules\PostImgFeed\Services\Strategies;

use App\Enums\ImagemFeed;
use App\Modules\PostImgFeed\Contracts\ImageWatermark;
use Illuminate\Http\UploadedFile;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\X264;
use Illuminate\Support\Facades\Storage;
use FFMpeg\FFProbe;

class VideoWaterMark implements ImageWatermark
{


    public function getSupportedType(): ImagemFeed
    {
        return ImagemFeed::VIDEO;
    }

    public function applyWatermark(UploadedFile $uploadedFile): string
    {
        // 1. Mover o vídeo para um local temporário
        $tempInputPath = storage_path('app/tmp/' . uniqid('video_') . '.' . $uploadedFile->getClientOriginalExtension());
        $uploadedFile->move(dirname($tempInputPath), basename($tempInputPath));

        // 2. Caminho da imagem da marca d’água
        $imagePath = public_path('watermarks/wmnovacolor24.png');

        // 3. Caminho de saída do vídeo
        $outputFilename = generate_unique_filename('video', pathinfo($tempInputPath, PATHINFO_FILENAME)) . '.mp4';
        $relativePath = auth_user()->id . '/posts/' . $outputFilename;
        $absoluteOutputPath = storage_path('app/tmp/' . $outputFilename);

        // 4. Processar com FFmpeg
        $ffmpeg = FFMpeg::create([
            'ffmpeg.binaries'  => 'C:\\ffmpeg\\bin\\ffmpeg.exe',
            'ffprobe.binaries' => 'C:\\ffmpeg\\bin\\ffprobe.exe',
        ]);
        $video = $ffmpeg->open($tempInputPath);

        $video->filters()->watermark($imagePath, [
            'position' => 'relative',
            'bottom' => 50,
            'right' => 50,
        ]);

        $video->save(new X264(), $absoluteOutputPath);

        // 5. Subir para S3 (opcional)
        $content = file_get_contents($absoluteOutputPath);
        Storage::disk('s3')->put($relativePath, $content);

        // 6. Limpeza
        unlink($tempInputPath);
        unlink($absoluteOutputPath);

        return $relativePath;
    }
}

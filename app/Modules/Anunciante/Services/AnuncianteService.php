<?php
namespace App\Modules\Anunciante\Services;

use App\Behaviors\CpfBehaviors;
use App\Modules\Watermark\Services\Strategies\TypeMediaValueEnum;
use App\Modules\Watermark\Services\WatermarkStrategy;
use App\Services\AnuncioApiService;
use App\Services\S3ImageGalleryService;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rules\Enum;

class AnuncianteService
{

    public function __construct(private AnuncioApiService $api, private WatermarkStrategy $watermarkStrategy, private S3ImageGalleryService $s3ImageGalleryService)
    {
    }

    public function getAnuncioCpf(CpfBehaviors $cpf)
    {
        return $this->api->getAnuncionsCpf($cpf);
    }
    public function getDados($id)
    {
        return $this->api->getAnuncioDados($id);
    }

    public function postDados($id, array $dados)
    {
        return $this->api->postAnuncioDados($id, $dados);
    }

    public function postMidia($id, UploadedFile $file, $tipo)
    {
        $waterMarkPath = storage_path('app/public/wmnovacolor24.png');
        $svc = $this->watermarkStrategy->resolve(TypeMediaValueEnum::from($tipo));

        $tmpDir = storage_path('app/public/tmp');
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0775, true);
        }

        // Gera nome e move
        $baseName = uniqid('midia_') . '.' . $file->getClientOriginalExtension();
        $inputFullPath = $tmpDir . DIRECTORY_SEPARATOR . $baseName;
        $file->move($tmpDir, $baseName);

        // Saída
        $outName = uniqid('midia_wm_') . '.' . $file->getClientOriginalExtension();
        $outputFullPath = $tmpDir . DIRECTORY_SEPARATOR . $outName;

        $newImage = $svc->applyWatermark($inputFullPath, $waterMarkPath, $outputFullPath);
        $pathS3 = $this->s3ImageGalleryService->uploadImage($newImage, $id);

        return $pathS3;
    }



}

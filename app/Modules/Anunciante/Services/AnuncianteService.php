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

    public function __construct(private AnuncioApiService $api, private S3ImageGalleryService $s3ImageGalleryService)
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




}

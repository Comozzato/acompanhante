<?php

namespace App\Modules\PostImgFeed\Services\Strategies;

use App\Modules\Watermark\Contracts\ImageWatermark;
// Removi WatermarkServiceInterface, pois não está sendo usado diretamente nesta classe.
// Se for necessário para outras partes do seu módulo, mantenha-o.

class MasterImageWaterMark implements ImageWatermark
{
    /**
     * Aplica marcas d'água (meio, topo, rodapé) e redimensiona a imagem
     * para o tamanho fixo de 'imgmalbum' (630x950, sem crop).
     *
     * @param string $inputFile Caminho da imagem original
     * @param string|null $outputFile Caminho do arquivo de saída (opcional)
     * @return string Caminho do arquivo salvo
     * @throws \RuntimeException Se ocorrerem erros durante o processamento.
     */
    public function applyWatermark(
        string $inputFile,
        ?string $outputFile = null
    ): string {
        $width = 630;
        $height = 950;
        // $crop = false; // Variável não utilizada, pode ser removida se não houver lógica de crop.

        // NOTA: Este código assume que a imagem de entrada é PNG.
        // Para suportar outros formatos (JPEG, GIF), você precisará detectar o tipo
        // da imagem (ex: com getimagesize() ou exif_imagetype()) e usar a função
        // imagecreatefrom* apropriada (imagecreatefromjpeg, imagecreatefromgif).
        $image = imagecreatefrompng($inputFile);
        if (!$image) {
            throw new \RuntimeException('Falha ao carregar a imagem de entrada: ' . $inputFile);
        }

        $origWidth = imagesx($image);
        $origHeight = imagesy($image);

        // Calcula a proporção para redimensionar sem cortar, mantendo o aspecto
        $scale = min($width / $origWidth, $height / $origHeight);
        $newWidth = intval($origWidth * $scale);
        $newHeight = intval($origHeight * $scale);

        // Cria a imagem redimensionada final com fundo branco
        $resized = imagecreatetruecolor($width, $height);
        if (!$resized) {
            imagedestroy($image);
            throw new \RuntimeException('Falha ao criar a imagem redimensionada em memória.');
        }

        $white = imagecolorallocate($resized, 255, 255, 255);
        if ($white === false) {
            imagedestroy($image);
            imagedestroy($resized);
            throw new \RuntimeException('Falha ao alocar cor branca para o fundo.');
        }
        imagefill($resized, 0, 0, $white);

        // Centraliza a imagem original redimensionada na nova imagem com fundo branco
        $dstX = intval(($width - $newWidth) / 2);
        $dstY = intval(($height - $newHeight) / 2);
        if (!imagecopyresampled($resized, $image, $dstX, $dstY, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight)) {
            imagedestroy($image);
            imagedestroy($resized);
            throw new \RuntimeException('Falha ao redimensionar e copiar a imagem.');
        }

        imagedestroy($image); // Libera a memória da imagem original carregada
        $image = $resized;   // $image agora é o recurso da imagem redimensionada e com fundo

        // Aplica as marcas d'água
        $this->applyMarkMid($image);
        $this->applyMarkTop($image);
        $this->applyMarkBottom($image);

        // Define o caminho do arquivo de saída se não for fornecido
        $finalOutputFile = $outputFile ?: $inputFile;

        // Salva a imagem final como PNG
        // NOTA: Se quiser salvar em outro formato, use imagejpeg(), imagegif(), etc.
        // e ajuste a extensão do arquivo de saída.
        $saveSuccess = imagepng($image, $finalOutputFile);

        imagedestroy($image); // Libera a memória da imagem processada final

        if (!$saveSuccess) {
            throw new \RuntimeException('Falha ao salvar a imagem final com marcas d\'água: ' . $finalOutputFile);
        }

        return $finalOutputFile;
    }

    /**
     * Aplica uma marca d'água no centro da imagem.
     *
     * @param resource $bufferImg Recurso da imagem GD onde aplicar a marca.
     * @return void
     */
    private function applyMarkMid($bufferImg): void
    {
        $watermarkPath = public_path('watermarks/wmnovacolor24.png'); // Certifique-se que este arquivo existe

        if (!file_exists($watermarkPath)) {
            error_log('Arquivo da marca d\'água do meio não encontrado: ' . $watermarkPath);
            // Considerar lançar uma exceção ou retornar um status de erro se for crítico
            return;
        }

        $wm = imagecreatefrompng($watermarkPath);
        if (!$wm) {
            error_log('Falha ao criar imagem da marca d\'água do meio a partir de: ' . $watermarkPath);
            return;
        }

        $wmWidth = imagesx($wm);
        $wmHeight = imagesy($wm);
        $imgWidth = imagesx($bufferImg);
        $imgHeight = imagesy($bufferImg);

        $destX = intval(($imgWidth - $wmWidth) / 2);
        $destY = intval(($imgHeight - $wmHeight) / 2);

        imagecopyresampled($bufferImg, $wm, $destX, $destY, 0, 0, $wmWidth, $wmHeight, $wmWidth, $wmHeight);
        imagedestroy($wm);
    }

    /**
     * Aplica uma marca d'água no topo central da imagem.
     *
     * @param resource $bufferImg Recurso da imagem GD onde aplicar a marca.
     * @return void
     */
    private function applyMarkTop($bufferImg): void
    {
        $watermarkPath = public_path('watermarks/mctop24.png'); // Certifique-se que este arquivo existe

        if (!file_exists($watermarkPath)) {
            error_log('Arquivo da marca d\'água do topo não encontrado: ' . $watermarkPath);
            return;
        }

        $wm = imagecreatefrompng($watermarkPath);
        if (!$wm) {
            error_log('Falha ao criar imagem da marca d\'água do topo a partir de: ' . $watermarkPath);
            return;
        }

        $wmWidth = imagesx($wm);
        $wmHeight = imagesy($wm);
        $imgWidth = imagesx($bufferImg);
        // $imgHeight = imagesy($bufferImg); // Não usado para posicionamento no topo central com padding

        $paddingTop = 10; // Espaçamento do topo em pixels
        $destX = intval(($imgWidth - $wmWidth) / 2); // Centraliza horizontalmente
        $destY = $paddingTop;                     // Define a distância do topo

        imagecopyresampled($bufferImg, $wm, $destX, $destY, 0, 0, $wmWidth, $wmHeight, $wmWidth, $wmHeight);
        imagedestroy($wm);
    }

    /**
     * Aplica uma marca d'água no rodapé central da imagem.
     *
     * @param resource $bufferImg Recurso da imagem GD onde aplicar a marca.
     * @return void
     */
    private function applyMarkBottom($bufferImg): void
    {
        $watermarkPath = public_path('watermarks/wmnovaurl24.png'); // Certifique-se que este arquivo existe

        if (!file_exists($watermarkPath)) {
            error_log('Arquivo da marca d\'água do rodapé não encontrado: ' . $watermarkPath);
            return;
        }

        $wm = imagecreatefrompng($watermarkPath);
        if (!$wm) {
            error_log('Falha ao criar imagem da marca d\'água do rodapé a partir de: ' . $watermarkPath);
            return;
        }

        $wmWidth = imagesx($wm);
        $wmHeight = imagesy($wm);
        $imgWidth = imagesx($bufferImg);
        $imgHeight = imagesy($bufferImg);

        $paddingBottom = 10; // Espaçamento do fundo em pixels
        $destX = intval(($imgWidth - $wmWidth) / 2); // Centraliza horizontalmente
        $destY = $imgHeight - $wmHeight - $paddingBottom; // Posiciona no fundo com espaçamento

        imagecopyresampled($bufferImg, $wm, $destX, $destY, 0, 0, $wmWidth, $wmHeight, $wmWidth, $wmHeight);
        imagedestroy($wm);
    }
}
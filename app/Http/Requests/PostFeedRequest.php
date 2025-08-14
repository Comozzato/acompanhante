<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostFeedRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
     public function rules(): array
    {
        // Assumindo que o nome do seu campo no formulário é 'media_file'
        return [
            'media_file' => [
                'required', // O campo é obrigatório
                'file',     // Deve ser um arquivo

                // Tamanho máximo de 250MB (o valor é em kilobytes)
                // 250 * 1024 = 256000
                'max:256000',

                // Define os tipos de arquivo permitidos (imagens e vídeos comuns)
                'mimes:jpg,jpeg,png,webp,gif,mp4,mov,avi,mkv',

                // Define as dimensões MÁXIMAS para IMAGENS
                // LARGURA máxima de 1920px e ALTURA máxima de 1080px.
                // ATENÇÃO: Esta regra funciona apenas para imagens, não para vídeos.
                'dimensions:max_width=1920,max_height=1080',
            ],
            // Você pode adicionar outras regras para outros campos aqui
            'texto_post' => 'nullable|string|max:2200',
        ];
    }
}

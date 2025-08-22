<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

// 
class PostFeedRequest extends FormRequest
{

    public function rules(): array
    {
        $imageMimes = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $videoMimes = ['mp4', 'mov', 'avi', 'mkv'];
        // Assumindo que o nome do seu campo no formulário é 'media_file'
        return [
            'file' => [
                'required',
                // Usando o objeto File para uma validação mais clara e poderosa
                    File::types(array_merge($imageMimes, $videoMimes))
                    ->max(250 * 1024), // 250MB (cálculo direto para evitar "números mágicos")
            ],
            // Adiciona a regra de dimensão SOMENTE se o arquivo enviado for uma imagem.
            // Isso evita erros de validação ao enviar vídeos.
            'dimensions' => [
                'nullable', // Permite que a regra não seja aplicada se não for imagem
                Rule::dimensions()->maxWidth(1920)->maxHeight(1080)
                    ->when(
                        // A condição para aplicar a regra:
                        fn($input) => $this->file('file') && in_array(strtolower($this->file('file')->getClientOriginalExtension()), $imageMimes),
                        // Se a condição for verdadeira, a regra é aplicada
                        fn($rule) => $rule
                    )
            ],

            'post' => ['nullable', 'string', 'max:2200'],
        ];
    }

    public function messages()
    {
        return [
            'file.required' => 'Você precisa enviar um arquivo.',
            'file.types'    => 'O arquivo deve ser uma imagem (jpg, png, webp, gif) ou um vídeo (mp4, mov, avi, mkv).',
            'file.max'      => 'O arquivo é muito grande. O tamanho máximo permitido é de 250MB.',

            'dimensions.dimensions' => 'A imagem é muito grande. As dimensões máximas são 1920px de largura por 1080px de altura.',

            'post.max' => 'O texto da postagem não pode ultrapassar :max caracteres.',
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response(['message' => $validator->errors()->first()], 400));
    }
}

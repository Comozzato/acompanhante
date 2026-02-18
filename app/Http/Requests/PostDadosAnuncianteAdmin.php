<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Gate;

class PostDadosAnuncianteAdmin extends FormRequest
{
    public function authorize(): bool
    {
        if(Gate::forUser(auth_user())->allows('admin'))
        {
            return true;
        }
        return false;
    }

    public function rules(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | STRINGS
            |--------------------------------------------------------------------------
            */

            'ddi_acompanhante' => ['sometimes', 'required', 'string'],
            'whatsapp_acompanhante' => ['sometimes', 'required', 'string', 'regex:/^[0-9]+$/'],
            'telegram_acompanhante' => ['nullable', 'string'],
            'cache_acompanhante' => ['nullable', 'string'],
            'nomeoriginal_acompanhante' => ['nullable', 'string'],
            'quadril_acompanhante' => ['nullable', 'string'],
            'cartao_acompanhante' => ['nullable', 'string', 'in:Sim,Não'],
            'novofotos_acompanhante' => ['nullable', 'string'],
            'obs_acompanhante' => ['nullable', 'string'],
            'estreia_acompanhante' => ['nullable', 'regex:/^\d{2}\/\d{2}$/'],
            'termino_acompanhante' => ['nullable', 'date_format:Y-m-d'],

            /*
            |--------------------------------------------------------------------------
            | NUMÉRICOS (string ou number)
            |--------------------------------------------------------------------------
            */

            'novoidade_acompanhante' => ['nullable', 'regex:/^\d+$/'],
            'novoaltura_acompanhante' => ['nullable', 'regex:/^\d+(\.\d+)?$/'],
            'novopeso_acompanhante' => ['nullable', 'regex:/^\d+$/'],
            'novopes_acompanhante' => ['nullable', 'regex:/^\d+$/'],

            /*
            |--------------------------------------------------------------------------
            | BOOLEANOS (checkbox)
            |--------------------------------------------------------------------------
            */

            'ultimos' => ['sometimes', 'in:1'],
            'comvideo' => ['sometimes', 'in:1'],

            /*
            |--------------------------------------------------------------------------
            | ARRAYS
            |--------------------------------------------------------------------------
            */

            'novoatendimento_acompanhante' => ['nullable', 'array'],
            'novoatendimento_acompanhante.*' => ['string'],

            'novoacompanha_acompanhante' => ['nullable', 'array'],
            'novoacompanha_acompanhante.*' => ['string'],

            /*
            |--------------------------------------------------------------------------
            | DATAS
            |--------------------------------------------------------------------------
            */

            'dtensaio_acompanhante' => ['nullable', 'date_format:Y-m-d'],

            /*
            |--------------------------------------------------------------------------
            | TAXONOMIAS
            |--------------------------------------------------------------------------
            */

            'secao' => ['nullable', 'array'],
            'secao.*' => ['string'], // pode ser slug ou id

            'cidadevirtual' => ['nullable', 'array'],
            'cidadevirtual.*' => ['integer'],

            /*
            |--------------------------------------------------------------------------
            | STATUS
            |--------------------------------------------------------------------------
            */

            'post_status' => ['nullable', 'in:draft,publish,pending'],
        ];
    }

    public function messages(): array
    {
        return [

            'whatsapp_acompanhante.regex' => 'O WhatsApp deve conter apenas números.',
            'novoaltura_acompanhante.regex' => 'A altura deve usar ponto como separador decimal (ex: 1.70).',
            'novoidade_acompanhante.regex' => 'A idade deve conter apenas números.',
            'novopeso_acompanhante.regex' => 'O peso deve conter apenas números.',
            'novopes_acompanhante.regex' => 'O número do pé deve conter apenas números.',

            'estreia_acompanhante.regex' => 'A estreia deve estar no formato DD/MM.',
            'termino_acompanhante.date_format' => 'A data de término deve estar no formato YYYY-MM-DD.',
            'dtensaio_acompanhante.date_format' => 'A data de ensaio deve estar no formato YYYY-MM-DD.',

            'ultimos.in' => 'O campo últimos deve ser enviado como "1".',
            'comvideo.in' => 'O campo com vídeo deve ser enviado como "1".',

            'post_status.in' => 'O status deve ser draft, publish ou pending.',
        ];
    }

    public function attributes(): array
    {
        return [
            'ddi_acompanhante' => 'DDI',
            'whatsapp_acompanhante' => 'WhatsApp',
            'telegram_acompanhante' => 'Telegram',
            'cache_acompanhante' => 'Cache',
            'novoidade_acompanhante' => 'Idade',
            'novoaltura_acompanhante' => 'Altura',
            'novopeso_acompanhante' => 'Peso',
            'novopes_acompanhante' => 'Número do pé',
            'estreia_acompanhante' => 'Estreia',
            'dtensaio_acompanhante' => 'Data de ensaio',
            'cidadevirtual' => 'Cidade Virtual',
            'secao' => 'Sessão',
        ];
    }

      protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response(['message' => $validator->errors()->first()], 400));
    }
}

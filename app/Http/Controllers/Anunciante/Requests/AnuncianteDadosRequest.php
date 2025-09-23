<?php

namespace App\Http\Controllers\Anunciante\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnuncianteDadosRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        return [
            'ddi_acompanhante' => 'nullable|string',
            'whatsapp_acompanhante' => 'nullable|string',
            'novoatendimento_acompanhante' => 'nullable|string',
            'cache_acompanhante' => 'nullable|string',
            'cartao_acompanhante' => 'nullable|string',
            'novoaltura_acompanhante' => 'nullable|string',
            "novoaltura_esconder" => 'nullable|boolean',
            'novopeso_acompanhante' => 'nullable|string',
            "novopeso_esconder" => 'nullable|boolean',
            'quadril_acompanhante' => 'nullable|string',
            "quadril_esconder" => 'nullable|boolean',
            'novopes_acompanhante' => 'nullable|string',
            'novopes_esconder' => 'nullable|boolean',
            'novoacompanha_acompanhante' => 'nullable|string'
        ];
    }
}

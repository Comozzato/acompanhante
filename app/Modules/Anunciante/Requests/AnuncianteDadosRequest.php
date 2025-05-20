<?php
namespace App\Modules\Anunciante\Requests;

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
            'whatsapp_acompanhante' => 'nullable|string',
            'novoatendimento_acompanhante' => 'nullable|string',
            'cache_acompanhante' => 'nullable|string',
            'cartao_acompanhante' => 'nullable|string',
            'novoaltura_acompanhante' => 'nullable|string',
            'novopeso_acompanhante' => 'nullable|string',
            'quadril_acompanhante' => 'nullable|string',
            'novopes_acompanhante' => 'nullable|string',
            'novoacompanha_acompanhante' => 'nullable|string'
        ];
    }
}

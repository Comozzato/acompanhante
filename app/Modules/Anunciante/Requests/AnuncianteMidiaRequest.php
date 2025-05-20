<?php
namespace App\Modules\Anunciante\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnuncianteMidiaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        return [
            'file' => 'required|file',
            'tipo' => 'required|in:imagem,video',
        ];
    }
}

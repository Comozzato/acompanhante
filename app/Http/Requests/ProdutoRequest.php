<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProdutoRequest extends FormRequest
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
        return [
            'nome' => 'sometimes|string|Max:35',
            'preco' => 'sometimes|string|min:1|regex:/^\d+(\.\d{1,2})?$/',
            'descricao' => 'sometimes|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'nome.string' => 'O nome do produto deve ser uma string.',
            'nome.max' => 'O nome do produto não pode exceder 35 caracteres.',
            'preco.string' => 'O preço do produto deve ser uma string.',
            'preco.min' => 'O preço do produto deve ter pelo menos 1 caractere.',
            'preco.regex' => 'O preço do produto deve ser um número válido com até duas casas decimais.',
            'descricao.string' => 'A descrição do produto deve ser uma string.',
            'descricao.max' => 'A descrição do produto não pode exceder 255 caracteres.',
        ];
    }


    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();

        throw new HttpResponseException(
            response()->json([
                'message' => $errors->first(),  // mensagem principal
                'errors'  => $errors->messages() // opcional: todas as mensagens
            ], 400)
        );
    }
}

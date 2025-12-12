<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CobrancaoCreditoRequest extends FormRequest
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
            //
            'user_id' => 'required|integer|exists:users,id',
            'produto_id' => 'required|integer|exists:produtos,id',
            'post_id' => 'required|integer|exists:posts,id',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'O campo user_id é obrigatório.',
            'user_id.integer' => 'O campo user_id deve ser um número inteiro.',
            'user_id.exists' => 'O usuário especificado não existe.',
            'produto_id.required' => 'O campo produto_id é obrigatório.',
            'produto_id.integer' => 'O campo produto_id deve ser um número inteiro.',
            'produto_id.exists' => 'O produto especificado não existe.',
            'post_id.required' => 'O campo post_id é obrigatório.',
            'post_id.integer' => 'O campo post_id deve ser um número inteiro.',
            'post_id.exists' => 'O post especificado não existe.',
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


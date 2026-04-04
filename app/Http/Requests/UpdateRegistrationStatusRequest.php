<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateRegistrationStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'remarks' => 'sometimes|nullable|string|max:1000',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $fieldErrors = collect($validator->errors()->getMessages())->map(function ($messages, $field) {
            return [
                'field' => $field,
                'messages' => $messages,
            ];
        })->values();

        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $fieldErrors,
        ], 422));
    }
}

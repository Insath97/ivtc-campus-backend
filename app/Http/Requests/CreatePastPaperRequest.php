<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreatePastPaperRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'batch_id' => 'required|exists:batches,id',
            'has_scheme' => 'required|boolean',
            'description' => 'nullable|string',
            'paper_file' => 'required|file|mimes:pdf,doc,docx,zip,png,jpeg,jpg|max:102400',
            'scheme_file' => 'required_if:has_scheme,true|file|mimes:pdf,doc,docx,zip,png,jpeg,jpg|max:102400',
            'is_active' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'scheme_file.required_if' => 'The scheme file is required when the batch has a scheme.',
            'paper_file.required' => 'The past paper file is required.',
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

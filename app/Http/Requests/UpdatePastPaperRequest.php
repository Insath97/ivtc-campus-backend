<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdatePastPaperRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'batch_id' => 'sometimes|exists:batches,id',
            'has_scheme' => 'sometimes|boolean',
            'description' => 'sometimes|nullable|string',
            'paper_file' => 'sometimes|file|mimes:pdf,doc,docx,zip,png,jpeg,jpg|max:102400',
            
            // Scheme file is optional on update, but if provided must match criteria.
            // If they are explicitly setting has_scheme to true and sending a file, this validates it.
            'scheme_file' => 'sometimes|file|mimes:pdf,doc,docx,zip,png,jpeg,jpg|max:102400',
            
            'is_active' => 'sometimes|boolean',
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

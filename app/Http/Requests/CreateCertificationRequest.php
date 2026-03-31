<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateCertificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => 'required|string|max:255',
            'starting_date' => 'required|date',
            'ending_date' => 'required|date|after_or_equal:starting_date',
            'entrol_number' => 'nullable|string|unique:certifications,entrol_number|max:255',
            'course_code' => 'nullable|string|unique:certifications,course_code|max:255',
            'verification_code' => 'required|string|unique:certifications,verification_code|max:255',
            'certificate_number' => 'required|string|unique:certifications,certificate_number|max:255',
            'nic' => 'required|string|unique:certifications,nic|max:255',
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

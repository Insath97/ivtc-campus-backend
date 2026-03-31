<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateCertificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('certification') ?? $this->route('id');

        return [
            'full_name' => 'sometimes|string|max:255',
            'starting_date' => 'sometimes|date',
            'ending_date' => 'sometimes|date|after_or_equal:starting_date',
            'entrol_number' => 'sometimes|nullable|string|unique:certifications,entrol_number,' . $id . '|max:255',
            'course_code' => 'sometimes|nullable|string|unique:certifications,course_code,' . $id . '|max:255',
            'verification_code' => 'sometimes|string|unique:certifications,verification_code,' . $id . '|max:255',
            'certificate_number' => 'sometimes|string|unique:certifications,certificate_number,' . $id . '|max:255',
            'nic' => 'sometimes|string|unique:certifications,nic,' . $id . '|max:255',
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

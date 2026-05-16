<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:staff,slug|max:255',
            'email' => 'required|email|unique:staff,email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'designation' => 'required|string|max:255',
            'employee_number' => 'nullable|string|unique:staff,employee_number|max:50',
            'join_date' => 'nullable|date',
            'bio' => 'nullable|string',
            'dob' => 'nullable|date',
            'nic_number' => 'nullable|string|unique:staff,nic_number|max:20',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
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

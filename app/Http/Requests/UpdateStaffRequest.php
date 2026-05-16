<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('staff') ?? $this->route('id');

        return [
            'name' => 'sometimes|required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('staff', 'slug')->ignore($id),
            ],
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('staff', 'email')->ignore($id),
            ],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'designation' => 'sometimes|required|string|max:255',
            'employee_number' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('staff', 'employee_number')->ignore($id),
            ],
            'join_date' => 'nullable|date',
            'bio' => 'nullable|string',
            'dob' => 'nullable|date',
            'nic_number' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('staff', 'nic_number')->ignore($id),
            ],
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

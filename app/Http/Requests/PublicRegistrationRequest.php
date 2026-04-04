<?php

namespace App\Http\Requests;

use App\Models\Course;
use App\Models\RegistrationProgram;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PublicRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pathway_id' => 'required|exists:pathways,id',
            'program_type' => 'required|in:course,program',
            'program_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $type = $this->input('program_type');
                    if ($type === 'course') {
                        if (!Course::where('id', $value)->active()->exists()) {
                            $fail('The selected course is invalid or inactive.');
                        }
                    } elseif ($type === 'program') {
                        if (!RegistrationProgram::where('id', $value)->active()->exists()) {
                            $fail('The selected program is invalid or inactive.');
                        }
                    }
                },
            ],
            'full_name' => 'required|string|max:255',
            'nic' => 'required|string|max:20',
            'dob' => 'required|date',
            'gender' => 'required|in:Male,Female,Other',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'district' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'school_name' => 'nullable|string|max:255',
            'occupation' => 'nullable|string|max:255',
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

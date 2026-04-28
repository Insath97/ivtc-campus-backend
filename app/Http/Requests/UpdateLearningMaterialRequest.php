<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateLearningMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'batch_id' => 'sometimes|exists:batches,id',
            'subject_name' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
            'material_type' => 'sometimes|in:notes,video,youtube,slides,handout,podcast,ebook,live_class,link',

            // File is optional on update, but if provided must match type
            'file' => 'sometimes|file|mimes:mp4,mkv,mov,avi,wmv,pdf,doc,docx,ppt,pptx,txt,zip,png,jpeg,jpg|max:102400',
            'external_url' => 'sometimes|nullable|url|max:2000',

            'uploaded_date' => 'sometimes|nullable|date',
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

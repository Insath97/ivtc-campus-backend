<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateLearningMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'batch_id' => 'required|exists:batches,id',
            'subject_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'material_type' => 'required|in:notes,video,youtube,slides,handout,podcast,ebook,live_class,link',
            
            // Conditional validation based on material_type
            'file' => 'required_if:material_type,notes,video,slides,handout,podcast,ebook|file|mimes:mp4,mov,avi,wmv,pdf,doc,docx,ppt,pptx,txt,zip|max:102400',
            'external_url' => 'required_if:material_type,youtube,live_class,link|nullable|url|max:2000',
            
            'uploaded_date' => 'nullable|date',
            'is_active' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required_if' => 'The file is required when material type is video or document.',
            'external_url.required_if' => 'The external URL is required when material type is youtube or link.',
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

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('course') ?? $this->route('id');
        return [
            'category_id' => 'sometimes|exists:categories,id',
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|nullable|string|unique:courses,slug,' . $id ,
            'code' => 'sometimes|string|unique:courses,code,' . $id,
            'duration' => 'sometimes|integer|min:1',
            'duration_unit' => 'sometimes|in:month,year',
            'level' => 'sometimes|in:Beginner,Intermediate,Advanced,Professional',
            'medium' => 'sometimes|in:English,Sinhala,Tamil',
            'short_description' => 'sometimes|string',
            'full_description' => 'sometimes|string',
            'show_in_registration' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
            'is_new' => 'sometimes|boolean',
            'has_certificate' => 'sometimes|boolean',
            'primary_image' => 'sometimes|nullable|string',
            'fees_structure' => 'sometimes|nullable|string',
            'tags' => 'sometimes|array',
            'tags.*' => 'exists:tags,id',
            'images' => 'sometimes|array',
            'images.*' => 'string',
            'videos' => 'sometimes|array',
            'videos.*.url' => 'required|string',
            'videos.*.title' => 'sometimes|nullable|string',
            'video_files' => 'sometimes|array',
            'video_files.*' => 'file|mimes:mp4,mov,avi,wmv,mkv|max:102400', // 100MB max
            'video_file_titles' => 'sometimes|array',
            'video_file_titles.*' => 'sometimes|nullable|string',
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

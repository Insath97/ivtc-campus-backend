<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'slug' => 'sometimes|nullable|string|unique:courses,slug|max:255',
            'code' => 'required|string|unique:courses,code|max:50',
            'duration' => 'required|integer|min:1',
            'duration_unit' => 'required|in:month,year',
            'level' => 'required|in:Beginner,Intermediate,Advanced,Professional',
            'medium' => 'required|in:English,Sinhala,Tamil',
            'short_description' => 'required|string',
            'full_description' => 'required|string',
            'show_in_registration' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
            'is_new' => 'sometimes|boolean',
            'primary_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp',
            'fees_structure' => 'sometimes|nullable|string',

            'tags' => 'nullable|string|max:1000',

            'images' => 'sometimes|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg,webp',

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

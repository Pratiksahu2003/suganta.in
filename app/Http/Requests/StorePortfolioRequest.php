<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePortfolioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => [
                'required',
                'file',
                'image',
                'max:5120',
                'mimes:jpg,jpeg,png,gif,webp'
            ],
            'files' => ['nullable', 'array', 'max:10'],
            'files.*' => [
                'required',
                'file',
                'max:10240',
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar'
            ],
            'category' => ['nullable', 'string', 'max:500'],
            'tags' => ['nullable', 'string', 'max:500'],
            'url' => ['nullable', 'url', 'max:500'],
            'status' => [
                'nullable',
                'string',
                Rule::in(['draft', 'published', 'archived'])
            ],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_featured' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Portfolio title is required.',
            'title.max' => 'Portfolio title must not exceed 255 characters.',
            'images.max' => 'You can upload a maximum of 10 images.',
            'images.*.image' => 'Each file must be a valid image.',
            'images.*.max' => 'Each image must not exceed 5MB.',
            'files.max' => 'You can upload a maximum of 10 files.',
            'files.*.max' => 'Each file must not exceed 10MB.',
            'url.url' => 'Please provide a valid URL.',
            'category.max' => 'Categories must not exceed 500 characters.',
            'tags.max' => 'Tags must not exceed 500 characters.',
            'status.in' => 'Invalid status selected.',
        ];
    }
}

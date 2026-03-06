<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePortfolioRequest extends FormRequest
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
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'images' => ['sometimes', 'array', 'max:10'],
            'images.*' => [
                'required',
                'file',
                'image',
                'max:5120',
                'mimes:jpg,jpeg,png,gif,webp'
            ],
            'remove_images' => ['sometimes', 'array'],
            'remove_images.*' => ['string'],
            'files' => ['sometimes', 'array', 'max:10'],
            'files.*' => [
                'required',
                'file',
                'max:10240',
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar'
            ],
            'remove_files' => ['sometimes', 'array'],
            'remove_files.*' => ['string'],
            'category' => ['sometimes', 'nullable', 'string', 'max:500'],
            'tags' => ['sometimes', 'nullable', 'string', 'max:500'],
            'url' => ['sometimes', 'nullable', 'url', 'max:500'],
            'status' => [
                'sometimes',
                'string',
                Rule::in(['draft', 'published', 'archived'])
            ],
            'order' => ['sometimes', 'integer', 'min:0'],
            'is_featured' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
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

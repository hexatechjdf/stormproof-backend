<?php

namespace App\Http\Requests\Homeowner;

use Illuminate\Foundation\Http\FormRequest;

class StorePhotoReportRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function expectsJson()
    {
        return true;
    }
    public function rules()
    {
        return [

            // Required link to a home
            'inspection_id' => 'required|exists:inspections,id',

            // Title optional
            'title' => 'nullable|string|max:255',

            // Type of inspection report
            'type' => 'nullable|in:pre_storm,post_storm,other',

            // File upload (optional, but required if no file_url)
            'file' => 'nullable|file|max:20000|mimes:jpg,jpeg,png,webp,pdf,doc,docx',

            // External URL (optional, but required if no file)
            'file_url' => 'nullable|url',

            // These are set by controller, never by user
            'pdf_path' => 'sometimes|string|nullable',
            'thumbnail_path' => 'sometimes|string|nullable',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            // Must provide either file or URL
            if (!$this->file('file') && !$this->file_url) {
                $validator->errors()->add('file', 'You must upload a file or provide a file URL.');
                $validator->errors()->add('file_url', 'You must upload a file or provide a file URL.');
            }

            // Cannot provide both at the same time
            if ($this->file('file') && $this->file_url) {
                $validator->errors()->add('file', 'Choose either a file upload OR a file URL, not both.');
                $validator->errors()->add('file_url', 'Choose either a file upload OR a file URL, not both.');
            }
        });
    }
}

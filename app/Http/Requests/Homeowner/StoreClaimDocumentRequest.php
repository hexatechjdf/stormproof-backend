<?php


namespace App\Http\Requests\Homeowner;


use Illuminate\Foundation\Http\FormRequest;


class StoreClaimDocumentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }


    public function rules()
    {
        return [
            'home_id' => 'required|exists:homes,id',
            'title' => 'nullable|string|max:255',
            'type' => 'nullable|in:pre_storm,post_storm,other',
            'file' => 'nullable|file|max:20000|mimes:jpg,jpeg,png,webp,pdf,doc,docx',
            'file_url' => 'nullable|url',
            'pdf_path' => 'sometimes|string|nullable',
            'thumbnail_path' => 'sometimes|string|nullable',
        ];
    }
}

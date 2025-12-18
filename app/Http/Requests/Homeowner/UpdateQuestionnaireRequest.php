<?php


namespace App\Http\Requests\Homeowner;


use Illuminate\Foundation\Http\FormRequest;


class UpdateQuestionnaireRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }


    public function rules()
    {
        return [
            'home_id' => 'required|exists:homes,id',
            'responses' => 'required|array',
        ];
    }
}

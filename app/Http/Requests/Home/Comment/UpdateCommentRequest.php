<?php

namespace App\Http\Requests\Home\Comment;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCommentRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'content' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'content.required' => ':attribute không được để trống'
        ];
    }

    public function attributes()
    {
        return [
            'content' => 'Nội dung',
        ];
    }
}

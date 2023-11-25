<?php

namespace App\Http\Requests\Home\Post;


use App\Http\Requests\BaseRequest;

class UpdatePostRequest extends BaseRequest
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
            'title' => 'required',
            'content' => 'required',
            'image' => 'mimes:jpeg,png,jpg,gif',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => ':attribute không được để trống',
            'content.required' => ':attribute không được để trống',
            'image.mimes' => ':attribute chỉ hỗ trợ các file có định dạnh là: jpeg,png,jpg,gif',
        ];
    }

    public function attributes()
    {
        return [
            'title' => 'Tiêu đề',
            'content' => 'Nội dung',
            'image' => 'Ảnh'
        ];
    }
}

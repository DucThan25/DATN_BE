<?php

namespace App\Http\Requests\Community\Group;

use App\Http\Requests\BaseRequest;

class UpdateGroupRequest extends BaseRequest
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
            'name' => ['required','string', 'min:3', 'max: 255', "unique:groups,name, $this->id"],
            'introduce' => '',
            'type' => 'required',
            'avatar' => 'mimes:jpeg,png,jpg,gif',
            'cover_image' => 'mimes:jpeg,png,jpg,gif',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => ':attribute không được để trống',
            'type.required' => ':attribute không được để trống',
            'avatar.mimes' => ':attribute chỉ hỗ trợ các file có định dạnh là: jpeg,png,jpg,gif',
            'cover_image.mimes' => ':attribute chỉ hỗ trợ các file có định dạnh là: jpeg,png,jpg,gif'
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'Tên nhóm',
            'type' => 'Loại nhóm',
            'avatar' => 'Ảnh đại diện',
            'cover_image' => 'Ảnh bìa'
        ];
    }
}

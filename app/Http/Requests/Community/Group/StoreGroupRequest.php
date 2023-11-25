<?php

namespace App\Http\Requests\Community\Group;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class StoreGroupRequest extends BaseRequest
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
            'name' => 'required|min:3|max:255|unique:groups|string',
            'introduce' => '',
            'type' => 'required',
            'avatar' => 'required|mimes:jpeg,png,jpg,gif',
            'cover_image' => 'required|mimes:jpeg,png,jpg,gif',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => ':attribute không được để trống',
            'name.unique' => ':attribute đã tồn tại',
            'type.required' => ':attribute không được để trống',
            'avatar.mimes' => ':attribute chỉ hỗ trợ các file có định dạnh là: jpeg,png,jpg,gif',
            'cover_image.mimes' => ':attribute chỉ hỗ trợ các file có định dạnh là: jpeg,png,jpg,gif',
            'avatar.required' => ':attribute không được để trống',
            'cover_image.required' => ':attribute không được để trống'
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

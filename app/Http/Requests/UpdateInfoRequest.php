<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class UpdateInfoRequest extends BaseRequest
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
            'name' => 'required',
            'date' => $this->date ? 'date_format:d-m-Y' : '',
            'address' => '',
            'avatar' => 'mimes:jpeg,png,jpg,gif',
            'cover_image' => 'mimes:jpeg,png,jpg,gif',
            'introduce' => '',
            'gender' => 'integer'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => ':attribute không được để trống',
            'avatar.mimes' => ':attribute chỉ được hỗ trợ kiểu jpg,jpeg,png,gif',
            'cover_image.mimes' => ':attribute chỉ được hỗ trợ kiểu jpg,jpeg,png,gif',
            'date.date_format' => ':attribute phải theo định dạng dd-mm-yyyy'
        ];
    }

    public function attributes()
    {
        return [
          'name' => 'Tên',
          'avatar' => 'Ảnh đại diện',
          'cover_image' => 'Ảnh bìa',
          'date' => 'Ngày sinh'
        ];
    }
}

<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'password' => 'required|min:6',
            're_password' => 'required|same:password',
        ];
    }
    public function messages()
    {
        return [
            're_password.same' => ':attribute không trùng khớp',
            'password.min' => ':attribute cần tối thiểu 6 kí tự',
            're_password.required' => ':attribute xác nhận không được để trống',
            '*.required' => ':attribute không được để trống',
        ];
    }
    public function attributes()
    {
        return [
            'password' => 'Mật khẩu',
            're_password' => 'Mật khẩu xác nhận',
        ];
    }
}

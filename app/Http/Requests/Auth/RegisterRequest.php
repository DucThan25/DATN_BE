<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'name' => 'required|max:30',
            'email' => 'required|email|unique:users|regex:/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix',
        ];
    }

    public function messages()
    {
        return [
            'name.max' => ':attribute của bạn phải ít hơn 30 ký tự',
            '*.regex' => ':attribute sai định dạng',
            'email.email' => ':attribute sai định dạng',
            '*.unique' => ':attribute đã được dùng để đăng ký tài khoản',
        ];
    }
    public function attributes()
    {
        return [
            'email' => 'Email',
            'name' => 'Tên',
        ];
    }
}

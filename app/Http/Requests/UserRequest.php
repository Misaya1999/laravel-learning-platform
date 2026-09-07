<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UserRequest extends FormRequest
{
    
    public function authorize()
    {
        return true;
    }
    
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'prohibited',
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20|unique:users,phone,' . $this->user()->id,
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1024',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Vui lòng nhập name',
            'email.prohibited' => 'Địa chỉ email của tài khoản không thể thay đổi.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự',
            'phone.max' => 'Số điện thoại không được vượt quá 20 ký tự',
            'message.max' => 'Tin nhắn không được vượt quá 500 ký tự',
            'avatar.image' => 'Avatar phải là hình ảnh',
            'avatar.mimes' => 'Avatar phải là jpeg, png, jpg, gif',
            'avatar.max' => 'Avatar không được lớn hơn 1MB',
        ];
    }
}

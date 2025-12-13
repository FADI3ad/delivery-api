<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|unique:users,email',
            'phone'           => 'required|string|unique:users,phone',
            'national_id'     => 'nullable|string|unique:users,national_id',
            'vechicle_type'   => 'nullable|in:سيارة,عميل,توك توك,موتوسيكل,تروسيكل,دراجة',
            'role'            => 'nullable|in:admin,عميل,سائق',
            'password'        => 'required|string|min:6|confirmed',
            'image'           => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'national_image'  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'الاسم مطلوب',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة',
            'email.unique' => 'البريد الإلكتروني مستخدم من قبل',
            'phone.required' => 'رقم الهاتف مطلوب',
            'phone.unique' => 'رقم الهاتف مستخدم من قبل',
            'national_id.unique' => 'الرقم القومي مستخدم من قبل',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.min' => 'كلمة المرور يجب أن تكون على الأقل 6 أحرف',
            'password.confirmed' => 'كلمة المرور لا تتطابق مع التأكيد',
        ];
    }
}

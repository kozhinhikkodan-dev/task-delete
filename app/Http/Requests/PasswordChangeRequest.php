<?php

namespace App\Http\Requests;

use Hash;
use Illuminate\Foundation\Http\FormRequest;

class PasswordChangeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'old_password' => [
                'required',
                'min:8',
                function ($attribute, $value, $fail) {
                    if (!Hash::check($value, auth()->user()->password)) {
                        $fail('The old password is incorrect.');
                    }
                },
            ],
            'password' => [
                'required',
                'min:8',
                function ($attribute, $value, $fail) {
                    if (Hash::check($value, auth()->user()->password)) {
                        $fail('The new password must be different from the old password.');
                    }
                },
            ],
            'password_confirmation' => 'required_with:password|same:password',
        ];
    }

    public function messages()
    {
        return [
            // 'permissions.required' => 'At least one permission is required',
            'password_confirmation.required_with' => 'Please verify your password',
            'password_confirmation.same' => 'Password does not match',
        ];
    }

    public function getData(): array
    {
        return $this->validationData();
    }

    public function prepareForValidation(): void
    {
        // If role is not Tailor, set commission to null
        // if ($this->input('old_passwod') !== Role::COMMISSION_REQUIRED_ROLE) {
        //     $this->merge([
        //         'commission' => null,
        //     ]);
        // }
    }


}

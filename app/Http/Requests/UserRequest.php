<?php

namespace App\Http\Requests;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
        $rules = [
            'name' => 'required|max:50|unique:users,name',
            'email' => 'required|email|unique:users,email',
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|min:8',
            'password_confirmation' => 'required_with:password|same:password',
            'role_name' => 'required|exists:roles,name',
            'min_task_per_day' => 'required|integer|min:1',
            'max_task_per_day' => 'required|integer|min:1',
            'available_days' => 'required|array|min:1',
            'available_days.*' => 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'status' => 'required|in:active,inactive'
        ];

        if($this->isMethod('PUT')){
            $user = $this->route('user');
            $rules['name'] = 'required|max:50|unique:users,name' . ($user ? ',' . $user->id : '');
            $rules['email'] = 'required|email|unique:users,email' . ($user ? ',' . $user->id : '');
            $rules['username'] = 'required|string|max:50|unique:users,username' . ($user ? ',' . $user->id : '');
            $rules['password'] = 'nullable|min:8';

            // Add custom validation for max_task_per_day
            $rules['max_task_per_day'] = 'required|integer|min:1|gte:min_task_per_day';
        } else {
            // Add custom validation for max_task_per_day on create
            $rules['max_task_per_day'] = 'required|integer|min:1|gte:min_task_per_day';
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'name' => 'User Name',
            'role_name' => 'Role',
            'min_task_per_day' => 'Minimum Task Per Day',
            'max_task_per_day' => 'Maximum Task Per Day',
            'available_days' => 'Available Days',
            'status' => 'Status',
        ];
    }

    public function messages()
    {
        return [
            'password_confirmation.required_with' => 'Please verify your password',
            'password_confirmation.same' => 'Password does not match',
            'available_days.required' => 'Please select at least one available day',
            'available_days.min' => 'Please select at least one available day',
            'max_task_per_day.gte' => 'Maximum task per day must be greater than or equal to minimum task per day',
        ];
    }

    public function getData(): array
    {
        $data = $this->only([
            'name',
            'email',
            'username',
            'password',
            'min_task_per_day',
            'max_task_per_day',
            'available_days',
            'status'
        ]);

        // Hash password if provided
        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        return $data;
    }
}

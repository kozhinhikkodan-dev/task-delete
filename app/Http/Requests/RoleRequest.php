<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoleRequest extends FormRequest
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
            'name' => 'required|max:20|unique:roles,name',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name'
        ];
        if($this->isMethod('PUT')){
            $role = $this->route('role');
            $rules['name'] = 'required|max:20|unique:roles,name' . ($role ? ',' . $role->id : '');
        }
        return $rules;
    }


    public function attributes(): array
    {
        return [
            'name' => 'Role Name'
        ];
    }

    public function messages()
    {
        return [
            'permissions.required' => 'At least one permission is required',
        ];
    }

    public function getData(): array
    {
        return $this->validationData();
    }


}

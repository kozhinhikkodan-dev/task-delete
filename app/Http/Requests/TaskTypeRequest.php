<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskTypeRequest extends FormRequest
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
            'name' => 'required|string|max:100|unique:task_types,name',
            'code' => 'nullable|string|max:20|unique:task_types,code',
            'description' => 'nullable|string',
            'base_rate' => 'nullable|numeric|min:0',
            'estimated_time_minutes' => 'nullable|integer|min:1|max:1440',
            'priority' => 'nullable|in:low,medium,high',
            'status' => 'required|in:active,inactive',
            'requirements' => 'nullable|array',
            'requirements.*' => 'string|max:255',
            'meta_data' => 'nullable|array',
        ];

        if ($this->isMethod('PUT')) {
            $taskType = $this->route('task_type');
            $rules['name'] = 'required|string|max:100|unique:task_types,name' . ($taskType ? ',' . $taskType->id : '');
            $rules['code'] = 'nullable|string|max:20|unique:task_types,code' . ($taskType ? ',' . $taskType->id : '');
        }

        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Task type name is required.',
            'name.unique' => 'Task type name already exists.',

            'code.unique' => 'Task type code already exists.',
            'base_rate.required' => 'Base rate is required.',
            'base_rate.numeric' => 'Base rate must be a valid number.',
            'base_rate.min' => 'Base rate must be at least 0.',
            'estimated_time_minutes.required' => 'Estimated time is required.',
            'estimated_time_minutes.integer' => 'Estimated time must be a valid number.',
            'estimated_time_minutes.min' => 'Estimated time must be at least 1 minute.',
            'estimated_time_minutes.max' => 'Estimated time cannot exceed 1440 minutes (24 hours).',
            'priority.required' => 'Priority is required.',
            'priority.in' => 'Priority must be low, medium, or high.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be active or inactive.',
            'requirements.*.string' => 'Each requirement must be a string.',
            'requirements.*.max' => 'Each requirement cannot exceed 255 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'Task Type Name',
            'code' => 'Task Type Code',
            'description' => 'Description',
            'base_rate' => 'Base Rate',
            'estimated_time_minutes' => 'Estimated Time',
            'priority' => 'Priority',
            'status' => 'Status',
            'requirements' => 'Requirements',
            'meta_data' => 'Meta Data',
        ];
    }

    /**
     * Get the validated data for the request.
     *
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        $data = $this->validated();
        
        // Auto-generate code if not provided
        if (empty($data['code'])) {
            $data['code'] = strtoupper(str_replace(' ', '_', $data['name']));
        }
        
        // Set default values for hidden fields
        $data['base_rate'] = $data['base_rate'] ?? 0;
        $data['estimated_time_minutes'] = $data['estimated_time_minutes'] ?? 60;
        $data['priority'] = $data['priority'] ?? 'medium';
        
        // Convert requirements array to proper format
        if (isset($data['requirements']) && is_array($data['requirements'])) {
            $data['requirements'] = array_filter($data['requirements'], function($value) {
                return !empty(trim($value));
            });
        } else {
            $data['requirements'] = [];
        }

        return $data;
    }
}

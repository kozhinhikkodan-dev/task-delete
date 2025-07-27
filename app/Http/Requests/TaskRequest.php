<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends FormRequest
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
            'customer_id' => 'required|exists:customers,id',
            'task_type_id' => 'required|exists:task_types,id',
            'task_date' => 'required|date',
            'note_content' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'estimated_cost' => 'nullable|numeric|min:0',
            'estimated_duration_minutes' => 'nullable|integer|min:1',
            'started_at' => 'nullable|date',
            'completed_at' => 'nullable|date',
            'completion_notes' => 'nullable|string',
        ];

        // assigned_to can be optional for all users (automatic assignment)
        $rules['assigned_to'] = 'nullable|exists:users,id';

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
            'customer_id.required' => 'Please select a customer.',
            'customer_id.exists' => 'The selected customer is invalid.',
            'task_type_id.required' => 'Please select a task type.',
            'task_type_id.exists' => 'The selected task type is invalid.',
            'assigned_to.required' => 'Please assign the task to a user or leave empty for automatic assignment.',
            'assigned_to.exists' => 'The selected user is invalid.',
            'task_date.required' => 'Please select a task date.',
            'task_date.date' => 'The task date must be a valid date.',
            'status.required' => 'Please select a status.',
            'status.in' => 'The selected status is invalid.',
            'estimated_cost.numeric' => 'The estimated cost must be a number.',
            'estimated_cost.min' => 'The estimated cost must be at least 0.',
            'estimated_duration_minutes.integer' => 'The estimated duration must be a whole number.',
            'estimated_duration_minutes.min' => 'The estimated duration must be at least 1 minute.',
            'started_at.date' => 'The started at must be a valid date.',
            'completed_at.date' => 'The completed at must be a valid date.',
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
        
        // Set default status if not provided
        if (!isset($data['status'])) {
            $data['status'] = 'pending';
        }
        
        return $data;
    }
} 
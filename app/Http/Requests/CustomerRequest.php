<?php

namespace App\Http\Requests;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class CustomerRequest extends FormRequest
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
            'name' => 'required|max:100',
            'email' => 'required|email|unique:customers,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'status' => 'required|in:active,inactive',
            'service_start_date' => 'nullable|date',
            'service_renew_date' => 'nullable|date|after_or_equal:service_start_date',
            'total_posters' => 'nullable|numeric|min:0',
            'total_video_edits' => 'nullable|numeric|min:0',
            'total_blog_posts' => 'nullable|numeric|min:0',
            'total_anchoring_video' => 'nullable|numeric|min:0',
            'posters_assigned' => [
                'nullable',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    $this->isStaffAvailable($value, $this->input('total_posters'), $this->input('service_start_date'), $this->input('service_renew_date'), $fail);
                }
            ],
            'video_edits_assigned' => [
                'nullable',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    $this->isStaffAvailable($value, $this->input('total_video_edits'), $this->input('service_start_date'), $this->input('service_renew_date'), $fail);
                }
            ],
            'blog_posts_assigned' => [
                'nullable',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    $this->isStaffAvailable($value, $this->input('total_blog_posts'), $this->input('service_start_date'), $this->input('service_renew_date'), $fail);
                }
            ],
            'anchoring_video_assigned' => [
                'nullable',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    $this->isStaffAvailable($value, $this->input('total_anchoring_video'), $this->input('service_start_date'), $this->input('service_renew_date'), $fail);
                }
            ],
        ];

        if ($this->isMethod('PUT')) {
            $customer = $this->route('customer');
            $rules['email'] = 'required|email|unique:customers,email' . ($customer ? ',' . $customer->id : '');
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'name' => 'Customer Name',
            'email' => 'Email Address',
            'phone' => 'Phone Number',
            'address' => 'Address',
            'city' => 'City',
            'state' => 'State',
            'zip_code' => 'ZIP Code',
            'country' => 'Country',
            'status' => 'Status',
            'service_start_date' => 'Service Start Date',
            'service_renew_date' => 'Service Renewal Date',
            'total_posters' => 'Total Posters',
            'total_video_edits' => 'Total Video Edits',
            'total_blog_posts' => 'Total Blog Posts',
            'total_anchoring_video' => 'Total Anchoring Video',
            'posters_assigned' => 'Posters Assigned To',
            'video_edits_assigned' => 'Video Edits Assigned To',
            'blog_posts_assigned' => 'Blog Posts Assigned To',
            'anchoring_video_assigned' => 'Anchoring Video Assigned To',
        ];
    }

    public function messages()
    {
        return [
            'service_renew_date.after_or_equal' => 'Service renewal date must be after or equal to service start date',
        ];
    }

    public function getData(): array
    {
        return $this->only([
            'name',
            'email',
            'phone',
            'address',
            'city',
            'state',
            'zip_code',
            'country',
            'status',
            'service_start_date',
            'service_renew_date',
            'total_posters',
            'total_video_edits',
            'total_blog_posts',
            'total_anchoring_video',
        ]);
    }

    /**
     * Get task assignment data
     */
    public function getAssignmentData(): array
    {
        return [
            'posters_assigned' => $this->input('posters_assigned'),
            'video_edits_assigned' => $this->input('video_edits_assigned'),
            'blog_posts_assigned' => $this->input('blog_posts_assigned'),
            'anchoring_video_assigned' => $this->input('anchoring_video_assigned'),
        ];
    }
    private function isStaffAvailable($staffId, $totalTaskcount, $start, $renew, $fail)
    {
        $user = User::find($staffId);

        if (!$user || !$user->hasRole('Staff') || $user->status !== 'active') {
            $fail('User is not a staff member or is not active.');
        }
        // Convert to Carbon instances if not already
        $start = \Carbon\Carbon::parse($start);
        $renew = \Carbon\Carbon::parse($renew);

        // Ensure available_days is an array
        $availableDays = $user->available_days ?? [];

        if (!is_array($availableDays) || empty($availableDays)) {
            $fail('Staff has no available days set.');
            return;
        }

        // Count matching available days between the range
        $daysCount = 0;
        $current = $start->copy();

        while ($current->lte($renew)) {
            if (in_array($current->format('l'), $availableDays)) {
                $daysCount++;
            }
            $current->addDay();
        }

        $currentAutoTasksCount = Task::where('assigned_to', $staffId)
            // ->whereDate('task_date', $current->format('Y-m-d'))
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->where('is_auto', true)
            ->count();

        $availableMaxTasksCount = $user->max_task_per_day*$daysCount;
        $availableTasksCount = $availableMaxTasksCount - $currentAutoTasksCount;
        
        if ($totalTaskcount > $availableTasksCount) {
            $fail('Staff is not available for the selected date range, only ' . $availableTasksCount . ' tasks can be assigned.');
        }

    }
}

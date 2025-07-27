<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\Customer;
use App\Models\TaskType;
use App\Models\User;
use Illuminate\Database\Seeder;

class TasksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing records
        $customers = Customer::all();
        $taskTypes = TaskType::all();
        $users = User::all();

        if ($customers->isEmpty() || $taskTypes->isEmpty() || $users->isEmpty()) {
            $this->command->info('Skipping TasksSeeder - Required data (customers, task types, users) not found');
            return;
        }

        $tasks = [
            [
                'customer_id' => $customers->first()->id,
                'task_type_id' => $taskTypes->where('name', 'Web Development')->first()->id ?? $taskTypes->first()->id,
                'assigned_to' => $users->first()->id,
                'task_date' => now()->addDays(1),
                'note_content' => 'Create a responsive landing page for the new product launch. Include contact form, testimonials section, and integration with the existing CRM system.',
                'status' => 'pending',
                'estimated_cost' => 250.00,
                'estimated_duration_minutes' => 480,
            ],
            [
                'customer_id' => $customers->skip(1)->first()->id ?? $customers->first()->id,
                'task_type_id' => $taskTypes->where('name', 'Content Writing')->first()->id ?? $taskTypes->first()->id,
                'assigned_to' => $users->skip(1)->first()->id ?? $users->first()->id,
                'task_date' => now()->addDays(2),
                'note_content' => 'Write 10 blog posts about digital marketing trends. Each post should be 1000-1500 words with SEO optimization and relevant keywords.',
                'status' => 'in_progress',
                'estimated_cost' => 150.00,
                'estimated_duration_minutes' => 600,
                'started_at' => now()->subHours(2),
            ],
            [
                'customer_id' => $customers->first()->id,
                'task_type_id' => $taskTypes->where('name', 'Data Entry')->first()->id ?? $taskTypes->first()->id,
                'assigned_to' => $users->first()->id,
                'task_date' => now()->subDays(1),
                'note_content' => 'Enter customer data from Excel spreadsheet into the CRM system. Verify email addresses and phone numbers for accuracy.',
                'status' => 'completed',
                'estimated_cost' => 50.00,
                'estimated_duration_minutes' => 120,
                'started_at' => now()->subDays(1)->addHours(9),
                'completed_at' => now()->subDays(1)->addHours(11),
                'completion_notes' => 'Successfully imported 500 customer records. 15 records had invalid email addresses which were flagged for review.',
            ],
            [
                'customer_id' => $customers->skip(2)->first()->id ?? $customers->first()->id,
                'task_type_id' => $taskTypes->skip(1)->first()->id ?? $taskTypes->first()->id,
                'assigned_to' => $users->skip(2)->first()->id ?? $users->first()->id,
                'task_date' => now()->addDays(5),
                'note_content' => 'Design and develop a mobile app prototype for the inventory management system. Include user authentication, product scanning, and reporting features.',
                'status' => 'pending',
                'estimated_cost' => 500.00,
                'estimated_duration_minutes' => 960,
            ],
            [
                'customer_id' => $customers->first()->id,
                'task_type_id' => $taskTypes->skip(2)->first()->id ?? $taskTypes->first()->id,
                'assigned_to' => $users->first()->id,
                'task_date' => now()->subDays(3),
                'note_content' => 'Project was cancelled due to budget constraints and changing requirements.',
                'status' => 'cancelled',
                'estimated_cost' => 100.00,
                'estimated_duration_minutes' => 240,
            ],
        ];

        foreach ($tasks as $taskData) {
            Task::create($taskData);
        }

        $this->command->info('Sample tasks created successfully');
    }
}

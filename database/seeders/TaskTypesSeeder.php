<?php

namespace Database\Seeders;

use App\Models\TaskType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaskTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $taskTypes = [
            [
                'name' => 'Data Entry',
                'code' => 'DATA_ENTRY',
                'description' => 'Simple data entry tasks including form filling, data transcription, and basic data processing.',
                'base_rate' => 5.00,
                'estimated_time_minutes' => 30,
                'priority' => 'low',
                'status' => 'active',
                'requirements' => ['Basic computer skills', 'Attention to detail', 'Typing speed min 40 WPM'],
            ],
            [
                'name' => 'Content Writing',
                'code' => 'CONTENT_WRITE',
                'description' => 'Creating written content including articles, blog posts, product descriptions, and marketing copy.',
                'base_rate' => 15.00,
                'estimated_time_minutes' => 120,
                'priority' => 'medium',
                'status' => 'active',
                'requirements' => ['Excellent writing skills', 'Grammar knowledge', 'Research abilities'],
            ],
            [
                'name' => 'Web Development',
                'code' => 'WEB_DEV',
                'description' => 'Website development, maintenance, and programming tasks including front-end and back-end development.',
                'base_rate' => 50.00,
                'estimated_time_minutes' => 480,
                'priority' => 'high',
                'status' => 'active',
                'requirements' => ['Programming knowledge', 'HTML/CSS/JavaScript', 'Framework experience'],
            ],
            [
                'name' => 'Graphic Design',
                'code' => 'GRAPHIC_DESIGN',
                'description' => 'Creating visual designs including logos, banners, social media graphics, and marketing materials.',
                'base_rate' => 25.00,
                'estimated_time_minutes' => 180,
                'priority' => 'medium',
                'status' => 'active',
                'requirements' => ['Design software proficiency', 'Creative skills', 'Portfolio required'],
            ],
            [
                'name' => 'Social Media Management',
                'code' => 'SOCIAL_MEDIA',
                'description' => 'Managing social media accounts, creating posts, engaging with followers, and analytics reporting.',
                'base_rate' => 12.00,
                'estimated_time_minutes' => 90,
                'priority' => 'medium',
                'status' => 'active',
                'requirements' => ['Social media experience', 'Content creation skills', 'Analytics knowledge'],
            ],
            [
                'name' => 'Translation',
                'code' => 'TRANSLATION',
                'description' => 'Translating documents, articles, and content between different languages.',
                'base_rate' => 20.00,
                'estimated_time_minutes' => 240,
                'priority' => 'medium',
                'status' => 'active',
                'requirements' => ['Fluency in multiple languages', 'Cultural knowledge', 'Translation experience'],
            ],
            [
                'name' => 'Customer Support',
                'code' => 'CUSTOMER_SUPPORT',
                'description' => 'Providing customer service through chat, email, or phone support.',
                'base_rate' => 8.00,
                'estimated_time_minutes' => 60,
                'priority' => 'high',
                'status' => 'active',
                'requirements' => ['Communication skills', 'Problem-solving abilities', 'Patience'],
            ],
            [
                'name' => 'Video Editing',
                'code' => 'VIDEO_EDIT',
                'description' => 'Editing videos for various purposes including promotional content, tutorials, and entertainment.',
                'base_rate' => 30.00,
                'estimated_time_minutes' => 360,
                'priority' => 'medium',
                'status' => 'active',
                'requirements' => ['Video editing software', 'Creative skills', 'Technical knowledge'],
            ],
            [
                'name' => 'SEO Optimization',
                'code' => 'SEO_OPT',
                'description' => 'Search engine optimization tasks including keyword research, content optimization, and link building.',
                'base_rate' => 18.00,
                'estimated_time_minutes' => 150,
                'priority' => 'medium',
                'status' => 'active',
                'requirements' => ['SEO knowledge', 'Analytics tools', 'Content optimization'],
            ],
            [
                'name' => 'Virtual Assistant',
                'code' => 'VIRTUAL_ASSISTANT',
                'description' => 'General administrative tasks including email management, scheduling, and research.',
                'base_rate' => 10.00,
                'estimated_time_minutes' => 120,
                'priority' => 'low',
                'status' => 'active',
                'requirements' => ['Organization skills', 'Communication abilities', 'Multi-tasking'],
            ],
            [
                'name' => 'Legacy Task Type',
                'code' => 'LEGACY_TASK',
                'description' => 'An outdated task type that is no longer actively used but kept for historical records.',
                'base_rate' => 1.00,
                'estimated_time_minutes' => 15,
                'priority' => 'low',
                'status' => 'inactive',
                'requirements' => ['Basic skills'],
            ],
        ];

        foreach ($taskTypes as $taskTypeData) {
            TaskType::updateOrCreate(
                ['code' => $taskTypeData['code']],
                $taskTypeData
            );
        }
    }
}

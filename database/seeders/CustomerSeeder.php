<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'phone' => '+1 (555) 123-4567',
                'address' => '123 Main St',
                'city' => 'New York',
                'state' => 'NY',
                'zip_code' => '10001',
                'country' => 'USA',
                'status' => 'active',
                'service_start_date' => '2024-01-01',
                'service_renew_date' => '2024-12-31',
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane.smith@example.com',
                'phone' => '+1 (555) 987-6543',
                'address' => '456 Oak Ave',
                'city' => 'Los Angeles',
                'state' => 'CA',
                'zip_code' => '90001',
                'country' => 'USA',
                'status' => 'active',
                'service_start_date' => '2024-02-01',
                'service_renew_date' => '2025-01-31',
            ],
            [
                'name' => 'Michael Johnson',
                'email' => 'michael.johnson@example.com',
                'phone' => '+1 (555) 456-7890',
                'address' => '789 Pine Rd',
                'city' => 'Chicago',
                'state' => 'IL',
                'zip_code' => '60001',
                'country' => 'USA',
                'status' => 'inactive',
                'service_start_date' => '2023-06-01',
                'service_renew_date' => '2024-05-31',
            ],
            [
                'name' => 'Emily Davis',
                'email' => 'emily.davis@example.com',
                'phone' => '+1 (555) 321-0987',
                'address' => '321 Elm St',
                'city' => 'Houston',
                'state' => 'TX',
                'zip_code' => '77001',
                'country' => 'USA',
                'status' => 'active',
                'service_start_date' => '2024-03-01',
                'service_renew_date' => '2025-02-28',
            ],
            [
                'name' => 'Robert Wilson',
                'email' => 'robert.wilson@example.com',
                'phone' => '+1 (555) 654-3210',
                'address' => '654 Maple Ave',
                'city' => 'Phoenix',
                'state' => 'AZ',
                'zip_code' => '85001',
                'country' => 'USA',
                'status' => 'active',
                'service_start_date' => '2024-01-15',
                'service_renew_date' => '2025-01-14',
            ],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }
    }
}

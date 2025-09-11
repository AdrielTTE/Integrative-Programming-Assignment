<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    public function run()
    {
        Customer::create([
            'customer_id' => 'CUST20240101SAMPLE',
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'phone' => '+1234567890',
            'address' => '123 Main Street, City, State 12345',
            'status' => 'active'
        ]);

        Customer::create([
            'customer_id' => 'CUST20240102SAMPLE',
            'name' => 'Jane Smith',
            'email' => 'jane.smith@example.com',
            'phone' => '+1234567891',
            'address' => '456 Oak Avenue, City, State 12346',
            'status' => 'active'
        ]);
    }
}

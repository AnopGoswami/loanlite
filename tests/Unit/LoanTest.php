<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use App\Models\Customer;
use App\Models\Loan;


class LoanTest extends TestCase
{
    use RefreshDatabase;
 
    public function test_api_healthcheck()
    {
        $response = $this->get('api/healthcheck');

        $response->assertStatus(200);
    }

    public function test_admin_can_register() {

        $data = [
            'name' => 'testadmin',
            'email'=> $email = time().'@test.com',
            'password' => Hash::make('testpass123')
        ];

        $this->post('api/v1/admin/register', $data)
            ->assertStatus(200)
            ->assertJson( ['data'=>[
                'name' => 'testadmin',
                'email'=> $email 
            ]]);
    }

    public function test_customer_can_register() {

        $data = [
            'name' => 'testcustomer',
            'email'=> $email = time().'@test.com',
            'password' => Hash::make('testpass123')
        ];

        $this->post('api/v1/customer/register', $data)
            ->assertStatus(200)
            ->assertJson( ['data'=>[
                'name' => 'testcustomer',
                'email'=> $email 
            ]]);
    }

    public function test_user_table()
    {
        User::factory()->count(3)->create();

        $this->assertDatabaseCount('users', 3);
        
    }

    public function test_customer_table()
    {
        Customer::factory()->count(3)->create();

        $this->assertDatabaseCount('customers', 3);
    }

    public function test_loan_table()
    {
        Loan::factory()->count(3)->create();

        $this->assertDatabaseCount('loans', 3);
    }
}

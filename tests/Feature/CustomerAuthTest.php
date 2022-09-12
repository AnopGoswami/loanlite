<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Nette\Utils\Random;
use Tests\TestCase;

class CustomerAuthTest extends TestCase
{
    use RefreshDatabase;
    
     /**
     * Test customer register required fields
     *
     * @return void
     */
    public function testRequiredFieldsForRegistration()
    {
        $this->json('POST', 'api/v1/customer/register', ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                "name" => ["The name field is required."],
                "email" => ["The email field is required."],
                "password" => ["The password field is required."],
            ]);
    }

    /**
     * Test customer login unauthorised
     *
     * @return void
     */
    public function testCustomerLoginUnAuthorised()
    {
        $this->json('POST', 'api/v1/customer/login')
            ->assertStatus(401)                
            ->assertJson([
                'message' => 'Unauthorized'
            ]);                        
    }

    /**
     * Test customer registration success
     *
     * @return void
     */
    public function testRegisterSucess()
    {
        $response = $this->json('POST', 'api/v1/customer/register', [
            'name'  => 'testcustomer',
            'email'  =>  time().'testcustomer@test.com',
            'password'  => Random::generate(8),
        ]);
       
        $response->assertStatus(200);

        // Receive our token
        $this->assertArrayHasKey('access_token',$response->json());

    }

    /**
     * Test customer login success
     *
     * @return void
     */
    public function testLoginSuccess()
    {
        // Creating customer user
        Customer::create([
            'name' => 'testcustomer',
            'email'=> $email = time().'@test.com',
            'password' => Hash::make('testpass123')
        ]);

        // Simulated landing
        $response = $this->json('POST','api/v1/customer/login',[
            'email' => $email,
            'password' => 'testpass123',
        ]);
        
        // Determine whether the login is successful and receive token 
        $response->assertStatus(200);

        $this->assertArrayHasKey('access_token',$response->json());

    }

    
}

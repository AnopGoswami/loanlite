<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use App\Models\Loan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Nette\Utils\Random;
use Tests\TestCase;

class LoanTest extends TestCase
{
    use RefreshDatabase;
     
     /**
     * Authenticate admin
     *
     * @return void
     */
    public function authenticateAdmin()
    {
        // Creating admin user
        User::create([
            'name' => 'testadmin',
            'email'=> $email = time().'@test.com',
            'password' => Hash::make('testpass123')
        ]);

        // Simulated landing
        $response = $this->json('POST','api/v1/admin/login',[
            'email' => $email,
            'password' => 'testpass123',
        ]);
        
        return $response->json('access_token');
    }

    /**
     * Authenticate customer
     *
     * @return void
     */
    public function authenticateCustomer()
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

        return $response->json('access_token');
    }

     /**
     * Apply Loan
     *
     * @return void
     */
    public function testApplyLoan()
    {
        //Get token
        $token = $this->authenticateCustomer();
        
        //Send request
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->json('POST','api/v1/customer/loan/apply',[
            'amount' => 100,
            'term' => 3,
        ]);

        $response->assertStatus(200);
    }

    /**
     * View Loan
     *
     * @return void
     */
    public function testViewLoan()
    {
        //Get token
        $token = $this->authenticateCustomer();
        
        //Apply loan request
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->json('POST','api/v1/customer/loan/apply',[
            'amount' => 100,
            'term' => 3,
        ]);

        $loan=Loan::orderBy('id','desc')->first();

        //View loan request
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->json('GET','api/v1/customer/loan/view/'.$loan->id);

        $response->assertStatus(200);
    }


    /**
     * Approve Loan
     *
     * @return void
     */
    public function testApproveLoan()
    {
        //Get token
        $token = $this->authenticateAdmin();
        
        //Send request
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->json('POST','api/v1/admin/loan/approve/1');

        $response->assertStatus(200);
    }

    /**
     * Pay Loan
     *
     * @return void
     */
    public function testPayLoan()
    {
        //Get customer token
        $token = $this->authenticateCustomer();
        
        //Apply loan request
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->json('POST','api/v1/customer/loan/apply',[
            'amount' => 100,
            'term' => 3,
        ]);

        $loan=Loan::orderBy('id','desc')->first();

        //Get token
        $adminToken = $this->authenticateAdmin();
        
        //Approve loan by admin
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $adminToken,
        ])->json('POST','api/v1/admin/loan/approve/'.$loan->id);


         //Pay loan by customer first payments
         $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->json('POST','api/v1/customer/loan/pay/'.$loan->id,[
            'amount' => 33.33
        ]);

        $response->assertStatus(200);

        //Pay loan by customer second payments
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->json('POST','api/v1/customer/loan/pay/'.$loan->id,[
            'amount' => 40
        ]);

        $response->assertStatus(200);

        //Pay loan by customer remaining payment
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->json('POST','api/v1/customer/loan/pay/'.$loan->id,[
            'amount' => 26.67
        ]);

        $response->assertStatus(200);
    }
    
}

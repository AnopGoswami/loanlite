<?php
namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Auth;
use Validator;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth as FacadesAuth;

/**
 * CustomerController handles all operations related to customer
 *
 */
class CustomerController extends Controller
{
    
    /**
     * Register customer
     *
     * @param  object  $request
     * @return json response
     */
    public function register(Request $request)
    {
        //Define validation rules
        $validator = Validator::make($request->all(),[
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:customers',
            'password' => 'required|string|min:8'
        ]);

        //Validating request
        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        //Create customer entry
        $customer = Customer::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
         ]);

        //Generate token
        $token = $customer->createToken('auth_token')->plainTextToken;

        //Return response with token
        return response()->json(['data' => $customer,'access_token' => $token, 'token_type' => 'Bearer']);
    }

     /**
     * Login customer customer
     *
     * @param  object  $request
     * @return json response
     */
    public function login(Request $request)
    {
        //Checking login credentials
        if (!Auth::guard('customer')->attempt($request->only('email', 'password')))
        {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        //Get customer instance by email
        $customer = Customer::where('email', $request['email'])->firstOrFail();

        //Generate new token
        $token = $customer->createToken('auth_token')->plainTextToken;

        //Return response with token
        return response()->json(['message' => 'Login success','access_token' => $token, 'token_type' => 'Bearer']);
    }

    
    /**
     * Destroy customer customer token
     *
     * @return json response
     */
    public function logout(Request $request)
    {
         //Remove customer token
         if ($request->user()) { 
            $request->user()->tokens()->delete();
         }
         
        //Return response json
        return response()->json(['message' => 'Logout success']);
    }
}
<?php
namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Auth;
use Validator;
use App\Models\User;

/**
 * AdminController handles all operations related to admin user
 *
 */
class AdminController extends Controller
{
    
    /**
     * Register admin user
     *
     * @param  object  $request
     * @return json response
     */
    public function register(Request $request)
    {
        //Define validation rules
        $validator = Validator::make($request->all(),[
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8'
        ]);

        //Validating request
        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        //Create user entry
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
         ]);

        //Generate token
        $token = $user->createToken('auth_token')->plainTextToken;

        //Return response with token
        return response()->json(['data' => $user,'access_token' => $token, 'token_type' => 'Bearer']);
    }

     /**
     * Login admin user
     *
     * @param  object  $request
     * @return json response
     */
    public function login(Request $request)
    {
        //Checking login credentials
        if (!Auth::attempt($request->only('email', 'password')))
        {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        //Get user instance by email
        $user = User::where('email', $request['email'])->firstOrFail();

        //Generate new token
        $token = $user->createToken('auth_token')->plainTextToken;

        //Return response with token
        return response()->json(['message' => 'Login success','access_token' => $token, 'token_type' => 'Bearer']);
    }

    
    /**
     * Destroy admin user token
     *
     * @return json response
     */
    public function logout(Request $request)
    {
        //Remove admin token
        if ($request->user()) { 
            $request->user()->tokens()->delete();
        }

        //Return response json
        return response()->json(['message' => 'Logout success']);
    }
}
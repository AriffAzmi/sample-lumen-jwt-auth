<?php

namespace App\Http\Controllers;

use Validator;
use App\User;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Firebase\JWT\ExpiredException;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Routing\Controller as BaseController;

class UserController extends BaseController 
{
    /**
     * The request instance.
     *
     * @var \Illuminate\Http\Request
     */
    private $request;

    /**
     * Create a new controller instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function __construct(Request $request) {
        $this->request = $request;
    }

    /**
     * Create a new token.
     * 
     * @param  \App\User   $user
     * @return string
     */
    protected function jwt(User $user) {

        $payload = [
            'iss' => "lumen-jwt", // Issuer of the token
            'sub' => $user->id, // Subject of the token
            'iat' => time(), // Time when JWT was issued. 
            'exp' => time() + 60*60 // Expiration time
        ];
        
        // As you can see we are passing `JWT_SECRET` as the second parameter that will 
        // be used to decode the token in the future.
        return JWT::encode($payload, env('JWT_SECRET'));
    } 

    /**
     * Authenticate a user and return the token if the provided credentials are correct.
     * 
     * @param  \App\User   $user 
     * @return mixed
     */
    public function authenticate(User $user) {

        $validator = Validator::make($this->request->all(), [
            'username'     => 'required',
            'password'  => 'required'
        ]);
        
        if ($validator->fails()) { 
            
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 412);        
        }

        // Find the user by email
        $user = User::where('username', $this->request->email)->first();

        if (!$user) {
            
            return response()->json([
                'status' => false,
                'message' => 'Email does not exist',
                'errors' => ''
            ], 404);
        }

        // Verify the password and generate the token
        if (Hash::check($this->request->password, $user->password)) {
            
            return response()->json([
                'status' => true,
                'message' => 'User authentication success',
                'data' => [
                    'token' => $this->jwt($user)
                ],
                'errors' => ''
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Email or password is wrong.',
            'errors' => ''
        ], 400);
    }

    public function register(Request $request)
    {
       $validator = Validator::make($request->all(), [
            'username'     => 'required|unique:users',
            'password'  => 'required'
        ]);
        
        if ($validator->fails()) { 
            
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 412);    
        }

        try {
            
            $user = new User();
            $user->username = $request->username;
            $user->password = Hash::make($request->password);
            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'User registration success',
                'data' => [
                    'token' => $this->jwt($user)
                ],
                'errors' => ''
            ], 200);

        } catch (Exception $e) {
            
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'errors' => $e
            ], 500);
        }
    }

    public function me(Request $request)
    {
        return response()->json([
            'status' => true,
            'message' => '',
            'data' => $request->auth,
            'errors' => ''
        ], 200);
    }
}
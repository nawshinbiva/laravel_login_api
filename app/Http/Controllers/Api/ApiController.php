<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\MyTestMail;
use Exception;
use Illuminate\Support\Facades\Log;

class ApiController extends Controller
{
    //POST [name, email, password]
    public function register(Request $request)
    {
        // validations
        $request->validate([
            "name" => "required|string",
            "email" => "required|string|email|unique:users",
            "password" => "required|confirmed"
        ]);

        //create user
        User::create([
            "name" => $request->name,
            "email" => $request->email,
            "password" => bcrypt($request->password)
        ]);

        return response()->json([
            "status" => true,
            "message" => "User registered successfully!",
            "data" => []
        ]);
    }
    //POST [email, password]
    public function login(Request $request)
    {
        //validation
        $request->validate([
            "email" => "required|email|string",
            "password" => "required"
        ]);

        //email check
        $user = User::where("email", $request->email)->first();


        //password
        if (!empty($user)) {
            //user exists
            if (Hash::check($request->password, $user->password)) {
                // password matched
                //auth token generation
                $token = $user->createToken("mytoken")->accessToken;

                return response()->json([
                    'status' => true,
                    "message" => "Login Successful!",
                    "token" => $token,
                    "data" => []
                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "Password didn't match!",
                    "data" => []
                ]);
            }
        } else {
            return response()->json([
                "status" => false,
                "message" => "Invalid Email value!",
                "data" => []
            ]);
        }
    }
    //GET [Auth: Token]
    public function profile()
    {
        $userData = auth()->user();

        return response()-> json([
            "status"=> true,
            "message"=> "Profile Information",
            "data"=> $userData,
            "id"=>auth()->user()->id

        ]);
    }
    //GET [Auth Token]
    public function logout()
    {
        $token = auth()->user()->token();
        $token->revoke();

        return response()->json([
            "status"=> true,
            "message"=> "User logged out!"
        ]);

    }
     // POST [email]
     public function forgotPassword(Request $request)
     {
         $request->validate([
             "email" => "required|email|string",
         ]);
 
         
            $user = User::where("email", $request->email)->first();

            if (!$user) {
                return response()->json([
                    "status" => false,
                    "message" => "Email not found!",
                    "data" => []
                ]);
            }

            $token = Str::random(4);

            DB::table('password_reset_tokens')->insert([
                'email' => $request->email,
                'token' => $token,
                'created_at' => Carbon::now()
            ]);

            $resetUrl = url('/reset-password?token=' . $token . '&email=' . urlencode($request->email));

            $details =  $resetUrl;
            try {
            Mail::to($request->email)->send(new MyTestMail($details));
            return response()->json([
                'status' => true,
                'message' => 'Password reset email sent!',
                'data' => []
            ]);
            } catch (Exception $e) {
                Log::info($e->getMessage());
                // Log the exception or handle it as per your application's needs
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to send password reset email.',
                    'error' => $e->getMessage(), 
                    'data' => []
                ], 500); 
            }
     }
 
     // POST [email, token, new_password, new_password_confirmation]
     public function resetPassword(Request $request)
     {
         $request->validate([
             'email' => 'required|email|string',
             'token' => 'required|string',
             'password' => 'required|confirmed'
         ]);
 
         $passwordReset = DB::table('password_reset_tokens')->where([
             ['token', $request->token],
             ['email', $request->email]
         ])->first();
 
         if (!$passwordReset) {
             return response()->json([
                 'status' => false,
                 'message' => 'Invalid token!',
                 'data' => []
             ],400);
         }
 
         $user = User::where('email', $request->email)->first();
 
         if (!$user) {
             return response()->json([
                 'status' => false,
                 'message' => 'User not found!',
                 'data' => []
             ]);
         }
 
         $user->password = Hash::make($request->password);
         $user->save();
 
         DB::table('password_reset_tokens')->where(['email' => $request->email])->delete();
 
         return response()->json([
             'status' => true,
             'message' => 'Password has been reset!',
             'data' => []
         ]);
     }
}

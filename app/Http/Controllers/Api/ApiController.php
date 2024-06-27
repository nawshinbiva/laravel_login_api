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


        //auth token


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
    //POST [email]
    public function forgotPassword(Request $request){
        //validate
        $request->validate([
            "email"=>"required|email|string",
        ]);

        //find the user by email
        $user = User::where("email", $request->email)->first();

        if(!$user){
            return response()->json([
                "status"=> false,
                "message"=> "Email not found!",
                "data"=> []
            ]);
        }

        //generate a token
        $token = Str::random(60);

        //store the token in the password_reset table
        DB::table('password_resets')->inset([
            'email'=>$request->email,
            'token'=>$token,
            'created_at'=>Carbon::now()
        ]);
        
        //sending the reset email
        Mail::send('email.passwordReset',['token'=>$token], function($message) use ($request){
            $message->to($request->email);
            $message->subject('Password Reset Request');
        });

        return response()->json([
            'status'=> true,
            'message'=> 'Password resent email sent!',
            'data'=> []
        ]);

    }
    //POST [email, token, new_password, new_password_confirmation]
    public function resetPassword(Request $request){
        //validation
        $request->validate([
            'email'=>'required|email|string',
            'token'=>'required|string',
            'password'=> 'required|confirmend'
        ]);
        //find the token in the password_resets table
        $passwordReset = DB::table('password_resets')->where([
            ['token',$request->token],
            ['email',$request->email]
        ])->first();

        if(!$passwordReset){
            return response()->json([
                'status'=> false,
                'message'=> 'Invalid token!',
                'data'=> []
            ]);
        }

        //find the user by email
        $user = User::where('email',$request->email)->first();

        if(!$user){
            return response()->json([
                'status'=> false,
                'message'=> 'User not found!',
                'data'=> []
            ]);
        }

        //update the user's password
        $user->password= bcrypt($request->password);
        $user->save();

        //delete token
        DB::table('password_resets')->where(['email'=>$request->email])->delete();
        
        return response()->json([
            'status'=> true,
            'message'=> 'Password has been reset!',
            'data'=> []
        ]);

    }
}

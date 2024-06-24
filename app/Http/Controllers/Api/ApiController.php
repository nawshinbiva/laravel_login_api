<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

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
    //GET [Auth Token]
    public function profile()
    {
    }
    //GET [Auth Token]
    public function logout()
    {
    }
}

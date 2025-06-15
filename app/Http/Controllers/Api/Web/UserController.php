<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function login(LoginRequest $request){
        try{
            $validated = $request->validated();
            $user = User::where('phone_number',$validated['phone_number'])->first();

            if(!$user || !Hash::check($validated['password'],$user->password)){
                return response()->json([
                    'message' => 'Phone number or password is incorrect.'
                ],401);
            }

            $token = $user->createToken($user->role .'-token')->plainTextToken;

            return response()->json([
                'message' => 'Login successful',
                'token' => $token
            ],200);
        }catch(\Exception $e){
            return response()->json([
                'message' => $e->getMessage(),
            ],500);
        }
    }

    public function logout(Request $request){
        try{
            //$request->user()->tokens()->delete(); for all the devices
            $request->user()->currentAccessToken()->delete(); //only for the current device
            return response()->json(['message' => 'Successfully logged out']);
        }catch(\Exception $e){
            return response()->json([
                'message' => $e->getMessage()
            ],500);
        }
    }
}

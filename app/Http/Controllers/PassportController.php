<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PassportController extends Controller
{
    public function register(RegisterRequest $request) {

        try {

            $user = User::create($request->only(['email','password','username','pin','name']));

            $token = $user->createToken('FlipApp')->accessToken;

            return response()->json(['status' => true,'message' => 'User registered','_token' => $token]);
        } catch (\Exception $exception) {
            report($exception);
            return response()->json(['status' => false,'message' => $exception->getMessage()]);
        }
    }

    public function login(LoginRequest $request) {

        if (Auth::attempt($request->only(['email','password']))) {

            $token = Auth::user()->createToken('FlipApp')->accessToken;
            return response()->json(['status' => true,'user' => Auth::user(),'_token' => $token]);

        }
    }
}

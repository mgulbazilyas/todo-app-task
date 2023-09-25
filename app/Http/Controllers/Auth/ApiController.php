<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class ApiController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:255|unique:' . User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);
        $email = $request->email;
        $user = User::create([
            'name' => explode(",", $email)[0],
            'email' => $email,
            'password' => Hash::make($request->password),
        ]);
        event(new Registered($user));
        return response()->json(["status" => "success", "message" => "Please check your email for verification"], 201);
    }

    public function login(LoginRequest $request)
    {

        $request->authenticate();
        $user = Auth::user();
        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'candidate' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request){
        $request->user()->tokens()->where('id', $request->user()->currentAccessToken()->id)->delete();

    return response()->json(['message' => 'Logged out successfully', 'status' => 'success']);
    }

}

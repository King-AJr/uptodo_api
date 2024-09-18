<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        Log::info('request info', $request->all());
        $validator = Validator::make($request->all(), [
            'name'      => 'required|string|max:255|unique:users',
            'password'  => 'required|string|min:8'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();

            $formattedErrors = implode(', ', $errors);

            return response()->json([
                'success' => false,
                'error' => 'Validation Error',
                'message' => $formattedErrors
            ], 422);
        }

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password)
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'data'          => $user,
            'token'  => $token
        ]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string',
            'password'  => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::where('email', $request->email)->first();

    if ($user && is_null($user->password)) {

        Password::sendResetLink(['email' => $request->email]);
        return response()->json([
            'success' => false,
            'message' => 'It looks like you signed up using a social media account. Please log in using that method, or set a password via the link we have sent to your email.'
        ]);
    }

        // Attempt to authenticate the user using the provided credentials
        if (!Auth::attempt($request->only('name', 'password'))) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid login credentials',
                'message' => 'Please use a valid username and password'
            ], 401);
        }

        // Retrieve the authenticated user
        $user = Auth::user();

        // Generate a new token for the user
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success'       => true,
            'token'  => $token,
            'data' => $user

        ]);
    }
}

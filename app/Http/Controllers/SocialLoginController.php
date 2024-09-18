<?php

namespace App\Http\Controllers;

use App\Models\GoogleUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SocialLoginController extends Controller
{
    public function handleGoogleSignIn(Request $request)
    {
        Log::info('info gotten ', $request->all());
        $request->validate([
            'id' => 'required|string',
            'email' => 'required|email',
            'name' => 'required|string',
            'photoUrl' => 'nullable|string',
        ]);

        // Attempt to find a GoogleUser entry
        $googleUser = GoogleUser::where('google_id', $request->id)->first();

        if ($googleUser) {
            // Log in the existing user
            $user = $googleUser->user;
            Auth::login($user);
        } else {
            // Check if a user with the provided email already exists
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                // If no user with the email exists, check for a unique name
                $originalName = $request->name;
                $uniqueName = $originalName;
                $counter = 1;

                while (User::where('name', $uniqueName)->exists()) {
                    // Append a random two-digit number to the name
                    $uniqueName = $originalName . $counter++;
                    if ($counter > 99) {
                        // Avoid infinite loop if somehow name conflicts persist
                        throw new \Exception('Unable to generate a unique name.');
                    }
                }

                
                Log::info('user', $user);
                // Create a GoogleUser entry
                GoogleUser::create([
                    'google_id' => $request->id,
                    'email' => $request->email,
                    'avatar' => $request->photoUrl,
                    'user_id' => $user->id
                ]);


                $googleUser->save();

                // Log in the new user
                Auth::login($user);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success'       => true,
                'token'  => $token,
                'data' => $user

            ]);
        }
    }
}

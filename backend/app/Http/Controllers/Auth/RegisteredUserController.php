<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Http\JsonResponse;


class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => ['required', 'string', 'unique:users,phone'],
            'role' => ['required', Rule::in(['member', 'coach'])], // فقط السماح بهذان الدوران
            'gender' => ['required', Rule::in(['male', 'female'])],
            'birth_date' => ['nullable', 'date'],
            'profile_image' => ['nullable', 'string'],

        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role' => $request->role,
            'gender' => $request->gender,
            'birth_date' => $request->birth_date,
            'profile_image' => $request->profile_image,
        ]);

        event(new Registered($user));

        Auth::login($user);
$token = $user->createToken('API Token')->plainTextToken;
return response()->json([
    'message' => 'Registered successfully',
    'token' => $token,
    'user' => $user,
]);
    }
}

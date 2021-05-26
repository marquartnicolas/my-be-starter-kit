<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
  public function register(Request $request)
  {
    $fields = $request->validate([
      'name' => 'required|string',
      'email' => 'required|string|unique:users',
      'password' => 'required|string|confirmed',
    ]);

    $user = new User($fields);
    $user->password = bcrypt($user->password);
    $user->save();

    return response()->json(
      [
        'user' => $user,
        'token' => $user->createToken('appToken')->plainTextToken,
      ],
      201
    );
  }

  public function login(Request $request)
  {
    $fields = $request->validate([
      'email' => 'required|string',
      'password' => 'required|string',
    ]);

    $user = User::where('email', $fields['email'])->first();

    if (!$user || !Hash::check($fields['password'], $user->password)) {
      return response()->json(
        [
          'message' => 'Bad Credentials',
        ],
        401
      );
    }

    return response()->json(
      [
        'user' => $user,
        'token' => $user->createToken('appToken')->plainTextToken,
      ],
      200
    );
  }

  public function logout(Request $request)
  {
    $request
      ->user()
      ->currentAccessToken()
      ->delete();
    return response()->noContent();
  }
}

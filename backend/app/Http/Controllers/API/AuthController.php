<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    // Prijava za administratora.
    public function login(Request $request)
    {
        $data = [
            'username' => $request->username,
            'password' => $request->password
        ];
  
        if(auth()->attempt($data)) {
            $token = auth()->user()->createToken(config('app.name'))->accessToken;
            return response()->json(['status' => 1, 'token' => $token], 200);
        } else {
            return response()->json(['message' => 'Pogrešno korisničko ime ili lozinka.'], 400);
        }
    }
}

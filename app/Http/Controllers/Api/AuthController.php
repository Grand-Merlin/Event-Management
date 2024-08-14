<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // on valide les données
        $request->validate([
            'email'=>'required|email',
            'password'=>'required'
        ]);

        $user = User::where('email', $request->email)->first();

        //si l'email ne correspont pas a l'email dans la db
        if(!$user){
            throw ValidationException::withMessages([
                'email' => ['Les informations sont incorrectes']
            ]);
        }

        // si l'utilisateur existe
        // on verifie si le mdp correspond
        if(!Hash::check($request->password, $user->password)){
            throw ValidationException::withMessages([
                'email' => ['Les informations sont incorrectes']
            ]);
        }

        // on genere le jeton API
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token
        ]);
    }

     public function logout(Request $request)
     {
        $request->user()->tokens()->delete();
        return response()->json([
            'message'=>'Deconnexion réussie'
        ]);
     }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ChangePasswordRequest;
use App\Http\Requests\Api\LoginApiRequest;
use App\Http\Resources\EleveResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Connexion du parent : retourne un jeton Sanctum à utiliser pour
    // toutes les requêtes suivantes (en-tête Authorization: Bearer <token>).
    public function login(LoginApiRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email ou mot de passe incorrect.',
            ], 401);
        }

        if (! $user->is_active) {
            return response()->json([
                'message' => 'Ce compte a été désactivé.',
            ], 403);
        }

        if (! $user->isParent()) {
            return response()->json([
                'message' => "Cette application est réservée aux parents d'élèves.",
            ], 403);
        }

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'token'  => $token,
            'parent' => [
                'id'    => $user->id,
                'nom'   => $user->name,
                'email' => $user->email,
            ],
            'enfants' => EleveResource::collection($user->enfants()->with('classe')->get()),
        ]);
    }

    // Déconnexion : révoque uniquement le jeton utilisé pour cette requête
    // (les autres appareils éventuellement connectés restent valides).
    public function logout()
    {
        Auth::user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnexion réussie.']);
    }

    // Modification du mot de passe depuis l'application
    public function changePassword(ChangePasswordRequest $request)
    {
        $user = Auth::user();

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Le mot de passe actuel est incorrect.',
                'errors'  => ['current_password' => ['Le mot de passe actuel est incorrect.']],
            ], 422);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return response()->json(['message' => 'Mot de passe modifié avec succès.']);
    }
}

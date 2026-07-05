<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateMyPasswordRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    // Formulaire de modification de mon propre mot de passe
    public function editPassword()
    {
        return view('profile.password');
    }

    // Enregistrer le nouveau mot de passe
    public function updatePassword(UpdateMyPasswordRequest $request)
    {
        $user = Auth::user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()
                ->withErrors(['current_password' => 'Le mot de passe actuel est incorrect.'])
                ->onlyInput('current_password');
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Votre mot de passe a été modifié avec succès.');
    }
}

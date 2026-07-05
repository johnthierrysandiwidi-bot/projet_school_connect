<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class PasswordResetController extends Controller
{
    // Formulaire "mot de passe oublié" (saisie de l'email)
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    // Envoie le lien de réinitialisation par email, si le compte existe.
    public function sendResetLink(ForgotPasswordRequest $request)
    {
        Password::sendResetLink($request->only('email'));

        // Toujours le même message, qu'un compte existe pour cet email ou
        // non : on ne révèle jamais si une adresse est inscrite ou pas.
        return back()->with(
            'success',
            "Si un compte existe pour cette adresse, un lien de réinitialisation vient d'être envoyé."
        );
    }

    // Formulaire de saisie du nouveau mot de passe, depuis le lien reçu par email.
    public function showResetForm(string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => request('email'),
        ]);
    }

    // Vérifie le jeton et enregistre le nouveau mot de passe.
    public function reset(ResetPasswordRequest $request)
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->update(['password' => Hash::make($password)]);
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')
                ->with('success', 'Votre mot de passe a été réinitialisé. Vous pouvez vous connecter.');
        }

        return back()->withErrors(['email' => $this->messageErreur($status)]);
    }

    private function messageErreur(string $status): string
    {
        return match ($status) {
            Password::INVALID_TOKEN => 'Ce lien de réinitialisation est invalide ou a expiré. Demandez-en un nouveau.',
            Password::INVALID_USER  => "Aucun compte ne correspond à cette adresse email.",
            default                  => 'Impossible de réinitialiser le mot de passe. Réessayez.',
        };
    }
}

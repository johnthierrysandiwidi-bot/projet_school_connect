<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            if (!Auth::user()->is_active) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Ce compte a été désactivé.'
                ]);
            }

            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'email' => 'Email ou mot de passe incorrect.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function dashboard(Request $request)
    {
        $user = Auth::user();

        if ($user->isGestionnaire()) {
            return app(DashboardController::class)->index($request);
        }

        $annee = config('app.annee_scolaire');
        $trimestre = $request->trimestre ?? 1;
        $classe = $user->classe;

        $nbEleves = $nbMatieres = $nbNotes = 0;
        $moyenneClasse = null;
        $eleves = collect();

        if ($classe) {
            $eleves = \App\Models\Eleve::where('classe_id', $classe->id)
                                       ->where('annee_scolaire', $annee)
                                       ->where('statut', 'actif')
                                       ->orderBy('nom')
                                       ->get();

            $nbEleves = $eleves->count();

            $nbMatieres = \App\Models\Matiere::where('classe_id', $classe->id)
                                              ->where('is_active', true)
                                              ->count();

            $nbNotes = \App\Models\Note::whereIn('eleve_id', $eleves->pluck('id'))
                                       ->where('user_id', $user->id)
                                       ->count();

            $moyennes = $eleves
                ->map(fn($eleve) => \App\Services\MoyenneService::moyenneEleve($eleve, $trimestre, $annee))
                ->filter(fn($m) => $m !== null);

            $moyenneClasse = $moyennes->isNotEmpty() ? round($moyennes->avg(), 2) : null;
        }

        return view('teacher.dashboard', compact(
            'classe', 'nbEleves', 'nbMatieres', 'nbNotes',
            'moyenneClasse', 'eleves', 'annee', 'trimestre'
        ));
    }
}
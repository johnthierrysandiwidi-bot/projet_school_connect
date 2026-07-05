@extends('layouts.guest')

@section('title', 'Connexion')

@section('content')
    <div class="guest-logo">🏫</div>
    <div class="guest-title">{{ config('app.nom_ecole') }}</div>
    <div class="guest-subtitle">Suivi Scolaire — Connectez-vous pour accéder à l'application</div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('login') }}" method="POST">
        @csrf

        <div class="form-group">
            <label>Adresse email</label>
            <input type="email" name="email" value="{{ old('email') }}"
                   placeholder="votre@email.com" required autofocus>
        </div>

        <div class="form-group">
            <label>Mot de passe</label>
            <input type="password" name="password" placeholder="••••••••" required>
        </div>

        <div class="checkbox-row form-group" style="justify-content:space-between; display:flex; align-items:center;">
            <span style="display:flex; align-items:center; gap:6px;">
                <input type="checkbox" name="remember" id="remember">
                <label for="remember" style="margin:0">Se souvenir de moi</label>
            </span>
            <a href="{{ route('password.request') }}" style="font-size:13px">Mot de passe oublié ?</a>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Se connecter</button>
    </form>

    <div class="form-hint" style="margin-top:22px; padding:14px; background:#f8fafc; border-radius:8px;">
        <strong style="color:#374151">Compte de démonstration :</strong><br>
        👨‍💼 admin@ecole.bf — Gestionnaire
    </div>
@endsection

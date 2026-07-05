@extends('layouts.guest')

@section('title', 'Mot de passe oublié')

@section('content')
    <div class="guest-logo">🔑</div>
    <div class="guest-title">Mot de passe oublié</div>
    <div class="guest-subtitle">Indiquez votre email pour recevoir un lien de réinitialisation</div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('password.email') }}" method="POST">
        @csrf

        <div class="form-group">
            <label>Adresse email</label>
            <input type="email" name="email" value="{{ old('email') }}"
                   placeholder="votre@email.com" required autofocus>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Envoyer le lien de réinitialisation</button>
    </form>

    <div class="form-hint" style="margin-top:18px; text-align:center;">
        <a href="{{ route('login') }}">← Retour à la connexion</a>
    </div>
@endsection

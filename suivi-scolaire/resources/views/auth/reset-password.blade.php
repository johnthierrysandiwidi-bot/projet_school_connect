@extends('layouts.guest')

@section('title', 'Réinitialiser le mot de passe')

@section('content')
    <div class="guest-logo">🔑</div>
    <div class="guest-title">Nouveau mot de passe</div>
    <div class="guest-subtitle">Choisissez un nouveau mot de passe pour votre compte</div>

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('password.update') }}" method="POST">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="form-group">
            <label>Adresse email</label>
            <input type="email" name="email" value="{{ old('email', $email) }}"
                   placeholder="votre@email.com" required autofocus>
        </div>

        <div class="form-group">
            <label>Nouveau mot de passe</label>
            <input type="password" name="password" placeholder="••••••••" required>
            <div class="form-hint">Au moins 6 caractères.</div>
        </div>

        <div class="form-group">
            <label>Confirmer le nouveau mot de passe</label>
            <input type="password" name="password_confirmation" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Réinitialiser le mot de passe</button>
    </form>
@endsection

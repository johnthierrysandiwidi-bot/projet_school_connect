@extends('layouts.app')

@section('title', 'Mon mot de passe')
@section('page-title', 'Changer mon mot de passe')

@section('content')

    <form action="{{ route('profile.password.update') }}" method="POST" style="max-width:480px">
    @csrf
    @method('PUT')

        <div class="card">
            <div class="card-header">🔑 Modifier mon mot de passe</div>
            <div class="card-body">

                <div class="form-group">
                    <label>Mot de passe actuel *</label>
                    <input type="password" name="current_password" placeholder="••••••••">
                    @error('current_password')<div class="error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label>Nouveau mot de passe * <span class="opt">(minimum 6 caractères)</span></label>
                    <input type="password" name="password" placeholder="••••••••">
                    @error('password')<div class="error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label>Confirmer le nouveau mot de passe *</label>
                    <input type="password" name="password_confirmation" placeholder="••••••••">
                </div>

            </div>
        </div>

        <div class="form-actions" style="border-top:none; padding-top:0;">
            <button type="submit" class="btn btn-primary">✅ Enregistrer</button>
        </div>

    </form>

@endsection

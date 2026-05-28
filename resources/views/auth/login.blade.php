@extends('installer.layout')
@section('title', 'Login')

@section('content')
    <h2 class="card-title">Sign In to EdgeMail</h2>
    <p class="card-desc">Enter your credentials to access your email.</p>

    @if($errors->any())
        <div class="alert alert-danger">⚠️ {{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('login.attempt') }}">
        @csrf
        <div class="form-group">
            <label class="form-label">Email Address</label>
            <input type="email" class="form-input" name="email" value="{{ old('email') }}" required autofocus>
        </div>
        <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" class="form-input" name="password" required>
        </div>
        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
            <input type="checkbox" name="remember" id="remember" style="accent-color: var(--accent);">
            <label for="remember" style="font-size: 0.85rem; color: var(--text-secondary);">Remember me</label>
        </div>
        <button type="submit" class="btn btn-primary" style="width: 100%;">Sign In</button>
    </form>
@endsection

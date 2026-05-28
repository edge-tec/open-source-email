@extends('installer.layout')
@section('title', 'Two-Factor Authentication')

@section('content')
    <h2 class="card-title">Two-Factor Authentication</h2>
    <p class="card-desc">Enter the 6-digit code from your authenticator app.</p>

    @if($errors->any())
        <div class="alert alert-danger">⚠️ {{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('two-factor.verify.submit') }}">
        @csrf
        <div class="form-group">
            <label class="form-label">Verification Code</label>
            <input type="text" class="form-input" name="code" maxlength="6" pattern="[0-9]{6}" placeholder="000000" required autofocus style="text-align: center; font-size: 1.5rem; letter-spacing: 0.5rem;">
        </div>
        <button type="submit" class="btn btn-primary" style="width: 100%;">Verify</button>
        <p style="text-align: center; margin-top: 1rem; font-size: 0.8rem; color: var(--text-muted);">
            You can also enter a recovery code.
        </p>
    </form>
@endsection

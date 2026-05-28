@extends('installer.layout')
@section('title', 'Webmail Login')

@section('content')
    <h2 class="card-title">EdgeMail Webmail</h2>
    <p class="card-desc">Log in to access your inbox and send emails.</p>

    @if($errors->any())
        <div class="alert alert-danger">⚠️ {{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('webmail.login.attempt') }}">
        @csrf
        <div class="form-group">
            <label class="form-label">Email Address</label>
            <input type="email" class="form-input" name="email" value="{{ old('email') }}" placeholder="you@yourdomain.com" required autofocus>
        </div>
        <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" class="form-input" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary" style="width: 100%;">Log In</button>
    </form>
@endsection

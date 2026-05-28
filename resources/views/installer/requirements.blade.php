@extends('installer.layout')
@section('title', 'Requirements')

@section('steps')
<div class="steps">
    <div class="step completed"><span class="step-num">✓</span><span class="step-label">Welcome</span></div>
    <div class="step-line completed"></div>
    <div class="step active"><span class="step-num">2</span><span class="step-label">Requirements</span></div>
    <div class="step-line"></div>
    <div class="step"><span class="step-num">3</span><span class="step-label">Permissions</span></div>
    <div class="step-line"></div>
    <div class="step"><span class="step-num">4</span><span class="step-label">Database</span></div>
    <div class="step-line"></div>
    <div class="step"><span class="step-num">5</span><span class="step-label">Mail</span></div>
    <div class="step-line"></div>
    <div class="step"><span class="step-num">6</span><span class="step-label">Admin</span></div>
    <div class="step-line"></div>
    <div class="step"><span class="step-num">7</span><span class="step-label">Install</span></div>
</div>
@endsection

@section('content')
    <h2 class="card-title">Server Requirements</h2>
    <p class="card-desc">Checking that your server meets all requirements for EdgeMail.</p>

    {{-- PHP Version --}}
    <h3 style="font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.05em; margin-bottom: 0.75rem;">PHP Version</h3>
    <ul class="check-list" style="margin-bottom: 1.5rem;">
        <li class="check-item">
            <span class="check-icon {{ $checks['php']['satisfied'] ? 'check-pass' : 'check-fail' }}">
                {{ $checks['php']['satisfied'] ? '✓' : '✗' }}
            </span>
            <span class="check-label">{{ $checks['php']['name'] }}</span>
            <span class="check-value">{{ $checks['php']['current'] }} (requires {{ $checks['php']['required'] }})</span>
        </li>
    </ul>

    {{-- PHP Extensions --}}
    <h3 style="font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.05em; margin-bottom: 0.75rem;">PHP Extensions</h3>
    <ul class="check-list" style="margin-bottom: 1.5rem;">
        @foreach($checks['extensions'] as $ext)
        <li class="check-item">
            <span class="check-icon {{ $ext['loaded'] ? 'check-pass' : ($ext['critical'] ? 'check-fail' : 'check-warn') }}">
                {{ $ext['loaded'] ? '✓' : '✗' }}
            </span>
            <span class="check-label">{{ $ext['name'] }}</span>
            <span class="check-value">{{ $ext['loaded'] ? 'Loaded' : ($ext['critical'] ? 'Required' : 'Optional') }}</span>
        </li>
        @endforeach
    </ul>

    {{-- Server Requirements --}}
    <h3 style="font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.05em; margin-bottom: 0.75rem;">Server Settings</h3>
    <ul class="check-list">
        @foreach($checks['server'] as $req)
        <li class="check-item">
            <span class="check-icon {{ $req['satisfied'] ? 'check-pass' : 'check-warn' }}">
                {{ $req['satisfied'] ? '✓' : '!' }}
            </span>
            <span class="check-label">{{ $req['name'] }}</span>
            <span class="check-value">{{ $req['current'] }} (min: {{ $req['required'] }})</span>
        </li>
        @endforeach
    </ul>

    @if(!$allMet)
    <div class="alert alert-danger" style="margin-top: 1rem;">
        ⚠️ Some critical requirements are not met. Please install the missing PHP extensions before continuing.
    </div>
    @endif

    <div class="btn-row">
        <a href="{{ route('installer.welcome') }}" class="btn btn-secondary">← Back</a>
        <a href="{{ route('installer.permissions') }}" class="btn btn-primary {{ !$allMet ? 'disabled' : '' }}" @if(!$allMet) onclick="return false;" @endif>
            Continue →
        </a>
    </div>
@endsection

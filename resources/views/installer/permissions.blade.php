@extends('installer.layout')
@section('title', 'Permissions')

@section('steps')
<div class="steps">
    <div class="step completed"><span class="step-num">✓</span><span class="step-label">Welcome</span></div>
    <div class="step-line completed"></div>
    <div class="step completed"><span class="step-num">✓</span><span class="step-label">Requirements</span></div>
    <div class="step-line completed"></div>
    <div class="step active"><span class="step-num">3</span><span class="step-label">Permissions</span></div>
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
    <h2 class="card-title">Directory Permissions</h2>
    <p class="card-desc">The following directories need to be writable by the web server.</p>

    <ul class="check-list" id="permissionList">
        @foreach($permissions as $perm)
        <li class="check-item">
            <span class="check-icon {{ $perm['writable'] ? 'check-pass' : 'check-fail' }}">
                {{ $perm['writable'] ? '✓' : '✗' }}
            </span>
            <span class="check-label"><code style="font-size: 0.85rem;">{{ $perm['path'] }}</code></span>
            <span class="check-value">{{ $perm['writable'] ? '0775 ✓' : 'Not writable' }}</span>
        </li>
        @endforeach
    </ul>

    @if(!$allWritable)
    <div style="margin-top: 1rem;">
        <button class="btn btn-success" id="fixBtn" onclick="fixPermissions()">
            🔧 Auto-Fix Permissions
        </button>
    </div>
    @endif

    <div class="btn-row">
        <a href="{{ route('installer.requirements') }}" class="btn btn-secondary">← Back</a>
        <a href="{{ route('installer.database') }}" class="btn btn-primary" id="continueBtn">
            Continue →
        </a>
    </div>
@endsection

@section('scripts')
<script>
async function fixPermissions() {
    const btn = document.getElementById('fixBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Fixing...';

    try {
        const result = await apiPost('{{ route("installer.fix-permissions") }}', {});
        if (result.success) {
            location.reload();
        }
    } catch (err) {
        btn.innerHTML = '❌ Fix Failed - Try manually';
    }
}
</script>
@endsection

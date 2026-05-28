@extends('installer.layout')
@section('title', 'Admin Account')

@section('steps')
<div class="steps">
    <div class="step completed"><span class="step-num">✓</span></div>
    <div class="step-line completed"></div>
    <div class="step completed"><span class="step-num">✓</span></div>
    <div class="step-line completed"></div>
    <div class="step completed"><span class="step-num">✓</span></div>
    <div class="step-line completed"></div>
    <div class="step completed"><span class="step-num">✓</span></div>
    <div class="step-line completed"></div>
    <div class="step completed"><span class="step-num">✓</span></div>
    <div class="step-line completed"></div>
    <div class="step active"><span class="step-num">6</span><span class="step-label">Admin</span></div>
    <div class="step-line"></div>
    <div class="step"><span class="step-num">7</span><span class="step-label">Install</span></div>
</div>
@endsection

@section('content')
    <h2 class="card-title">Admin Account</h2>
    <p class="card-desc">Create the super administrator account for managing EdgeMail.</p>

    <form id="adminForm">
        <div class="form-group">
            <label class="form-label">Admin Name</label>
            <input type="text" class="form-input" name="admin_name" id="admin_name" placeholder="Super Admin" required>
        </div>

        <div class="form-group">
            <label class="form-label">Admin Email</label>
            <input type="email" class="form-input" name="admin_email" id="admin_email" placeholder="admin@example.com" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" class="form-input" name="admin_password" id="admin_password" placeholder="Min 8 characters" minlength="8" required>
            </div>
            <div class="form-group">
                <label class="form-label">Confirm Password</label>
                <input type="password" class="form-input" name="admin_password_confirm" id="admin_password_confirm" placeholder="Repeat password" required>
            </div>
        </div>

        <div id="passwordAlert" style="display: none;"></div>

        <div class="btn-row">
            <a href="{{ route('installer.mail-config') }}" class="btn btn-secondary">← Back</a>
            <button type="submit" class="btn btn-primary" id="installBtn">Begin Installation →</button>
        </div>
    </form>
@endsection

@section('scripts')
<script>
document.getElementById('adminForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const password = document.getElementById('admin_password').value;
    const confirm = document.getElementById('admin_password_confirm').value;
    const alert = document.getElementById('passwordAlert');

    if (password !== confirm) {
        alert.className = 'alert alert-danger';
        alert.innerHTML = '✗ Passwords do not match';
        alert.style.display = 'flex';
        return;
    }

    if (password.length < 8) {
        alert.className = 'alert alert-danger';
        alert.innerHTML = '✗ Password must be at least 8 characters';
        alert.style.display = 'flex';
        return;
    }

    // Merge all config
    const dbConfig = JSON.parse(sessionStorage.getItem('edgemail_db') || '{}');
    const mailConfig = JSON.parse(sessionStorage.getItem('edgemail_config') || '{}');
    const adminConfig = {
        admin_name: document.getElementById('admin_name').value,
        admin_email: document.getElementById('admin_email').value,
        admin_password: password,
    };

    const config = {...dbConfig, ...mailConfig, ...adminConfig};
    sessionStorage.setItem('edgemail_full_config', JSON.stringify(config));

    // Navigate to finalize
    window.location.href = '{{ route("installer.finalize") }}';
});
</script>
@endsection

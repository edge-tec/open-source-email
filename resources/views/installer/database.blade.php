@extends('installer.layout')
@section('title', 'Database')

@section('steps')
<div class="steps">
    <div class="step completed"><span class="step-num">✓</span><span class="step-label">Welcome</span></div>
    <div class="step-line completed"></div>
    <div class="step completed"><span class="step-num">✓</span><span class="step-label">Requirements</span></div>
    <div class="step-line completed"></div>
    <div class="step completed"><span class="step-num">✓</span><span class="step-label">Permissions</span></div>
    <div class="step-line completed"></div>
    <div class="step active"><span class="step-num">4</span><span class="step-label">Database</span></div>
    <div class="step-line"></div>
    <div class="step"><span class="step-num">5</span><span class="step-label">Mail</span></div>
    <div class="step-line"></div>
    <div class="step"><span class="step-num">6</span><span class="step-label">Admin</span></div>
    <div class="step-line"></div>
    <div class="step"><span class="step-num">7</span><span class="step-label">Install</span></div>
</div>
@endsection

@section('content')
    <h2 class="card-title">Database Configuration</h2>
    <p class="card-desc">Enter your MySQL/MariaDB connection details. The database must already exist.</p>

    <div id="dbAlert" style="display: none;"></div>

    <form id="dbForm" action="{{ route('installer.mail-config') }}" method="GET">
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Database Host</label>
                <input type="text" class="form-input" name="db_host" id="db_host" value="127.0.0.1" required>
            </div>
            <div class="form-group">
                <label class="form-label">Database Port</label>
                <input type="text" class="form-input" name="db_port" id="db_port" value="3306" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Database Name</label>
            <input type="text" class="form-input" name="db_name" id="db_name" value="edgemail" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Database Username</label>
                <input type="text" class="form-input" name="db_username" id="db_username" value="root" required>
            </div>
            <div class="form-group">
                <label class="form-label">Database Password</label>
                <input type="password" class="form-input" name="db_password" id="db_password" placeholder="Enter password">
            </div>
        </div>

        <button type="button" class="btn btn-success" id="testBtn" onclick="testConnection()" style="margin-bottom: 1rem;">
            🔌 Test Connection
        </button>

        <div class="btn-row">
            <a href="{{ route('installer.permissions') }}" class="btn btn-secondary">← Back</a>
            <button type="submit" class="btn btn-primary" id="continueBtn" disabled>Continue →</button>
        </div>
    </form>
@endsection

@section('scripts')
<script>
async function testConnection() {
    const btn = document.getElementById('testBtn');
    const alert = document.getElementById('dbAlert');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Testing...';

    const data = {
        db_host: document.getElementById('db_host').value,
        db_port: document.getElementById('db_port').value,
        db_name: document.getElementById('db_name').value,
        db_username: document.getElementById('db_username').value,
        db_password: document.getElementById('db_password').value,
    };

    try {
        const result = await apiPost('{{ route("installer.test-database") }}', data);
        if (result.success) {
            alert.className = 'alert alert-success';
            alert.innerHTML = '✓ ' + result.message;
            alert.style.display = 'flex';
            document.getElementById('continueBtn').disabled = false;

            // Store in sessionStorage for later steps
            sessionStorage.setItem('edgemail_db', JSON.stringify(data));
        } else {
            alert.className = 'alert alert-danger';
            alert.innerHTML = '✗ ' + result.message;
            alert.style.display = 'flex';
        }
    } catch (err) {
        const errData = err.response ? await err.response.json() : { message: 'Connection failed' };
        alert.className = 'alert alert-danger';
        alert.innerHTML = '✗ ' + (errData.message || 'Connection failed');
        alert.style.display = 'flex';
    }

    btn.disabled = false;
    btn.innerHTML = '🔌 Test Connection';
}

document.getElementById('dbForm').addEventListener('submit', function(e) {
    // Store database config in session storage for final install step
    const data = {
        db_host: document.getElementById('db_host').value,
        db_port: document.getElementById('db_port').value,
        db_name: document.getElementById('db_name').value,
        db_username: document.getElementById('db_username').value,
        db_password: document.getElementById('db_password').value,
    };
    sessionStorage.setItem('edgemail_db', JSON.stringify(data));
});
</script>
@endsection

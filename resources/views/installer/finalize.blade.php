@extends('installer.layout')
@section('title', 'Installing')

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
    <div class="step completed"><span class="step-num">✓</span></div>
    <div class="step-line completed"></div>
    <div class="step active"><span class="step-num">7</span><span class="step-label">Install</span></div>
</div>
@endsection

@section('content')
    <h2 class="card-title">Installing EdgeMail</h2>
    <p class="card-desc">Please wait while we configure your email server. This may take a minute.</p>

    <div class="progress-bar">
        <div class="progress-fill" id="progressFill" style="width: 0%"></div>
    </div>

    <div id="statusText" style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1rem;">
        Preparing installation...
    </div>

    <div class="log-output" id="logOutput">
        <div class="log-info">→ Starting EdgeMail installation...</div>
    </div>

    <div class="btn-row" id="completeActions" style="display: none;">
        <span></span>
        <a href="{{ route('installer.complete') }}" class="btn btn-primary">
            ✓ Continue to Dashboard →
        </a>
    </div>
@endsection

@section('scripts')
<script>
const logOutput = document.getElementById('logOutput');
const progressFill = document.getElementById('progressFill');
const statusText = document.getElementById('statusText');

function addLog(message, type = 'info') {
    const div = document.createElement('div');
    div.className = 'log-' + type;
    div.textContent = message;
    logOutput.appendChild(div);
    logOutput.scrollTop = logOutput.scrollHeight;
}

function setProgress(percent) {
    progressFill.style.width = percent + '%';
}

async function runInstallation() {
    const config = JSON.parse(sessionStorage.getItem('edgemail_full_config') || '{}');

    if (!config.db_host) {
        addLog('✗ Error: Missing configuration. Please go back and fill all steps.', 'error');
        return;
    }

    const steps = [
        { label: 'Writing environment configuration...', progress: 10 },
        { label: 'Connecting to database...', progress: 20 },
        { label: 'Parsing JSON schema files...', progress: 30 },
        { label: 'Generating Laravel migrations...', progress: 40 },
        { label: 'Running database migrations...', progress: 55 },
        { label: 'Seeding default settings...', progress: 65 },
        { label: 'Creating admin account...', progress: 75 },
        { label: 'Configuring mail server...', progress: 85 },
        { label: 'Setting up queue workers...', progress: 90 },
        { label: 'Finalizing installation...', progress: 95 },
    ];

    // Simulate step-by-step progress
    for (const step of steps) {
        addLog('→ ' + step.label, 'info');
        statusText.textContent = step.label;
        setProgress(step.progress);
        await new Promise(r => setTimeout(r, 300));
    }

    try {
        const result = await apiPost('{{ route("installer.run") }}', config);

        if (result.success) {
            setProgress(100);
            statusText.textContent = 'Installation completed successfully!';

            if (result.log) {
                result.log.forEach(msg => addLog(msg, msg.includes('✓') ? 'success' : 'info'));
            }

            addLog('', 'info');
            addLog('✓ EdgeMail has been installed successfully!', 'success');
            addLog('✓ You can now log in to the admin panel.', 'success');

            document.getElementById('completeActions').style.display = 'flex';

            // Clear session storage
            sessionStorage.removeItem('edgemail_db');
            sessionStorage.removeItem('edgemail_config');
            sessionStorage.removeItem('edgemail_full_config');
        } else {
            statusText.textContent = 'Installation failed!';
            addLog('✗ ' + (result.message || 'Unknown error'), 'error');

            if (result.steps) {
                Object.values(result.steps).forEach(step => {
                    if (step.error) {
                        addLog('  Error: ' + step.error, 'error');
                    }
                });
            }
        }
    } catch (err) {
        statusText.textContent = 'Installation failed!';
        addLog('✗ Installation request failed: ' + err.message, 'error');
    }
}

// Start installation when page loads
window.addEventListener('load', () => {
    setTimeout(runInstallation, 500);
});
</script>
@endsection

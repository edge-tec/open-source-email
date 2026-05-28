@extends('installer.layout')
@section('title', 'Complete')

@section('content')
    <div style="text-align: center; padding: 1rem 0;">
        <div style="width: 72px; height: 72px; background: var(--success-bg); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 2rem; margin-bottom: 1.25rem; border: 2px solid rgba(16,185,129,0.3);">
            ✓
        </div>

        <h2 class="card-title" style="font-size: 1.5rem;">Installation Complete!</h2>
        <p class="card-desc">EdgeMail has been successfully installed and configured.</p>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin: 1.5rem 0; text-align: left;">
            <div style="padding: 1rem; background: var(--bg-secondary); border-radius: 10px; border: 1px solid var(--border-color);">
                <div style="font-size: 0.7rem; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.3rem;">Admin Panel</div>
                <code style="font-size: 0.85rem; color: var(--accent-hover);">/admin</code>
            </div>
            <div style="padding: 1rem; background: var(--bg-secondary); border-radius: 10px; border: 1px solid var(--border-color);">
                <div style="font-size: 0.7rem; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.3rem;">Webmail</div>
                <code style="font-size: 0.85rem; color: var(--accent-hover);">/webmail</code>
            </div>
            <div style="padding: 1rem; background: var(--bg-secondary); border-radius: 10px; border: 1px solid var(--border-color);">
                <div style="font-size: 0.7rem; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.3rem;">REST API</div>
                <code style="font-size: 0.85rem; color: var(--accent-hover);">/api/v1</code>
            </div>
            <div style="padding: 1rem; background: var(--bg-secondary); border-radius: 10px; border: 1px solid var(--border-color);">
                <div style="font-size: 0.7rem; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.3rem;">Status</div>
                <span style="color: var(--success); font-size: 0.85rem; font-weight: 600;">● Running</span>
            </div>
        </div>

        <div class="alert alert-warning">
            ⚠️ Please configure your DNS records (MX, SPF, DKIM, DMARC) for your domain to start receiving emails.
        </div>

        <div style="margin-top: 1.5rem;">
            <a href="/admin" class="btn btn-primary" style="margin-right: 0.5rem;">
                Open Admin Panel →
            </a>
            <a href="/webmail" class="btn btn-secondary">
                Open Webmail
            </a>
        </div>
    </div>
@endsection

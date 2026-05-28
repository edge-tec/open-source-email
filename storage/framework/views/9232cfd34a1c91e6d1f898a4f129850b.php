<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>No Mailbox - EdgeMail</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body{font-family:'Inter',sans-serif;background:#0f172a;color:#f8fafc;display:flex;align-items:center;justify-content:center;height:100vh;margin:0}
        .card{background:#1e293b;border:1px solid #334155;border-radius:12px;padding:2rem;text-align:center;max-width:400px;box-shadow:0 10px 25px -5px rgba(0,0,0,.5)}
        .icon{font-size:3rem;margin-bottom:1rem}
        h2{margin-bottom:.5rem;font-size:1.25rem}
        p{color:#94a3b8;font-size:.9rem;line-height:1.5;margin-bottom:1.5rem}
        .btn{display:inline-block;background:#3b82f6;color:white;padding:.6rem 1.25rem;border-radius:6px;text-decoration:none;font-weight:500}
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">📭</div>
        <h2>No Active Mailbox</h2>
        <p>Your account does not have an active mailbox associated with it. If you are an administrator, please create a mailbox in the admin panel.</p>
        
        <?php if(auth()->user()->isAdmin()): ?>
            <a href="<?php echo e(route('admin.dashboard')); ?>" class="btn">Go to Admin Panel</a>
        <?php else: ?>
            <form method="POST" action="<?php echo e(route('logout')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn" style="background:#475569;border:none;cursor:pointer;font-family:inherit">Logout</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
<?php /**PATH /var/www/html/resources/views/webmail/no-mailbox.blade.php ENDPATH**/ ?>
/**
 * PASTIMES — js/main.js
 */

// ── Splash Screen ────────────────────────────────────────
window.addEventListener('load', function () {
    var splash = document.getElementById('splashScreen');
    if (!splash) return;
    setTimeout(function () {
        splash.style.display = 'none';
    }, 2500);
});

// ── Mobile Nav Toggle ────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    var toggle = document.getElementById('menuToggle');
    var menu   = document.getElementById('navMenu');
    if (toggle && menu) {
        toggle.addEventListener('click', function () {
            menu.classList.toggle('active');
        });
    }
});

// ── Password Toggle ──────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.toggle-password').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var target = document.querySelector(this.dataset.target);
            if (!target) return;
            if (target.type === 'password') {
                target.type = 'text';
                this.innerHTML = '<i class="fas fa-eye-slash"></i>';
            } else {
                target.type = 'password';
                this.innerHTML = '<i class="fas fa-eye"></i>';
            }
        });
    });
});

// ── Password Requirements ────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    var pw = document.getElementById('password');
    if (!pw) return;
    pw.addEventListener('input', function () {
        var v = this.value;
        var len = document.getElementById('req-length');
        var up  = document.getElementById('req-upper');
        var num = document.getElementById('req-number');
        if (len) len.classList.toggle('met', v.length >= 8);
        if (up)  up.classList.toggle('met',  /[A-Z]/.test(v));
        if (num) num.classList.toggle('met', /[0-9]/.test(v));
    });
});

// ── Confirm Password ─────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    var pw   = document.getElementById('password');
    var conf = document.getElementById('confirmPassword');
    var hint = document.getElementById('confirmHint');
    if (!pw || !conf) return;
    function check() {
        if (!conf.value) { if (hint) hint.textContent = ''; return; }
        if (pw.value === conf.value) {
            conf.style.borderColor = 'var(--success)';
            if (hint) { hint.textContent = '✓ Passwords match'; hint.style.color = '#4ade80'; }
        } else {
            conf.style.borderColor = 'var(--danger)';
            if (hint) { hint.textContent = '✗ Passwords do not match'; hint.style.color = '#f87171'; }
        }
    }
    conf.addEventListener('input', check);
    pw.addEventListener('input', check);
});

// ── Notification Toast ───────────────────────────────────
function showNotification(message, type) {
    type = type || 'success';
    var existing = document.querySelector('.notification');
    if (existing) existing.remove();
    var toast = document.createElement('div');
    toast.className = 'notification notification-' + type;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(function () { toast.classList.add('show'); }, 10);
    setTimeout(function () {
        toast.classList.remove('show');
        setTimeout(function () { toast.remove(); }, 350);
    }, 3200);
}

// ── Auto-hide alerts ─────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.alert').forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity .5s';
            alert.style.opacity = '0';
            setTimeout(function () { alert.remove(); }, 500);
        }, 5000);
    });
});

// ── Image Fallback ───────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('img').forEach(function (img) {
        img.addEventListener('error', function () {
            this.src = '/Pastimes/images/placeholder.png';
        });
    });
});
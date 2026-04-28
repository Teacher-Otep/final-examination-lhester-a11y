/* ============================================================
   test.js — Cosmic Botanical Student Portal Scripts
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {

    const logo     = document.getElementById('logo-trigger');
    const wrapper  = document.querySelector('.logo-wrapper');
    const sparkles = document.getElementById('sparkles');
    const sections = document.querySelectorAll('.glass-card');

    // =========================================================
    // AUTO-SHOW ACTIVE SECTION + Toast auto-dismiss
    // =========================================================
    const toast = document.getElementById('toast');
    if (toast) {
        setTimeout(() => {
            toast.style.transition = 'opacity 0.5s ease';
            toast.style.opacity    = '0';
            setTimeout(() => toast.remove(), 500);
        }, 4200);
    }

    // =========================================================
    // PARTICLE BURST — neon dots radiating outward
    // =========================================================
    const COLORS = ['#a8ff47', '#7b4dff', '#ff5e5e', '#a8ff47', '#fff', '#7b4dff'];

    function spawnParticles() {
        for (let i = 0; i < 14; i++) {
            const p = document.createElement('div');
            p.className = 'logo-particle';

            const angle = (i / 14) * Math.PI * 2;
            const dist  = 52 + Math.random() * 28;

            p.style.setProperty('--tx', Math.cos(angle) * dist + 'px');
            p.style.setProperty('--ty', Math.sin(angle) * dist + 'px');
            p.style.background  = COLORS[i % COLORS.length];
            p.style.animation   = `logo-burst 0.65s ease-out ${i * 0.028}s forwards`;

            wrapper.appendChild(p);
            setTimeout(() => p.remove(), 900);
        }
    }

    // =========================================================
    // LOGO CLICK — morph spin + glow + particles + reset view
    // =========================================================
    if (logo) {
        logo.addEventListener('click', () => {
            if (logo.classList.contains('spinning')) return;

            sections.forEach(s => {
                s.style.display = 'none';
                s.classList.remove('slide-out');
            });

            // Show welcome screen
            const welcome = document.getElementById('welcome');
            if (welcome) {
                welcome.style.display = 'flex';
                welcome.classList.add('slide-out');
            }

            logo.classList.add('spinning');

            if (sparkles) {
                sparkles.style.opacity = '1';
                setTimeout(() => { sparkles.style.opacity = '0'; }, 500);
            }

            spawnParticles();

            // Update active nav
            document.querySelectorAll('.nav-link').forEach(n => n.classList.remove('active'));

            setTimeout(() => {
                logo.classList.remove('spinning');
            }, 850);
        });
    }

    // =========================================================
    // KEYBOARD SHORTCUT — keys 1/2/3/4
    // =========================================================
    document.addEventListener('keydown', (e) => {
        const map = { '1': 'create', '2': 'read', '3': 'update', '4': 'delete' };
        if (map[e.key] && document.activeElement.tagName !== 'INPUT') {
            toggle(map[e.key]);
        }
    });

    // =========================================================
    // GLITCH TEXT on nav hover
    // =========================================================
    const GLITCH_CHARS = '!<>-_\\/[]{}—=+*^?#01';

    document.querySelectorAll('.nav-link').forEach(btn => {
        const textEl = btn.querySelector('.nav-text');
        if (!textEl) return;
        const original = textEl.textContent;
        let frame = 0, interval;

        btn.addEventListener('mouseenter', () => {
            clearInterval(interval);
            frame = 0;
            interval = setInterval(() => {
                textEl.textContent = original
                    .split('')
                    .map((ch, i) => {
                        if (i < frame) return original[i];
                        if (ch === ' ') return ' ';
                        return GLITCH_CHARS[Math.floor(Math.random() * GLITCH_CHARS.length)];
                    })
                    .join('');
                frame++;
                if (frame > original.length) {
                    clearInterval(interval);
                    textEl.textContent = original;
                }
            }, 35);
        });

        btn.addEventListener('mouseleave', () => {
            clearInterval(interval);
            textEl.textContent = original;
        });
    });

    // =========================================================
    // TILT EFFECT on glass cards (3D magnetic)
    // =========================================================
    document.querySelectorAll('.glass-card').forEach(card => {
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = (e.clientX - rect.left) / rect.width  - 0.5;
            const y = (e.clientY - rect.top)  / rect.height - 0.5;
            card.style.transform = `perspective(800px) rotateY(${x * 4}deg) rotateX(${-y * 4}deg)`;
        });
        card.addEventListener('mouseleave', () => {
            card.style.transform = '';
        });
    });

});

// =========================================================
// TOGGLE SECTION — show/hide glass-cards with animation
// =========================================================
function toggle(targetId) {
    const sections = document.querySelectorAll('.glass-card');
    const welcome  = document.getElementById('welcome');

    sections.forEach(s => {
        s.style.display = 'none';
        s.classList.remove('slide-out');
    });

    if (welcome) welcome.style.display = 'none';

    const target = document.getElementById(targetId);
    if (target) {
        target.style.display = 'block';

        // Force reflow so removing + re-adding the class re-triggers the animation
        void target.offsetWidth;
        target.classList.add('slide-out');

        // Re-trigger staggered input animations by resetting them
        target.querySelectorAll('.styled-form .input-wrap').forEach(el => {
            el.style.animation = 'none';
            void el.offsetWidth;
            el.style.animation = '';
        });

        // Re-trigger button animation
        const btn = target.querySelector('.styled-form .btn-primary');
        if (btn) {
            btn.style.animation = 'none';
            void btn.offsetWidth;
            btn.style.animation = '';
        }

        // Re-trigger table row animations
        target.querySelectorAll('tbody tr').forEach(row => {
            row.style.animation = 'none';
            void row.offsetWidth;
            row.style.animation = '';
        });

        // Re-trigger record count
        const rc = target.querySelector('.record-count');
        if (rc) {
            rc.style.animation = 'none';
            void rc.offsetWidth;
            rc.style.animation = '';
        }
    }

    // Update active state
    document.querySelectorAll('.nav-link').forEach(n => {
        n.classList.toggle('active', n.getAttribute('data-target') === targetId);
    });

    // Subtle logo react
    const logo = document.getElementById('logo-trigger');
    if (logo && !logo.classList.contains('spinning')) {
        logo.style.filter = 'drop-shadow(0 0 12px rgba(168,255,71,0.5))';
        setTimeout(() => { logo.style.filter = ''; }, 300);
    }
}

// =========================================================
// DELETE CONFIRMATION
// =========================================================
function confirmDelete() {
    return confirm('⚠ Permanently delete this student record? This action cannot be undone.');
}

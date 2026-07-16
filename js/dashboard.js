/* ═══════════════════════════════════════════════════════════
   FREELANCE PORTAL — dashboard.js
   Stat counter animation + progress bar fill
   ═══════════════════════════════════════════════════════════ */

document.addEventListener('DOMContentLoaded', () => {

    /* ── Animate stat counters ───────────────────────────── */
    function animateCount(el, target, duration = 1200) {
        if (target === 0) return;
        let start = null;
        const step = ts => {
            if (!start) start = ts;
            const p = Math.min((ts - start) / duration, 1);
            // Ease out cubic
            const eased = 1 - Math.pow(1 - p, 3);
            el.textContent = Math.floor(eased * target);
            if (p < 1) requestAnimationFrame(step);
            else el.textContent = target;
        };
        requestAnimationFrame(step);
    }

    document.querySelectorAll('[data-count]').forEach(el => {
        const target = parseInt(el.dataset.count);
        animateCount(el, target);
    });

    /* ── Fill progress bars ──────────────────────────────── */
    setTimeout(() => {
        document.querySelectorAll('[data-pct]').forEach(bar => {
            bar.style.width = bar.dataset.pct + '%';
        });
    }, 200);

});
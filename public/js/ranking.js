document.addEventListener('DOMContentLoaded', function () {
    const completeUrl = document.getElementById('complete-url').value;

    document.querySelectorAll('.star-group').forEach(function (group) {
        const stars  = Array.from(group.querySelectorAll('.star-btn'));
        const dishId = group.getAttribute('data-dish-id');

        // ── Hover ──────────────────────────────────────────────────
        stars.forEach(function (star, index) {
            star.addEventListener('mouseenter', function () {
                stars.forEach(function (s, i) {
                    setStarFilled(s, i <= index);
                });
            });
        });

        group.addEventListener('mouseleave', function () {
            renderCurrentRating(group, stars);
        });

        // ── Clic ───────────────────────────────────────────────────
        stars.forEach(function (star) {
            star.addEventListener('click', function () {
                const rating = parseInt(star.getAttribute('data-value'), 10);
                submitVote(dishId, rating, group, stars);
            });
        });
    });

    // ── Helpers ────────────────────────────────────────────────────

    function setStarFilled(starEl, filled) {
        const icon = starEl.querySelector('i');
        if (filled) {
            starEl.classList.add('text-amber-400');
            starEl.classList.remove('text-gray-300', 'dark:text-gray-600');
            icon.className = 'bi bi-star-fill';
        } else {
            starEl.classList.remove('text-amber-400');
            starEl.classList.add('text-gray-300');
            icon.className = 'bi bi-star';
        }
    }

    function renderCurrentRating(group, stars) {
        const current = parseInt(group.getAttribute('data-current') || '0', 10);
        stars.forEach(function (s, i) {
            setStarFilled(s, i < current);
        });
    }

    async function submitVote(dishId, rating, group, stars) {
        try {
            const res = await fetch(completeUrl + '/vote', {
                method:  'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body:    `dish_id=${dishId}&rating=${rating}`,
            });
            const data = await res.json();

            if (data.success) {
                group.setAttribute('data-current', rating);
                renderCurrentRating(group, stars);
                stars.forEach(function (s) {
                    s.disabled = true;
                    s.classList.add('cursor-default');
                    s.classList.remove('cursor-pointer');
                });
                group.classList.add('pointer-events-none');

                // Mise à jour de l'affichage de la note globale
                const avgEl   = document.getElementById('avg-'   + dishId);
                const countEl = document.getElementById('count-' + dishId);
                const starsEl = document.getElementById('stars-' + dishId);

                if (avgEl)   avgEl.textContent   = data.avg.toFixed(1);
                if (countEl) countEl.textContent  = data.count + ' vote' + (data.count > 1 ? 's' : '');
                if (starsEl) updateDisplayStars(starsEl, data.avg);

                showToast('Vote enregistré !', 'success');
            } else {
                showToast(data.error || (res.status === 419 ? 'Session expirée, recharge la page' : 'Erreur lors du vote'), 'error');
            }
        } catch {
            showToast('Erreur réseau', 'error');
        }
    }

    function updateDisplayStars(container, avg) {
        container.innerHTML = '';
        for (let i = 1; i <= 5; i++) {
            const el = document.createElement('i');
            if (avg >= i) {
                el.className = 'bi bi-star-fill text-amber-400';
            } else if (avg >= i - 0.5) {
                el.className = 'bi bi-star-half text-amber-400';
            } else {
                el.className = 'bi bi-star text-gray-300 dark:text-gray-600';
            }
            container.appendChild(el);
        }
    }

    function showToast(message, type) {
        const toast = document.createElement('div');
        toast.className = [
            'fixed bottom-6 right-6 z-50 px-4 py-3 rounded-xl text-sm font-medium shadow-lg transition-opacity duration-500',
            type === 'success'
                ? 'bg-emerald-600 text-white'
                : 'bg-red-600 text-white',
        ].join(' ');
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => { toast.style.opacity = '0'; }, 2000);
        setTimeout(() => toast.remove(), 2500);
    }
});

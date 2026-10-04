document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.dish-card').forEach(function (card) {
        const incBtn  = card.querySelector('.increment');
        const decBtn  = card.querySelector('.decrement');
        const display = card.querySelector('.qty-display');
        const input   = card.querySelector('input[type="hidden"]');
        const icon    = card.querySelector('.dish-icon');

        const isDark = () => document.documentElement.classList.contains('dark');

        function updateCard(qty) {
            input.value         = qty;
            display.textContent = qty;

            if (qty > 0) {
                card.style.borderColor     = isDark() ? '#6366f1' : '#818cf8'; // indigo-500 / indigo-400
                card.style.backgroundColor = isDark() ? 'rgba(99,102,241,0.08)' : 'rgba(238,242,255,0.6)';
                icon.className  = 'dish-icon bi bi-check-circle-fill text-indigo-500 flex-shrink-0 text-base transition-all';
                display.style.color = isDark() ? '#a5b4fc' : '#4f46e5'; // indigo-300 / indigo-700
            } else {
                card.style.borderColor     = 'transparent';
                card.style.backgroundColor = '';
                icon.className  = 'dish-icon bi bi-circle text-gray-300 dark:text-gray-600 flex-shrink-0 text-base transition-all';
                display.style.color = '';
            }
        }

        card.querySelector('.dish-toggle').addEventListener('click', function () {
            const current = parseInt(input.value) || 0;
            updateCard(current === 0 ? 1 : 0);
        });

        incBtn.addEventListener('click', function () {
            updateCard((parseInt(input.value) || 0) + 1);
        });

        decBtn.addEventListener('click', function () {
            updateCard(Math.max(0, (parseInt(input.value) || 0) - 1));
        });
    });
});

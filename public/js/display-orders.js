/* global Pusher, Modal */

function startFormCountdown() {
    const el = document.getElementById('form-countdown');
    if (!el) return;

    const deadline = parseInt(el.dataset.deadline, 10);
    const banner   = document.getElementById('form-deadline-banner');
    const gradient = banner?.querySelector('.bg-gradient-to-r');

    function update() {
        const diff = deadline - Math.floor(Date.now() / 1000);

        if (diff <= 0) {
            el.textContent = 'Fermé';
            if (gradient) {
                gradient.style.background = 'linear-gradient(to right, #6b7280, #9ca3af)';
            }
            return;
        }

        const h = Math.floor(diff / 3600);
        const m = Math.floor((diff % 3600) / 60);
        const s = diff % 60;
        el.textContent = [h, m, s].map(n => String(n).padStart(2, '0')).join(':');

        if (gradient) {
            if (diff < 10 * 60) {
                gradient.style.background = 'linear-gradient(to right, #dc2626, #e11d48)';
            } else if (diff < 30 * 60) {
                gradient.style.background = 'linear-gradient(to right, #d97706, #ea580c)';
            }
        }

        setTimeout(update, 1000);
    }

    update();
}

document.addEventListener('DOMContentLoaded', function () {
    startFormCountdown();
    const ordersContainer = document.getElementById('orders-container');
    if (!ordersContainer) return;

    // Pusher : logs uniquement en local
    const pusherKey = ordersContainer.dataset.pusherKey;
    if (pusherKey && typeof Pusher !== 'undefined') {
        Pusher.logToConsole = ['localhost', '127.0.0.1'].includes(window.location.hostname);

        const pusher  = new Pusher(pusherKey, { cluster: ordersContainer.dataset.pusherCluster });
        const channel = pusher.subscribe('send-sms');
        channel.bind('send-sms', function (data) {
            createSmsSummary(data['status'], data['message']);
        });
    }

    const completeUrl     = document.getElementById('complete-url').value;
    const editOrderModalEl = document.getElementById('editOrderModal');

    const editOrderModal = new Modal(editOrderModalEl, {
        onHide: () => {
            document.getElementById('perso').value = '';
            document.getElementById('editOrderModalLabel').textContent = '';
            document.querySelectorAll('input[name*="dish"]').forEach(input => { input.value = 0; });
            document.getElementById('edit-order-form').action = '';
        },
    });

    // Boutons de suppression
    document.querySelectorAll('[data-action="delete-order"]').forEach(button => {
        button.addEventListener('click', function () {
            const orderId = this.getAttribute('data-order-id');
            if (confirm('Êtes-vous sûr de vouloir supprimer cette commande ?')) {
                postTo(`${completeUrl}/delete-order/${orderId}`);
            }
        });
    });

    // Boutons d'édition
    document.querySelectorAll('[data-action="edit-order"]').forEach(button => {
        button.addEventListener('click', function () {
            const orderId          = this.getAttribute('data-order-id');
            const perso            = this.getAttribute('data-perso');
            const username         = this.getAttribute('data-username');
            const orderDishesObject = JSON.parse(this.getAttribute('data-order-dishes'));

            document.getElementById('editOrderModalLabel').textContent = username;

            if (orderDishesObject) {
                Object.keys(orderDishesObject).forEach(key => {
                    const el = document.getElementById('dish-' + orderDishesObject[key]['id']);
                    if (el) el.value = orderDishesObject[key]['quantity'];
                });
            }

            document.getElementById('perso').value = perso;

            document.getElementById('edit-order-form').action = `${completeUrl}/edit-order/${orderId}`;

            editOrderModal.show();
        });
    });

    // Fermeture de la modale
    document.getElementById('close-order-modal').addEventListener('click', () => editOrderModal.hide());
    document.getElementById('cancel-order-modal').addEventListener('click', () => editOrderModal.hide());

    // Soumission du formulaire d'édition
    document.getElementById('edit-order-form').addEventListener('submit', function (event) {
        event.preventDefault();
        if (confirm('Êtes-vous sûr de vouloir modifier cette commande ?')) {
            this.submit();
        }
    });
});

function createSmsSummary(status, message) {
    if (document.querySelector('.sms-summary-block')) return;

    const ordersContainer = document.getElementById('orders-container');
    if (!ordersContainer) return;

    const isSuccess  = status === 'success';
    const colorClass = isSuccess
        ? 'bg-green-50 dark:bg-green-900/20 border-green-300 dark:border-green-800 text-green-800 dark:text-green-300'
        : 'bg-red-50 dark:bg-red-900/20 border-red-300 dark:border-red-800 text-red-800 dark:text-red-300';

    const div = document.createElement('div');
    div.className = `sms-summary-block mb-6 p-4 rounded-lg border text-sm ${colorClass}`;
    div.innerHTML  = '<p class="font-semibold">🗨️ SMS De Commande</p><p></p>';
    div.lastChild.textContent = message;

    ordersContainer.insertBefore(div, ordersContainer.firstChild);
}

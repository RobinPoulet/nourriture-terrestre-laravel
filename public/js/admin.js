/* global Modal */
document.addEventListener('DOMContentLoaded', function () {
    const completeUrl = document.getElementById('complete-url').value;

    // ── Onglets ──────────────────────────────────────────────────────

    const TAB_ACTIVE  = ['bg-indigo-600', 'text-white', 'font-semibold'];
    const STORAGE_KEY = 'admin_tab';

    function activateTab(tabName) {
        document.querySelectorAll('.admin-tab').forEach(function (btn) {
            const isActive = btn.getAttribute('data-tab') === tabName;
            TAB_ACTIVE.forEach(c => btn.classList.toggle(c, isActive));
        });
        document.querySelectorAll('.admin-panel').forEach(function (panel) {
            panel.classList.toggle('hidden', panel.id !== 'panel-' + tabName);
        });
        sessionStorage.setItem(STORAGE_KEY, tabName);
    }

    document.querySelectorAll('.admin-tab').forEach(function (btn) {
        btn.addEventListener('click', function () {
            activateTab(this.getAttribute('data-tab'));
        });
    });

    activateTab(sessionStorage.getItem(STORAGE_KEY) || 'users');

    // ── Modale utilisateurs ──────────────────────────────────────────

    const userModalEl    = document.getElementById('addUserModal');
    const userNameInput  = document.getElementById('userName');
    const userValidateBtn = document.getElementById('user-validate');

    const userModal = new Modal(userModalEl, {
        onHide: () => resetUserModal(),
    });

    function resetUserModal() {
        document.getElementById('addUserModalLabel').textContent = 'Ajouter un utilisateur';
        document.getElementById('form-user').action = completeUrl + '/admin/create-user';
        userNameInput.value = '';
        userValidateBtn.textContent = 'Ajouter';
        const alert = document.getElementById('alert-user-modal');
        alert.className = '';
        alert.textContent = '';
    }

    document.getElementById('btn-add-user').addEventListener('click', function () {
        resetUserModal();
        userModal.show();
    });

    document.querySelectorAll('.btn-edit').forEach(function (button) {
        button.addEventListener('click', function () {
            const userId   = this.getAttribute('data-user-id');
            const userName = this.getAttribute('data-user-name');
            if (userId && userName) {
                document.getElementById('form-user').action = `${completeUrl}/admin/edit-user/${userId}`;
                document.getElementById('addUserModalLabel').textContent = 'Éditer un utilisateur';
                userNameInput.value = userName;
                userValidateBtn.textContent = 'Modifier';
            } else {
                resetUserModal();
            }
            userModal.show();
        });
    });

    document.getElementById('close-user-modal').addEventListener('click', () => userModal.hide());
    document.getElementById('cancel-user-modal').addEventListener('click', () => userModal.hide());

    document.getElementById('form-user').addEventListener('submit', function (event) {
        const isEdit = userValidateBtn.textContent === 'Modifier';
        handleUserAction(event, isEdit ? 'edit' : 'add');
    });

    // ── Modale annonces ──────────────────────────────────────────────

    const announcementModalEl  = document.getElementById('announcementModal');
    const announcementMsgInput = document.getElementById('announcement-message');
    const announcementValidBtn = document.getElementById('announcement-validate');

    const announcementModal = new Modal(announcementModalEl, {
        onHide: () => resetAnnouncementModal(),
    });

    function resetAnnouncementModal() {
        document.getElementById('announcementModalLabel').textContent = 'Ajouter une annonce';
        document.getElementById('announcement-form').action = completeUrl + '/admin/create-announcement';
        announcementMsgInput.value = '';
        announcementValidBtn.textContent = 'Ajouter';
        const alert = document.getElementById('alert-announcement-modal');
        alert.className = '';
        alert.textContent = '';
    }

    document.getElementById('btn-add-announcement').addEventListener('click', function () {
        resetAnnouncementModal();
        announcementModal.show();
    });

    document.querySelectorAll('.btn-edit-announcement').forEach(function (button) {
        button.addEventListener('click', function () {
            const id      = this.getAttribute('data-announcement-id');
            const message = this.getAttribute('data-announcement-message');
            if (id && message !== null) {
                document.getElementById('announcement-form').action = `${completeUrl}/admin/edit-announcement/${id}`;
                document.getElementById('announcementModalLabel').textContent = 'Éditer une annonce';
                announcementMsgInput.value = message;
                announcementValidBtn.textContent = 'Modifier';
            } else {
                resetAnnouncementModal();
            }
            announcementModal.show();
        });
    });

    document.getElementById('close-announcement-modal').addEventListener('click', () => announcementModal.hide());
    document.getElementById('cancel-announcement-modal').addEventListener('click', () => announcementModal.hide());

    document.getElementById('announcement-form').addEventListener('submit', function (event) {
        const msg = announcementMsgInput.value.trim();
        if (msg === '') {
            event.preventDefault();
            const alert = document.getElementById('alert-announcement-modal');
            alert.className = 'p-3 text-sm text-red-800 bg-red-50 border border-red-300 rounded-lg';
            alert.textContent = 'Le message ne peut pas être vide.';
        }
    });
});

function confirmDeleteUser(userId) {
    const completeUrl = document.getElementById('complete-url').value;
    if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) {
        postTo(`${completeUrl}/admin/delete-user/${userId}`);
    }
}

function confirmDeleteAnnouncement(id) {
    const completeUrl = document.getElementById('complete-url').value;
    if (confirm('Êtes-vous sûr de vouloir supprimer cette annonce ?')) {
        postTo(`${completeUrl}/admin/delete-announcement/${id}`);
    }
}

function confirmResetDevice(userId) {
    const completeUrl = document.getElementById('complete-url').value;
    if (confirm("Réinitialiser l'appareil de cet utilisateur ? Il sera ré-associé à sa prochaine commande.")) {
        postTo(`${completeUrl}/admin/reset-device/${userId}`);
    }
}

function handleUserAction(event, actionType) {
    const userName = document.getElementById('userName');
    const alertUserModal = document.getElementById('alert-user-modal');

    if (userName.value.trim() === '') {
        event.preventDefault();
        alertUserModal.className = 'p-3 text-sm text-red-800 bg-red-50 border border-red-300 rounded-lg';
        alertUserModal.textContent = "Merci de renseigner le champ 'Nom'";
        return;
    }

    if (actionType === 'edit' && !confirm('Êtes-vous sûr de vouloir modifier cet utilisateur ?')) {
        event.preventDefault();
    }
}

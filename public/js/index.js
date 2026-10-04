// ── Dark mode toggle ─────────────────────────────────────────────
function toggleTheme() {
    const isDark = document.documentElement.classList.toggle('dark');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
}

// ── Sécurité : jeton CSRF ────────────────────────────────────────
function csrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

// Envoie une requête POST (avec jeton CSRF) via un formulaire construit à la volée
function postTo(url) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = url;
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = '_token';
    input.value = csrfToken();
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}
